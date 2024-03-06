<?php
$default_tab = '';
if(in_array(getRightsId('send_beta_invite_manual_invite'), getUserRightsData($this->DeviceType))){
    $default_tab = 'lnkManualInvite';
}else if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){
    $default_tab = 'lnkImportFile';
}
?>

<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><a target="_self" href="<?php echo base_url('admin/betainvite'); ?>"><?php echo lang('BetaInvite'); ?></a></span></li>
                    <li>/</li>
                    <li><span><a target="_self" href="<?php echo base_url('admin/betainvite'); ?>"><?php echo lang('BetaInvite_Invited'); ?></a></span></li>
                    <li>/</li>
                    <li><span><a href="javascript:void(0);" class="selected"><?php echo lang('SendBetaInvite'); ?></a></span></li>
            
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="SendBetainviteCtrl" id="SendBetainviteCtrl" ng-init="loadSendInviteTab('<?php echo $default_tab; ?>');">
    
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><?php echo lang('SendBetaInvite'); ?></h2>
    </div> 
    <!--/Info row-->
    
    <div class="row-flued sendbeta-invite">
        <div class="global-tab">
          <ul class="tabs" id="tabs">
                <?php if(in_array(getRightsId('send_beta_invite_manual_invite'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a id="lnkManualInvite" href="javascript:;" onclick="HideShowTab('manual');" class="selected"><?php echo lang('Manual_Invite'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a id="lnkImportFile" href="javascript:;" onclick="HideShowTab('importfile');"><?php echo lang('Import_File'); ?></a></li>
                <?php } ?>
          </ul>
        </div>
        
        <div class="manual-invite" id="dvManualInvite">
            <?php if(in_array(getRightsId('send_beta_invite_manual_invite'), getUserRightsData($this->DeviceType))){ ?>
            <table class="invite-filed" id="manualInvite">              
              <tr>
                <td>
                    <div>
                        <div class="text-field large" data-type="focus">
                            <input class="betausername" type="text" name="username[]" value="" placeholder="Please enter name of the user">
                        </div>
                        <div class="error-holder"><span>Error</span></div>
                    </div>
                </td>
                <td>
                    <div>
                        <div class="text-field large" data-type="focus">
                            <input class="betauseremail" type="text" name="user_email[]" value="" onblur="CheckEmailDuplicacy(this);" placeholder="Please enter email of the user">
                        </div>
                        <div class="error-holder"><span>Error</span></div>
                    </div> 
                </td>
                <td>
                    <div>
                        <div class="text-field large" data-type="focus">
                            <input class="betausername" type="text" name="username[]" value="" placeholder="Please enter name of the user">
                        </div>
                        <div class="error-holder"><span>Error</span></div>
                    </div>
                </td>
                <td>
                    <div>
                        <div class="text-field large" data-type="focus">
                            <input class="betauseremail" type="text" name="user_email[]" value="" onblur="CheckEmailDuplicacy(this);" placeholder="Please enter email of the user">
                        </div>
                        <div class="error-holder"><span>Error</span></div>
                    </div> 
                </td>
                <td>
                    <div class="addmoreName" id="addMorefield" ng-click="addMoreField();">
                      <i class="icon-addmore">&nbsp;</i>
                    </div> 
                </td>
              </tr> 
            </table>
            <div class="clearfix"></div>
            <div class="form-footer">
                <div class="info-block"><b><?php echo lang('Note'); ?>:</b> <?php echo lang('Invite_User_Note'); ?></div>
                <button class="gray-button" onClick="window.location='<?php echo base_url(); ?>admin/betainvite'"><?php echo lang('Cancel_Upper'); ?></button>
              <?php if(in_array(getRightsId('send_beta_invite_manual_invite'), getUserRightsData($this->DeviceType))){ ?>
                <button class="button" id="btnsendinvite" onClick="GetValues();"><?php echo lang('Send_Invite'); ?></button>
              <?php } ?>
            </div>
            <?php } ?>            
        </div>
        
        <div class="import-file hide" id="dvImportFile">
            <?php if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){ ?>
            <form method="post" enctype="multipart/form-data" id="importcsvform">
                <table class="email-table" id="UploadField">
                    <tbody>
                        <tr>
                            <td>
                                <div>
                                    <div class="text-field support-browse browse" data-type="focus">
                                        <input type="text" placeholder="<?php echo lang('Upload_CSV_file'); ?>" class="addval" id="Upload">
                                        <div class="support-search">
                                            <input type="file" id="csv_file" name="csv_file"><label><?php echo lang('Browse'); ?></label>
                                        </div>
                                    </div>
                                    <div class="error-holder csverror" id="dvUploadError"><span>Error</span></div>
                                </div>
                                <?php if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){ ?>
                                    <a class="downloadSample" href="<?php echo base_url(); ?>admin/betainvite/downloadsample"><?php echo lang('Download_Sample_File'); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>            
                <table class="users-table registered-user" style="display: none;" id="uploadedList">
                    <tbody>
                        <tr>
                            <th class="ui-sort"><?php echo lang('FullName'); ?></th>
                            <th><?php echo lang('Email'); ?></th>
                            <th><?php echo lang('Action'); ?></th>
                        </tr>
                        <tr ng-repeat="import_user in importUserList" ng-init="import_user.indexArr=$index">
                            <td>{{import_user.name}}</td>
                            <td><a rel="tipsy" class="icon-email" href="mailto:{{import_user.email}}" original-title="mailto:{{import_user.email}}">&nbsp;</a></td>
                            <td>
                                <?php if(in_array(getRightsId('send_beta_invite_import_file_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                    <a href="javascript:void(0);" ng-click="deleteConfirmUploadedUser($index);" class="icon-delete" id="EmailID_{{$index}}"></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr ng-show="importListCount < 1">
                            <td colspan="3"><div class="no-content text-center noborder"><p><?php echo lang('ThereIsNoRecordToImport'); ?></p></div></td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-footer ">
                    <div class="info-block"><b><?php echo lang('Note'); ?>:</b> <?php echo lang('Invite_User_Note'); ?></div>
                    <?php if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){ ?>
                        <button class="button" style="display: none;" id="start_import" ng-click="ImportFinalUsers();"><?php echo lang('START_IMPORT'); ?></button>
                    <?php } ?>
                    <?php if(in_array(getRightsId('send_beta_invite_import_file'), getUserRightsData($this->DeviceType))){ ?>    
                        <input class="button" type="button" name="submit" id="btnUploadCsv" value="<?php echo lang('Upload'); ?>">
                    <?php } ?>
                        <input ng-show="importListCount < 1" class="button" type="button" name="submit" id="cancelUploadbtn" ng-click="resetImportSection()" value="<?php echo lang('Cancel'); ?>">
                    <div class="info-block" id="Msg">{{importText}}</div>                    
                </div>
            </form>
            <?php } ?>            
        </div>
        
        <!--Popup for change error log status -->
        <div class="popup confirme-popup animated" id="confirmeDeletePopup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeDeletePopup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p class="text-center"><?php echo lang('Sure_Delete'); ?> ?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onclick="closePopDiv('confirmeDeletePopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button" ng-click="deleteUploadedUser()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                </div>
            </div>
        </div>      
        <!--Popup for change error log status -->
        
    </div>
    
</div>
</section>