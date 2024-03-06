<?php
$selectall_permission = 0;
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('EmailSettings'); ?></li>
                    <li>/</li>
                    <li><span><?php echo lang('SMTPSettings'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<!--/Bread crumb-->
<section class="main-container">

<div class="container"   ng-controller="EmailSettingCtrl" id="EmailSettingCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('SMTPSettings'); ?></span> ({{totalSettings}})</h2>
        <div class="info-row-right">
            <?php if(in_array(getRightsId('smtp_settings_save_add_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="<?php echo base_url(); ?>admin/emailsetting/email_setting_authentication" class="btn-link">
                    <ins class="buttion-icon"><i class="icon-add"></i></ins>
                    <span><?php echo lang('Add'); ?></span>
                </a>
            <?php } ?>
            <?php if(in_array(getRightsId('smtp_settings_delete_event'), getUserRightsData($this->DeviceType))){
                $selectall_permission = 1; ?>
                <div id="selectallbox" class="text-field selectbox">
                    <span>
                        <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                    </span>
                    <label for="selectAll"><?php echo lang("Select_All"); ?></label>
                </div>
            <?php } ?>

            <div id="ItemCounter" class="items-counter">
                <ul class="button-list">
                    <?php if(in_array(getRightsId('smtp_settings_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="SetMultipleSmtpSettingStatus('delete');"><?php echo lang('Delete'); ?></a></li>
                    <?php } ?>
                </ul>
                <div class="total-count-view"><span class="counter">0</span> </div>
            </div>
        </div>
    </div>
    <!--/Info row-->

    <div class="row-flued">
        <div class="panel panel-secondary">
                <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover  smtp_setting_table">
                <tr>
                    <th id="name" class="ui-sort selected" ng-click="orderByField = 'name'; reverseSort = !reverseSort; sortBY('name')">                           
                        <div class="shortdiv sortedDown">Name<span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th id="fromemail" class="ui-sort" ng-click="orderByField = 'fromemail'; reverseSort = !reverseSort; sortBY('fromemail')">
                        <div class="shortdiv">Email<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                    </th>
                    <th id="islocalsmtp" class="ui-sort" ng-click="orderByField = 'islocalsmtp'; reverseSort = !reverseSort; sortBY('islocalsmtp')">
                        <div class="shortdiv">Local SMTP<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="servername" class="ui-sort" ng-click="orderByField = 'servername'; reverseSort = !reverseSort; sortBY('servername')">
                        <div class="shortdiv">SMTP Server<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="status" class="ui-sort" ng-click="orderByField = 'status'; reverseSort = !reverseSort; sortBY('status')">
                        <div class="shortdiv">Status<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th>Actions</th>
                </tr>

                <tr class="rowtr" ng-repeat="smtplist in listData[0].ObjSMTP"  ng-class="{selected : isSelected(smtplist),notselected:isNotSelected(smtplist)}" ng-init="smtplist.indexArr=$index" ng-click="selectCategory(smtplist);">
                    <td>
                        <p>{{smtplist.name}}</p>
                    </td>                
                    <td><a rel="tipsy" href="mailto:{{smtplist.fromemail}}" original-title="{{smtplist.fromemail}}">{{smtplist.fromemail}}</a></td>
                    <td>{{smtplist.islocalsmtp}}</td>
                    <td>{{smtplist.servername}}</td>
                    <td>{{smtplist.status}}</td>
                    <td>
                        <a href="javascript:void(0);" ng-click="SetSmtpDetail(smtplist);" class="smtp_action" onClick="smtpActionDropdown()">
                            <i class="icon-setting">&nbsp;</i>
                        </a>
                    </td>
                </tr>                  
            </table>
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

</div>
        </div>
            <!--Actions Dropdown menu-->
            <ul class="action-dropdown smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if(in_array(getRightsId('smtp_settings_make_inactive_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionInactive" ng-hide="SmtpStatus==1;"><a ng-hide="isDefaultSMTP==1" ng-click="SetSmtpSettingStatus('inactive');" href="javascript:void(0);"><?php echo lang('MakeInactive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('smtp_settings_make_active_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionActive" ng-hide="SmtpStatus==2;"><a ng-hide="isDefaultSMTP==1" ng-click="SetSmtpSettingStatus('active');" href="javascript:void(0);"><?php echo lang('MakeActive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('smtp_settings_save_edit_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionEdit"><a ng-click="editSmtpSetting()" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('smtp_settings_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionDelete" ng-hide="isDefaultSMTP==1"><a ng-click="SetSmtpSettingStatus('delete');" href="javascript:void(0);"><?php echo lang('Delete'); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
</div>
</section>
<input type="hidden" name="hdnSelectallPermission" id="hdnSelectallPermission" value="<?php echo $selectall_permission; ?>"/>

<!--Popup for active/Inactive a smtp setting  -->
<div class="popup confirme-popup animated" id="confirmeSingleSmtpSetting">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeSingleSmtpSetting', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p class="text-center">{{confirmationMessage}}</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onclick="closePopDiv('confirmeSingleSmtpSetting', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="updateSingleSmtpSettingStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
        </div>
    </div>
</div>      
<!--Popup for active/Inactive a smtp setting  -->

<!--Popup for active/Inactive a smtp setting  -->
<div class="popup confirme-popup animated" id="confirmeMultipleSettingPopup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleSettingPopup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p class="text-center">{{confirmationMessage}}</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onclick="closePopDiv('confirmeMultipleSettingPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="updateMultipleSmtpSettingStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
        </div>
    </div>
</div>      
<!--Popup for active/Inactive a smtp setting  -->