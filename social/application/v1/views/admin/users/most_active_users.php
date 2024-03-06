<?php 
$default_value = '';
$selectall_permission = 0;
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a href="<?php echo base_url('admin/users'); ?>"><?php echo lang('User_Index_Users'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('MostActiveUsers_MostActiveUsers'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<!--/Bread crumb-->
<section class="main-container">
<div ng-controller="MostActiveUserListCtrl" id="MostActiveUserListCtrl" class="container">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('MostActiveUsers_MostActiveUsers'); ?></span></h2>
        <div class="info-row-right rightdivbox">
            <?php if(in_array(getRightsId('analytic_download_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="btn-link download_link" ng-click="downloadMostActiveUsers();">
                    <ins class="buttion-icon"><i class="icon-download"></i></ins>
                    <span><?php echo lang("User_Index_Download"); ?></span>
                </a>
            <?php } ?>
            <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){
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
                    <?php /*if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="SetMultipleUserStatus('delete');"><?php echo lang("User_Index_Delete"); ?></a></li>
                    <?php }*/ ?>
                    <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php echo lang("User_Index_Communicate"); ?></a></li>   
                    <?php } ?>
                    <?php /*if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="SetMultipleUserStatus('block');"><?php echo lang("User_Index_Block"); ?></a></li>
                    <?php }*/ ?>
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
                <table class="table table-hover most_active_users_table">
                    <tr>
                        <th id="username" class="ui-sort selected" ng-click="orderByField = 'username'; reverseSort = !reverseSort; sortBY('username')">                           
                            <div class="shortdiv sortedDown">Name<span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="sessioncounts" class="ui-sort" ng-click="orderByField = 'sessioncounts'; reverseSort = !reverseSort; sortBY('sessioncounts')">
                            <div class="shortdiv">Sessions<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                        </th>
                        <th id="minutes" class="ui-sort" ng-click="orderByField = 'minutes'; reverseSort = !reverseSort; sortBY('minutes')">
                            <div class="shortdiv">Minutes<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="activitypercentile" class="ui-sort" ng-click="orderByField = 'activitypercentile'; reverseSort = !reverseSort; sortBY('activitypercentile')">
                            <div class="shortdiv">Activity Percentile<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th>Actions</th>
                    </tr>

                    <tr ng-repeat="userlist in listData[0].ObjUsers" ng-class="{selected : isSelected(userlist),notselected:isNotSelected(userlist)}" ng-init="userlist.indexArr=$index" ng-click="selectCategory(userlist);">
                        <td>
                            <a href="#" class="thumbnail40" ng-click="viewUserProfile(userlist.userguid)" title="Click to view profile" rel="tipsynw">                                        
                                <img ng-src="{{userlist.profilepicture}}" >
                            </a>
                            <a href="#" class="name zero_margin_top" ng-click="viewUserProfile(userlist.userguid)">{{userlist.username}}</a>
                            <p class="name-location">{{userlist.location}}</p>
                        </td>
                        <td>{{userlist.sessioncounts}}</td>
                        <td>{{userlist.minutes}}</td>
                        <td>{{userlist.activitypercentile}} %</td>
                        <td>
                            <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                <a href="javascript:void(0);" ng-click="SetUser(userlist);" onClick="openPopDiv('communicate_single_user', 'bounceInDown');"><?php echo lang("User_Index_Communicate"); ?></a>
                            <?php } ?>
                        </td>
                    </tr>                  
                </table>

            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            </div>
        </div>

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
</div>
</section>
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
<input type="hidden" name="hdnSelectallPermission" id="hdnSelectallPermission" value="<?php echo $selectall_permission; ?>"/>

<!--Popup for Communicate/send message to a user -->
<div class="popup communicate animated" id="communicate_single_user" ng-controller="messageCtrl">
    <div class="popup-title"><?php echo lang('User_Index_Communicate'); ?> <i class="icon-close" onClick="closePopDiv('communicate_single_user', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content loader_parent_div">
        <i class="loader_communication btn_loader_overlay"></i>
        <div class="user-detial-block">
            <a class="user-thmb" href="javascript:void(0);">
                <img ng-src="{{user.profilepicture}}" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser"></a>
            <div class="overflow">
                <a class="name-txt" href="javascript:void(0);" id="lnkUserName">{{user.firstname}} {{user.lastname}} </a>
                <div class="dob-id">
                    <span id="spnProcessDate">Member Since: {{user.membersince}} </span><br>
                    <a id="lnkUserEmail" href="javascript:void(0);">{{user.email}} </a>
                </div>
            </div>
        </div>
        
        <div class="communicate-footer row-flued">
            
            <div class="from-subject">
                <label for="subjects" class="label">Subject</label>
                <div class="text-field">
                    <input type="text" value="" name="Subject" id="emailSubject" >
                </div>
                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
            </div>
            <div class="text-msz">
                <?php //echo $this->ckeditor->editor('description', @$default_value); ?>
                <textarea id="description" name="description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
            </div>
        </div>

        <button ng-click="sendEmail(user,'analytic_users')" class="button float-right" type="submit" id="btnCommunicateSingle"><?php echo lang('Submit'); ?></button>
    </div>
</div>
<!--Popup end Communicate/send message to a user -->

<div class="communicate-morelist">
    <div id="dvtipcontent" class="tip-content"> <i class="icon-tiparrow">&nbsp;</i> </div>
</div>

<!--Popup for Communicate/send message to a multiple user -->
<div class="popup communicate animated" id="communicateMultiple" ng-controller="messageCtrl">    
    <div class="popup-title"><?php echo lang('User_Index_Communicate'); ?> <i class="icon-close" onClick="closePopDiv('communicateMultiple', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content loader_parent_div">
        <i class="loader_communication btn_loader_overlay"></i>
        <div class="multiple-comunicate" id="dvmorelist"></div>        
        <div class="communicate-footer row-flued">
            <div class="from-subject">                
                <label class="label" for="subject">Subject</label>
                <div class="text-field">
                    <input type="text" value="" name="Subject" id="Subject" >
                </div>
                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
            </div>
            <div class="text-msz">
                <?php //echo $this->ckeditor->editor('communication_description', @$default_value); ?>
                <textarea id="communication_description" name="communication_description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
            </div>
            <input type="hidden" name="hdnUsersId" id="hdnUsersId" value=""/>
            <button ng-click="sendEmailToMultipleUsers('analytic_users')" class="button float-right" type="submit" id="btnCommunicateMultiple"><?php echo lang('Submit'); ?></button>
        </div>
    </div>
</div>
<!--Popup end Communicate/send message to a multiple user -->

<div class="popup confirme-popup animated" id="confirmeMultipleUserPopup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p class="text-center">{{confirmationMessage}}</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="updateUsersStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
        </div>
    </div>
</div>