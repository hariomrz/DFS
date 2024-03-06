<?php
$default_value = '';
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <ul class="bread-crumb-nav clearfix">
            <li><span><a target="_self" href="<?php echo base_url('admin/users'); ?>"><?php echo lang('User_Index_Users'); ?></a></span></li>
            <li><i class="icon-rightarrow">&nbsp;</i></li>
            <li><span><?php echo lang("User_Index_RegisteredUsers"); ?></span></li>
            <li class="sub-navigation dropdown">
                <button class="btn btn-default btn-sm" data-toggle="dropdown"><i class="icon-arrow"></i> </button>
                <ul class="dropdown-menu">
                    <?php if(in_array(getRightsId('registered_user'), getUserRightsData($this->DeviceType))){ ?>
                        <li class="selected" id="liregister"><a href="javascript:void(0)" onclick="SetUserStatus(2);" id="lnkRegisteredUser"><?php echo lang("User_Index_RegisteredUsers"); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('deleted_user'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="lidelelte"><a href="javascript:void(0)" onclick="SetUserStatus(3);" id="lnkDeletedUser"><?php echo lang("User_Index_DeletedUsers"); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('blocked_user'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="liblock"><a href="javascript:void(0)" onclick="SetUserStatus(4);" id="lnkBlockedUser"><?php echo lang("User_Index_BlockedUsers"); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('waiting_for_approval'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="lipending"><a href="javascript:void(0)" onclick="SetUserStatus(1);" id="lnkWaitingUser"><?php echo lang("User_Index_WaitingForApproval"); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('suspended_user'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="lisuspended"><a href="javascript:void(0)" onclick="SetUserStatus(23);" id="lnkSuspendedUser">Suspended Users</a></li>
                    <?php } ?>
                </ul>
            </li>
        </ul> 
    </div>
</div>
<!--/Bread crumb-->

<section class="main-container">
<div ng-controller="UserListCtrl" id="UserListCtrl" class="container">
<?php $this->load->view('admin/users/persona/user_persona');?>
<!--Info row-->
<div class="page-heading">
    <div class="row">
        <div class="col-xs-3">
            <h2 class="page-title"><?php echo lang("User_Index_RegisteredUsers"); ?></span> ({{totalUsers}})</h2>
        </div>
        <div class="col-xs-9">
            <div class="page-actions row-flued">
                <div class="row ">
                    <div class="col-xs-10 col-xs-offset-2">
                        <div class="row gutter-5">
                            <div class="col-sm-8">
                                <div class="form-group clearfix">
                                    <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('suspended_user'), getUserRightsData($this->DeviceType))){ ?>
                                        <div id="selectallbox" class="text-field selectbox" >
                                            <span>
                                                <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                                            </span>
                                            <label for="selectAll"><?php echo lang("Select_All"); ?></label>
                                        </div>
                                    <?php } ?>
                                    
                                    <div id="ItemCounter" class="items-counter">
                                        <ul class="button-list">
                                            <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                                <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php echo lang("User_Index_Communicate"); ?></a></li>
                                            <?php } ?>
                                            <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                                <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php echo lang("User_Index_Block"); ?></a></li>   
                                            <?php } ?>
                                            <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                                <li><a href="javascript:void(0);" ng-hide="userStatus==3" ng-click="SetMultipleUserStatus('delete');"><?php echo lang("User_Index_Delete"); ?></a></li>
                                            <?php } ?>
                                            <?php if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                                <li><a href="javascript:void(0);" ng-show="userStatus==4" ng-click="SetMultipleUserStatus('unblock');"><?php echo lang("User_Index_Unblock"); ?></a></li>
                                            <?php } ?>
                                            <?php if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                                                <li><a href="javascript:void(0);" ng-show="userStatus==1" ng-click="SetMultipleUserStatus('approve');"><?php echo lang("User_Index_Approve"); ?></a></li>
                                            <?php } ?>

                                        </ul>
                                        <div class="total-count-view"><span class="counter">0</span> </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-icon right search-group open">
                                    <a class="icons search-icon" id="searchButton">                                          
                                        <svg class="svg-icons" id="search" width="14px" height="14px">
                                            <use xlink:href="<?php echo base_url('assets/admin/img/sprite.svg#searchIco');?>"></use>
                                        </svg>
                                    </a>                                       
                                    <input type="text" class="form-control" placeholder="Search" value="" id="searchField">
                                </div>                                
                            </div>
                            <div class="col-sm-2">
                                <?php if(in_array(getRightsId('download_users_event'), getUserRightsData($this->DeviceType))){ ?>
                                    <a class="btn btn-default" ng-click="downloadUsers();">
                                        <span class="icn">
                                            <i class="ficon-arrow-long-up "></i>
                                        </span>
                                        <span class="text"><?php echo lang("User_Index_Download"); ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <div class="info-row row-flued">
    <h2><span id="spnh2"><?php echo lang("User_Index_RegisteredUsers"); ?></span> ({{totalUsers}})</h2>
    <div class="info-row-right rightdivbox">
        <?php if(in_array(getRightsId('download_users_event'), getUserRightsData($this->DeviceType))){ ?>
            <a href="javascript:void(0);" class="btn-link download_link" ng-click="downloadUsers();">
                <ins class="buttion-icon"><i class="icon-download"></i></ins>
                <span><?php echo lang("User_Index_Download"); ?></span>
            </a>
        <?php } ?>

        <div class="text-field search-field" data-type="focus">
            <div class="search-block">
                <input type="text" value="" id="searchField">
                <div class="search-remove">
                    <i class="icon-close10" id="clearText">&nbsp;</i>
                </div>
            </div> 
            <input type="button" id="searchButton" class="icon-search search-btn">
        </div>
        <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('suspended_user'), getUserRightsData($this->DeviceType))){ ?>
            <div id="selectallbox" class="text-field selectbox" >
                <span>
                    <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                </span>
                <label for="selectAll"><?php echo lang("Select_All"); ?></label>
            </div>
        <?php } ?>
        
        <div id="ItemCounter" class="items-counter">
            <ul class="button-list">
                <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php echo lang("User_Index_Communicate"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php echo lang("User_Index_Block"); ?></a></li>   
                <?php } ?>
                <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-hide="userStatus==3" ng-click="SetMultipleUserStatus('delete');"><?php echo lang("User_Index_Delete"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-show="userStatus==4" ng-click="SetMultipleUserStatus('unblock');"><?php echo lang("User_Index_Unblock"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-show="userStatus==1" ng-click="SetMultipleUserStatus('approve');"><?php echo lang("User_Index_Approve"); ?></a></li>
                <?php } ?>

            </ul>
            <div class="total-count-view"><span class="counter">0</span> </div>
        </div>
    </div>
</div> -->
<!--/Info row-->


    <div class="panel panel-secondary">
        <div class="panel-body">
        <!-- Pagination -->
        <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
        <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
        <!-- Pagination -->   
        <table class="table table-hover" id="userlist_table">
            <thead>
                <tr>
                <th id="username" class="ui-sort selected" ng-click="orderByField = 'username'; reverseSort = !reverseSort; sortBY('username')">                           
                    <div class="shortdiv sortedDown">Users Name<span class="icon-arrowshort">&nbsp;</span></div>
                </th>
                <th id="type" class="ui-sort" ng-click="orderByField = 'type'; reverseSort = !reverseSort; sortBY('type')">
                    <div class="shortdiv">User Type<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                </th>
                <th id="email" class="ui-sort" ng-click="orderByField = 'email'; reverseSort = !reverseSort; sortBY('email')">
                    <div class="shortdiv">Email<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="resgisdate" class="ui-sort" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">
                    <div class="shortdiv">Registered Date<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="sourceicon" class="ui-sort" ng-click="orderByField = 'sourceicon'; reverseSort = !reverseSort; sortBY('sourceicon')">
                    <div class="shortdiv">Type<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th ng-hide="userStatus == 1 || userStatus == 3 || userStatus == 4" id="lastlogindate" class="ui-sort" ng-click="orderByField = 'lastlogindate'; reverseSort = !reverseSort; sortBY('lastlogindate')">
                    <div class="shortdiv">Last Login<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th ng-show="userStatus == 3" id="deleteddate" class="ui-sort" ng-click="orderByField = 'modifieddate'; reverseSort = !reverseSort; sortBY('deleteddate')">
                    <div class="shortdiv">Deleted On<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th ng-show="userStatus == 4" id="blockeddate" class="ui-sort" ng-click="orderByField = 'modifieddate'; reverseSort = !reverseSort; sortBY('blockeddate')">
                    <div class="shortdiv">Blocked On<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr class="rowtr" ng-repeat="userlist in listData[0].ObjUsers" ng-class="{selected : isSelected(userlist)}" ng-init="userlist.indexArr=$index" ng-click="selectCategory(userlist);">
                <td>
                    <a href="#" ng-click="viewUserProfile(userlist.userguid)" class="thumbnail40" title="Click to view profile" rel="tipsynw">                                        
                        <img ng-src="{{userlist.profilepicture}}" >
                    </a>
                    <a href="#" class="name" ng-click="viewUserProfile(userlist.userguid)">{{userlist.username}}</a>
                </td>
                <td>{{userlist.type}}</td>
                <td><a rel="tipsy" class="icon-email" href="mailto:{{userlist.email}}" original-title="mailto:{{userlist.email}}">&nbsp;</a></td>
                <td>{{userlist.resgisdate}}</td>
                <td><i class="{{userlist.sourceicon}}"></i></td>
                <!--<i class="iocn-twitter"> </i>-->
                <td ng-hide="userStatus == 1 || userStatus == 3 || userStatus == 4">{{userlist.lastlogindate}}</td>
                <td ng-show="userStatus == 3 || userStatus == 4">{{userlist.modifieddate}}</td>
                <td>
                    <a href="#"  ng-click="SetUser(userlist);" class="user-action" onClick="userActiondropdown()">
                        <i class="icon-setting">&nbsp;</i>
                    </a>
                </td>
            </tr>   
            </tbody>
        </table>
        <!-- Pagination -->
        <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
        <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
        <!-- Pagination -->
        </div>
    </div>
    <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    <!--Actions Dropdown menu-->
    <ul class="dropdown-menu userActiondropdown" style="display: none;">
        <?php if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionApprove" style="display: none;"><a onclick="SetStatus(1);" href="javascript:void(0);"><?php echo lang("User_Index_Approve"); ?></a></li>   
        <?php } ?>
        <?php if(in_array(getRightsId('suspended_user'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionSuspended" style="display: none;"><a onclick="SetStatus(23);" href="javascript:void(0);">Unsuspend</a></li>   
        <?php } ?>
        <?php if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionUnblock" style="display: none;"><a onclick="SetStatus(2);" href="javascript:void(0);"><?php echo lang("User_Index_Unblock"); ?></a></li>
        <?php } ?>
        <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionDelete" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1"><a onclick="SetStatus(3);" href="javascript:void(0);"><?php echo lang("User_Index_Delete"); ?></a></li>
        <?php } ?>
        <?php if(in_array(getRightsId('login_as_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionLoginThis" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1"><a href="javascript:void(0);" ng-click="autoLoginUser(userlist.userid)"><?php echo lang("LoginAsThisUser"); ?></a></li>
        <?php } ?>
        <?php if(in_array(getRightsId('user_profile'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionViewProfile"><a href="javascript:void(0);" ng-click="viewUserProfile(userlist.userguid)"><?php echo lang("User_Index_ViewProfile"); ?></a></li> 
        <?php } ?>
        <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionBlock" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1"><a onclick="SetStatus(4);" href="javascript:void(0);"><?php echo lang("User_Index_Block"); ?></a></li>
        <?php } ?>
        <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionCommunicate" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1"><a href="javascript:void(0);" data-toggle="modal" data-target="#communicate_single_user"><?php echo lang("User_Index_Communicate"); ?></a></li>
        <?php } ?>
        <?php if(in_array(getRightsId('change_password_event'), getUserRightsData($this->DeviceType))){ ?>
            <li id="ActionChangePwd" ng-hide="currentUserStatusId == 3"><a href="javascript:void(0);" onclick="SetStatus(5);"><?php echo lang("User_Index_ChangePassword"); ?></a></li>
        <?php } ?>
        <li id="ActionChangePwd" data-toggle="modal" ng-click="getUserPersonaDetail(userlist.userid);"><a href="javascript:void(0);">User Persona</a></li>
    </ul>
    <!--/Actions Dropdown menu-->


<!--Popup for Delete a user  -->
<div class="popup confirme-popup animated" id="delete_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button"  ng-click="ChangeStatus('delete_popup');" id="button_on_delete" name="button_on_delete">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
<!--Popup end Delete a user  -->

<!--Popup for Block a user  -->
<div class="popup confirme-popup animated" id="block_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('block_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p><?php echo lang('Sure_Block'); ?> <b>{{currentUserName}}</b>?</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('block_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="ChangeStatus('block_popup');" id="button_on_block" name="button_on_block">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
<!--Popup end Block a user  -->


<!--Popup for UnBlock a user  -->
<div class="popup confirme-popup animated" id="unblock_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('unblock_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p><?php echo lang('Sure_Unblock'); ?> <b>{{currentUserName}}</b>?</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('unblock_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="ChangeStatus('unblock_popup');" id="button_on_unblock" name="button_on_unblock">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
<!--Popup end UnBlock a user  -->

<!--Popup for Approve a user  -->
<div class="popup confirme-popup animated" id="approve_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('approve_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p><?php echo lang('Sure_Approve'); ?> ?</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('approve_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" onClick="ChangeStatus('approve_popup');" id="button_on_approve" name="button_on_approve">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
<!--Popup end Approve a user  -->

<!--Popup for unsuspended a user  -->
<div class="popup confirme-popup animated" id="suspended_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('suspended_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p>Are you sure you want to unsuspend this account.</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('suspended_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" onClick="ChangeStatus('suspended_popup');" id="button_on_suspended" name="button_on_suspended">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
<!--Popup end Approve a user  -->

<!-------------------------push notificaton----------------------------------->
<!-- Send Email To All Users -->
    <div id="send_email_all_model" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" data-ng-bind="lang.manage_send_email"></h5>
                </div>
                <!-- Inactive User modal -->
                <form  role="form" sendemail-all-form submit-handle="sendEmailAllUsers()">
                    <div class="modal-body has-padding">
                        <div class="form-group">
                            <label data-ng-bind="lang.selected_email"></label>
                            <div class="form-control" id="all_selected_email" disabled="" name="all_selected_email">All Users Selected</div>
                        </div>
                        <div class="form-group">
                            <label data-ng-bind="lang.subject"></label>
                            <input type="text" class="form-control" id="all_subject" name="all_subject" ng-model="userObj.subject">
                            <label for="all_subject" class="error hide" id="all_subject_error"></label>
                        </div>
                        <div class="form-group">
                            <label data-ng-bind="lang.message"></label>
                            <textarea class="form-control" id="all_message" name="all_message" ng-model="userObj.message"></textarea>
                            <label for="all_message" class="error hide" id="all_message_error"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal" ng-click="userObj={};userObj.user_unique_id=[];deselectUser();" data-ng-bind="lang.close"></button>
                        <button type="submit" class="btn btn-primary"><i class=""></i>{{lang.send_email}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Send Email To All Users Model -->

    <!-- Send Email To All Users -->
    <div id="send_email_selected_model" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" data-ng-bind="lang.manage_send_email"></h5>
                </div>
                <!-- Inactive User modal -->
                <form role="form" sendemail-form submit-handle="sendEmailSelectedUser();">
                    <div class="modal-body has-padding">
                        <div class="form-group">
                            <label data-ng-bind="lang.selected_email"></label>
                            <textarea class="form-control" id="selected_email" disabled="" name="selected_email" ng-bind="userObj.selected_emails">
                            </textarea>
                        </div>
                        <label for="selected_email" class="error hide" id="selected_email_error"></label>
                        
                        <div class="form-group">
                            <label data-ng-bind="lang.subject"></label>
                            <input type="text" class="form-control" id="subject" name="subject" ng-model="userObj.subject">
                            <label for="subject" class="error hide" id="subject_error"></label>
                        </div>
                        <div class="form-group">
                            <label data-ng-bind="lang.message"></label>
                            <textarea class="form-control" id="message" name="message" ng-model="userObj.message"></textarea>
                            <label for="message" class="error hide" id="message_error"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal" ng-click="userObj={};userObj.user_unique_id=[];deselectUser();" data-ng-bind="lang.close"></button>
                        <button type="submit" class="btn btn-primary"><i class=""></i>{{lang.send_email}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send Email To All Users Model -->
<!-------------------------push notificaton----------------------------------->


</div>
</section>

<input type="hidden" value="<?php echo $UserStatus; ?>" id="hdnUserStatus">
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>

<div class="modal fade" tabindex="-1" role="dialog" id="communicate_single_user" ng-controller="messageCtrl"> 
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
                <span aria-hidden="true"><i class="icon-close"></i></span> 
              </button>
              <h4 class="modal-title"><?php echo lang('User_Index_Communicate'); ?></h4>
            </div>
            <div class="modal-body">
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
                    <div class="form-group">
                        <label for="subjects" class="label">Subject</label>
                            <input type="text" class="form-control" value="" name="Subject" id="emailSubject" >
                        <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
                    </div>
                    <div class="text-msz editordiv">
                        <textarea id="description" name="description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                        <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button ng-click="sendEmail(user,'users')" class="btn btn-primary pull-right" type="submit" id="btnCommunicateSingle"><?php echo lang('Submit'); ?></button>
            </div>
         </div>
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
            <div class="text-msz editordiv">
                <?php //echo $this->ckeditor->editor('communication_description', @$default_value); ?>
                <textarea id="communication_description" name="communication_description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
            </div>
            <input type="hidden" name="hdnUsersId" id="hdnUsersId" value=""/>
            <button ng-click="sendEmailToMultipleUsers('users')" class="button float-right" type="submit" id="btnCommunicateMultiple"><?php echo lang('Submit'); ?></button>
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
