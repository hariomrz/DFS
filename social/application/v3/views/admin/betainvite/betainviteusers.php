<?php 
$user_type = 'invited_users';
if(in_array(getRightsId('beta_invite_invited_users'), getUserRightsData($this->DeviceType))){ 
    $user_type = "invited_users";
}else if(in_array(getRightsId('beta_invite_not_joined_yet'), getUserRightsData($this->DeviceType))){
    $user_type = "not_joined_yet";
}else if(in_array(getRightsId('beta_invite_deleted_users'), getUserRightsData($this->DeviceType))){
    $user_type = "deleted_users";
}else if(in_array(getRightsId('beta_invite_removed_access_users'), getUserRightsData($this->DeviceType))){
    $user_type = "removed_access_users";
}
$selectall_permission = 0;
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><a target="_self" href="<?php echo base_url('admin/betainvite'); ?>"><?php echo lang('BetaInvite'); ?></a></span></li>
                    <li><span>{{InviteUserText}}</span></li>
                    <li class="sub-navigation dropdown">
                        <button class="btn btn-default btn-sm" data-toggle="dropdown"><i class="icon-arrow"></i> </button>
                        <ul class="dropdown-menu">
                            <?php if(in_array(getRightsId('beta_invite_invited_users'), getUserRightsData($this->DeviceType))){ ?>
                                <li id="lijoined" ng-click="setInviteUserStatus(2);" class="selected"><a href="javascript:void(0)"><?php echo lang('BetaInvite_JoinedUsers'); ?></a></li>
                            <?php } ?>
                            <?php if(in_array(getRightsId('beta_invite_not_joined_yet'), getUserRightsData($this->DeviceType))){ ?>
                                <li id="linotjoined" ng-click="setInviteUserStatus(1);" class=""><a href="javascript:void(0)"><?php echo lang('BetaInvite_NotJoinedYet'); ?></a></li>
                            <?php } ?>
                            <?php if(in_array(getRightsId('beta_invite_deleted_users'), getUserRightsData($this->DeviceType))){ ?>
                                <li id="lideleted" ng-click="setInviteUserStatus(3);" class=""><a href="javascript:void(0)"><?php echo lang('BetaInvite_DeletedUsers'); ?></a></li>
                            <?php } ?>
                            <?php if(in_array(getRightsId('beta_invite_removed_access_users'), getUserRightsData($this->DeviceType))){ ?>
                                <li id="liremovedaccess" ng-click="setInviteUserStatus(4);" class=""><a href="javascript:void(0)"><?php echo lang('BetaInvite_RemovedAccessUsers'); ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
            
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="BetainviteCtrl" id="BetainviteCtrl" ng-init="loadUsersByType('<?php echo $user_type; ?>')">

    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2">{{InviteUserText}}</span> ({{totalUsers}})</h2>
        <div class="info-row-right rightdivbox">
            <?php if(in_array(getRightsId('beta_invite_send_beta_invite'), getUserRightsData($this->DeviceType))){ ?>
                <a href="<?php echo base_url(); ?>admin/betainvite/sendbetainvite" class="button float-right marl10"><?php echo lang('SendBetaInvite'); ?></a>
            <?php } ?>
            <?php if(in_array(getRightsId('beta_invite_download_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="btn-link download_link" ng-click="downloadBetaUsers();">
                    <ins class="buttion-icon"><i class="icon-download"></i></ins>
                    <span><?php echo lang("User_Index_Download"); ?></span>
                </a>
            <?php } ?>
            <div id="search_box_div" class="text-field search-field" data-type="focus">
                <div class="search-block">
                    <input type="text" value="" id="searchField">
                    <div class="search-remove">
                        <i class="icon-close10" ng-click="clearBetaSearch();" id="betaClearText">&nbsp;</i>
                    </div>
                </div> 
                <input type="button" id="betaSearch" ng-click="searchData();" class="icon-search search-btn">
            </div>

            <?php if(in_array(getRightsId('beta_invite_remove_access_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('beta_invite_grant_access_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('beta_invite_reinvite_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('beta_invite_delete_event'), getUserRightsData($this->DeviceType))){ 
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
                    <?php if(in_array(getRightsId('beta_invite_remove_access_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-show="inviteUserStatus == 2" ng-click="changeMultipleBetaInviteUserStatus('removeaccess');"><?php echo lang('RemoveAccess'); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('beta_invite_grant_access_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-show="inviteUserStatus == 4" ng-click="changeMultipleBetaInviteUserStatus('grantaccess');"><?php echo lang('GrantAccess'); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('beta_invite_reinvite_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-show="inviteUserStatus == 1" ng-click="changeMultipleBetaInviteUserStatus('reinvite');"><?php echo lang('Reinvite'); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('beta_invite_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-hide="inviteUserStatus == 3" ng-click="changeMultipleBetaInviteUserStatus('delete');"><?php echo lang('Delete'); ?></a></li>
                    <?php } ?>
                </ul>
                <div class="total-count-view"><span class="counter">0</span> </div>
            </div>
        </div>
    </div>
    <!--/Info row-->    
    <div class="row-flued" ng-cloak id="inviteuserlistdiv">
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
                <table class="table table-hover registered-user">
                    <tbody>
                    <tr>
                        <th id="name" class="ui-sort" ng-click="orderByField = 'name'; reverseSort = !reverseSort; sortBY('name')">                           
                            <div class="shortdiv">Users Name<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="email" class="ui-sort" ng-click="orderByField = 'email'; reverseSort = !reverseSort; sortBY('email')">
                            <div class="shortdiv">Email<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="created_date" class="ui-sort selected" ng-click="orderByField = 'created_date'; reverseSort = !reverseSort; sortBY('created_date')">
                            <div class="shortdiv sortedUp">Invite Date<span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="code" class="ui-sort" ng-click="orderByField = 'code'; reverseSort = !reverseSort; sortBY('code')">
                            <div class="shortdiv">Code<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="modified_date" class="ui-sort" ng-show="inviteUserStatus==4" ng-click="orderByField = 'modified_date'; reverseSort = !reverseSort; sortBY('modified_date')">
                            <div class="shortdiv">Removed Access Date<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="lastlogindate" class="ui-sort" ng-hide="inviteUserStatus==1" ng-click="orderByField = 'lastlogindate'; reverseSort = !reverseSort; sortBY('lastlogindate')">
                            <div class="shortdiv">Last Login<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="modified_date" class="ui-sort" ng-show="inviteUserStatus==3" ng-click="orderByField = 'modified_date'; reverseSort = !reverseSort; sortBY('modified_date')">
                            <div class="shortdiv">Deleted Date<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th id="register_email" class="ui-sort" ng-show="inviteUserStatus==2 || inviteUserStatus==4" ng-click="orderByField = 'register_email'; reverseSort = !reverseSort; sortBY('register_email')">
                            <div class="shortdiv">Registered Email<span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th ng-hide="inviteUserStatus == 3">Actions</th>
                    </tr>

                    <tr class="rowtr" ng-repeat="userlist in listData[0].ObjUsers" ng-class="{selected : isSelected(userlist)}" ng-init="userlist.indexArr=$index" ng-click="selectCategory(userlist);">
                        <td>
                            {{userlist.name}}
                        </td>
                        <td><a rel="tipsy" class="icon-email" href="mailto:{{userlist.email}}" original-title="mailto:{{userlist.email}}">&nbsp;</a></td>
                        <td>{{userlist.created_date}}</td>
                        <td>{{userlist.code}}</td>
                        <td ng-show="inviteUserStatus==4">{{userlist.modified_date}}</td>
                        <td ng-hide="inviteUserStatus==1">{{userlist.lastlogindate}}</td>
                        <td ng-show="inviteUserStatus==3">{{userlist.modified_date}}</td>
                        <td ng-show="inviteUserStatus==2 || inviteUserStatus==4"><a rel="tipsy" class="icon-email" href="mailto:{{userlist.register_email}}" original-title="mailto:{{userlist.register_email}}">&nbsp;</a></td>
                        <td ng-hide="inviteUserStatus == 3">
                            <a href="javascript:void(0);"  ng-click="SetBetaInvite(userlist);" class="user-action" onClick="userActiondropdown()">
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

            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if(in_array(getRightsId('beta_invite_remove_access_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li ng-show="inviteUserStatus == 2" id="ActionRemoveAccess"><a ng-click="changeBetaInviteUserStatus('removeaccess')" href="javascript:void(0);"><?php echo lang('RemoveAccess'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('beta_invite_grant_access_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li ng-show="inviteUserStatus == 4" id="ActionGrantAccess"><a ng-click="changeBetaInviteUserStatus('grantaccess')" href="javascript:void(0);"><?php echo lang('GrantAccess'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('beta_invite_reinvite_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li ng-show="inviteUserStatus == 1" id="ActionReinvite"><a ng-click="changeBetaInviteUserStatus('reinvite')" href="javascript:void(0);"><?php echo lang('Reinvite'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('beta_invite_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li ng-hide="inviteUserStatus == 3" id="ActionDelete"><a ng-click="changeBetaInviteUserStatus('delete')" href="javascript:void(0);"><?php echo lang('Delete'); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
    <div id="accessdenieddiv"></div>
    
    <!--Popup for change error log status -->
    <div class="popup confirme-popup animated" id="confirmeBetaInvitePopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeBetaInvitePopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeBetaInvitePopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateBetaInviteUserStatus('confirmeBetaInvitePopup')"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>      
    <!--Popup for change error log status -->    
    <input type="hidden" name="hdnSelectallPermission" id="hdnSelectallPermission" value="<?php echo $selectall_permission; ?>"/>
</div>
</section>