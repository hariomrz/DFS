<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('EmailSettings'); ?></li>
                    <li>/</li>
                    <li><span><?php echo lang('Emails'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">

<div class="container"  ng-controller="SmtpEmailsCtrl" id="SmtpEmailsCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('Emails'); ?></span> ({{totalEmails}})</h2>
        <div class="info-row-right"></div>
    </div>
    <!--/Info row-->

    <div class="row-flued">
        <div class="panel panel-secondary">
                <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover email_type_table">
                <tr>
                    <th id="name" class="ui-sort selected" ng-click="orderByField = 'name'; reverseSort = !reverseSort; sortBY('name')">                           
                        <div class="shortdiv sortedDown">Email<span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th id="fromemail" class="ui-sort" ng-click="orderByField = 'fromemail'; reverseSort = !reverseSort; sortBY('fromemail')">
                        <div class="shortdiv">Sent From<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                    </th>
                    <th id="subject" class="ui-sort" ng-click="orderByField = 'subject'; reverseSort = !reverseSort; sortBY('subject')">
                        <div class="shortdiv">Subject<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="statusid" class="ui-sort" ng-click="orderByField = 'statusid'; reverseSort = !reverseSort; sortBY('statusid')">
                        <div class="shortdiv">Status<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th>Actions</th>
                </tr>

                <tr class="rowtr" ng-repeat="emaillist in listData[0].ObjSMTP" ng-class="{ inactive_email: emaillist.statusid==1}">
                    <td>
                        <p>{{emaillist.name}}</p>
                    </td>                
                    <td><a rel="tipsy" href="mailto:{{emaillist.fromemail}}" original-title="{{emaillist.fromemail}}">{{emaillist.fromemail}}</a></td>
                    <td>{{emaillist.subject}}</td>
                    <td>{{emaillist.status}}</td>
                    <td>
                        <a href="javascript:void(0);" ng-click="SetEmailDetail(emaillist);" class="smtp_action user-action" onClick="smtpActionDropdown()">
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
            <ul class="dropdown-menu smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if(in_array(getRightsId('smtp_emails_make_inactive_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionInactive" ng-hide="EmailStatus==1"><a ng-click="SetSmtpEmailStatus('inactive');" href="javascript:void(0);"><?php echo lang('MakeInactive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('smtp_emails_make_active_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionActive" ng-hide="EmailStatus==2"><a ng-click="SetSmtpEmailStatus('active');" href="javascript:void(0);"><?php echo lang('MakeActive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('smtp_emails_edit_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionEdit"><a ng-click="editSmtpEmailDetails();" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
    
    <!--Popup for active/Inactive a smtp setting  -->
    <div class="popup confirme-popup animated" id="confirmeSingleSmtpEmailPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeSingleSmtpEmailPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeSingleSmtpEmailPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateSmtpEmailStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>      
    <!--Popup for active/Inactive a smtp setting  -->

    <!--Popup for Communicate/send message to a user -->
    <div class="popup communicate animated" id="editEmailTypePopup" ng-init="EmailSettingParams();">
        <div class="popup-title"><?php echo lang('EditEmailType'); ?> <i class="icon-close" onClick="closePopDiv('editEmailTypePopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content loader_parent_div">
            <i class="loader_email btn_loader_overlay"></i>
            <div class="communicate-footer row-flued">
                <input type="hidden" name="emailtypeid" id="emailtypeid" data-ng-model="mod.emailtypeid"/>
                <div class="from-subject">
                    <label for="fromemail" class="label"><?php echo lang('FromEmail'); ?></label>
                    <select class="width100" chosen data-disable-search="true" data-ng-options="item.Name+' ('+item.FromEmail+')' for item in emailSetting track by item.EmailSettingID" name="EmailSettingID" id="EmailSettingID" ng-model="selectedSettingId">
                        <option value=""></option>
                    </select>
                    <div class="error-holder" ng-show="showTypeError" style="color: #CC3300;">{{errorTypeMessage}}</div>
                </div>
                <div class="from-subject">
                    <label for="name" class="label"><?php echo lang('Name'); ?> </label>
                    <div class="text-field">
                        <input type="text" name="name" id="name" data-ng-model="mod.name" value="{{emaildata.name}}" readonly="1">
                    </div>
                </div>
                <div class="from-subject">
                    <label for="subject" class="label"><?php echo lang('Subject'); ?></label>
                    <div class="text-field">
                        <input type="text" name="subject" id="subject" data-ng-model="mod.subject" value="{{emaildata.subject}}">
                    </div>
                    <div class="error-holder" ng-show="showSubjectError" style="color: #CC3300;">{{errorSubjectMessage}}</div>
                </div>
            </div>        
            <button ng-click="updateEmailDetails(emailtypeid)" class="button float-right" type="submit" id="btnSaveEmailType"><?php echo lang('Submit'); ?></button>
            <button class="button wht float-right" onclick="closePopDiv('editEmailTypePopup', 'bounceOutUp');"><?php echo lang('Cancel'); ?></button>
        </div>
    </div>
    <!--Popup end Communicate/send message to a user -->
    
</div>

</section>