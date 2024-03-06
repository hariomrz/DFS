<?php
$default_value = '';
?>

<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Groups</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div class="container" ng-controller="PageListCtrl" id="PageListCtrl">
<!--Info row-->
<div class="info-row row-flued" ng-init="list();">
    <h2><span id=""><?php echo lang('total_groups'); ?></span> ({{total_pages}})</h2>
    <div class="info-row-right rightdivbox">
        <?php if(in_array(getRightsId('download_users_event'), getUserRightsData($this->DeviceType))){ ?>
            <a href="javascript:void(0);" class="btn-link download_link" ng-click="download_page_list();">
                <ins class="buttion-icon" style="margin: 0;"><i class="icon-download"></i></ins>
                <span><?php echo lang("User_Index_Download"); ?></span>
            </a>
        <?php } ?>

        <div class="text-field search-field" data-type="focus">
            <div class="search-block">
                <input type="text" ng-model="search_university_model" value="" id="searchField">
                <div class="search-remove">
                    <i class="icon-close10" id="clearText" ng-click="pages_reset_search();">&nbsp;</i>
                </div>
            </div> 
            <input type="button" id="searchButton" ng-click="search_pages();" class="icon-search search-btn">
        </div>

        <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
            <div id="selectallbox" class="text-field selectbox" >
                <span>
                    <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                </span>
                <label for="selectAll"><?php echo lang("Select_All"); ?></label>
            </div>
        <?php } ?>
        <div id="ItemCounter" class="items-counter">
            <ul class="button-list">
                <?php //if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php //echo lang("User_Index_Communicate"); ?></a></li> -->
                <?php //} ?>
                <?php //if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php //echo lang("User_Index_Block"); ?></a></li> -->   
                <?php //} ?>
                <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-hide="userStatus==3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                <?php } ?>
                <?php //if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-show="userStatus==4" ng-click="SetMultipleUserStatus('unblock');"><?php //echo lang("User_Index_Unblock"); ?></a></li> -->
                <?php //} ?>
                <?php //if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-show="userStatus==1" ng-click="SetMultipleUserStatus('approve');"><?php //echo lang("User_Index_Approve"); ?></a></li> -->
                <?php //} ?>
            </ul>
            <div class="total-count-view"><span class="counter">0</span> </div>
        </div>
        
    </div>
    <!--Popup for Delete a user  -->
    <div class="popup confirme-popup animated" id="delete_popup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="delete_page();" id="button_on_delete" name="button_on_delete">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>
            </div>
        </div>
    </div>
    <!--Popup end Delete a user  -->
    <div class="popup delete_confirm_popup animated" id="confirmeMultipleUniversityPopup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center"><?php echo lang('Sure_Delete')?></p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="delete_multiple_page()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>
    <div class="popup confirme-popup animated" id="Setsong_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('Setsong_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p>Team page owner: <span ng-bind="CreatedBy"></span></p>
        <label class="label">Change Owner</label>  
          <div class="text-field large">
          <input type="text" class="" ng-model="search_user" id="search-user" value="">
           <i class="icon-removed remove-owner hide"></i>
          </div>
          <div class="clearfix">&nbsp;</div>
          <div ng-bind="Error.error_Schollyme_ownername" class="error-holder usrerror ng-binding">ddfc</div>
          <div class="clearfix">&nbsp;</div>
          <div id="searchResult"></div>
          <input type="hidden" id="current_group_owner_guid" ng-model="current_group_owner_guid" value="">
          <input type="hidden" id="current_group_guid" ng-model="current_group_guid" value="">
          <input type="hidden" id="ownerguid" ng-model="ownerguid" value="">
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('Setsong_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="change_owner();" id="button_on_delete" name="button_on_delete">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>
</div>
    <!--/Info row-->
    <div class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="total_pages" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="total_pages" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
        <table class="table table-hover universities" id="userlist_table">
            <tbody>
            <tr>
                <th id="GroupName" class="ui-sort selected" ng-click="orderByField = 'GroupName'; reverseSort = !reverseSort; sortBY('GroupName')">                           
                    <div class="shortdiv sortedDown"><?php echo lang('Title'); ?><span class="icon-arrowshort">&nbsp;</span></div>
                </th>
                <th id="UEmail" class="ui-sort" >                           
                    <div class=""><?php echo lang('privacy'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="MemberCount" class="ui-sort"  ng-click="orderByField = 'MemberCount'; reverseSort = !reverseSort; sortBY('MemberCount')">                           
                    <div class="shortdiv sortedUp"><?php echo lang('member_count'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="CreatedDate" class="ui-sort" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">                           
                    <div class="shortdiv sortedDown"><?php echo lang('CreatedDate'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th id="PhoneNumber" class="ui-sort" >                           
                    <div class="shortdiv sortedDown"><?php echo lang('owner'); ?><span >&nbsp;</span></div>
                </th>
                 <th id="Position" class="ui-sort" >                           
                    <div class=""><?php echo lang('Actions'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
            </tr>
            <tr class="rowtr" ng-repeat="Pages in listData[0].ObjPages" ng-class="{selected : isSelected(Pages)}" ng-init="Pages.indexArr=$index" ng-click="selectCategory(Pages);">
                <td>
                    <a href="#" class="thumbnail40"  rel="tipsynw">                                        
                        <img ng-src="{{Pages.GroupCover}}" >
                    </a>
                    <a href="#" class="name">{{Pages.GroupName}}</a>
                </td>
                <td ng-bind="Pages.IsPublic"></td>
                <td ng-bind="Pages.MemberCount"></td>
                <td ng-bind="Pages.CreatedDate"></td>
                <td ng-bind="Pages.CreatedBy.FirstName+' '+Pages.CreatedBy.LastName"></td>
                <td><a href="#"  ng-click="set_page_data(Pages);" class="user-action" onClick="userActiondropdown()">
                        <i class="icon-setting">&nbsp;</i>
                    </a></td>
            </tr>   
            </tbody>
        </table>
        
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="total_pages" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="total_pages" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
            <li><a href="javascript:void(0);" onclick="openPopDiv('delete_popup', 'bounceOutDown');"><?php echo lang('Delete');?></a></li>   
            <!-- <li><a href="javascript:void(0);">Block</a></li> -->
            <li ng-show="page_type==1"><a href="javascript:void(0);" onclick="openPopDiv('Setsong_popup', 'bounceOutDown');"><?php echo lang('change_owner');?></a></li>
        </ul>
        <!--/Actions Dropdown menu-->
    </div>

    <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
</div>
</div>
</section>

<input type="hidden" value="<?php //echo $UserStatus; ?>" id="hdnUserStatus">
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
<input type="hidden"  name="" id="pageName" value="<?php //echo $page_name;?>"/>
