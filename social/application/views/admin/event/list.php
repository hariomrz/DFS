<?php
$default_value = '';
?>

<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Events</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="main-container">
<div class="container" ng-controller="EventListCtrl" id="EventListCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id=""><?php echo lang('total_events'); ?></span> ({{total_events}})</h2>
        <div class="info-row-right rightdivbox">
            <?php /*if(in_array(getRightsId('download_users_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="btn-link download_link" ng-click="download_page_list();">
                    <ins class="buttion-icon" style="margin: 0;"><i class="icon-download"></i></ins>
                    <span><?php echo lang("User_Index_Download"); ?></span>
                </a>
            <?php }*/ ?>

            <div class="text-field search-field" data-type="focus">
                <div class="search-block">
                    <input type="text" ng-model="search_university_model" value="" id="searchEventField">
                    <div class="search-remove">
                        <i class="icon-close10" ng-click="pages_reset_search();">&nbsp;</i>
                    </div>
                </div> 
                <input type="button" id="searchEventButton" class="icon-search search-btn">
            </div>

            
            <div id="ItemCounter" class="items-counter">
                <ul class="button-list">
                    <?php //if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <!-- <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php //echo lang("User_Index_Communicate"); ?></a></li> -->
                    <?php //} ?>
                    <?php //if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <!-- <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php //echo lang("User_Index_Block"); ?></a></li> -->   
                    <?php //} ?>
                    <?php /*if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-hide="userStatus==3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                    <?php }*/ ?>

                    <?php /*if(in_array(getRightsId('feature_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_FeatureEvent"); ?></a></li>
                    <?php }*/ ?>

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
        <!--Popup for Delete a event  -->
        <div class="popup confirme-popup animated" id="delete_popup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button" ng-click="delete_event();" id="button_on_delete" name="button_on_delete">
                        <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div> 
        <!--Popup for Feature a event  -->
        <div class="popup confirme-popup animated" id="feature_popup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('feature_popup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p><?php echo lang('Sure_Feature'); ?> <b>{{currentUserName}}</b>?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onClick="closePopDiv('feature_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button" ng-click="feature_event(1);" id="button_on_delete" name="button_on_delete">
                        <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div> 

        <!--Popup for remove Feature a event  -->
        <div class="popup confirme-popup animated" id="feature_remove_popup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('feature_remove_popup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p><?php echo lang('Sure_Remove_Feature'); ?> <b>{{currentUserName}}</b>?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onClick="closePopDiv('feature_remove_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button" ng-click="feature_event(0);" id="button_on_delete" name="button_on_delete">
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
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="total_events" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="total_events" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
        <table class="table table-hover universities" id="userlist_table">
            <tbody>
            <tr>
                <th id="Title" class="ui-sort selected" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('Title')">                           
                    <div class="shortdiv sortedDown"><?php echo lang('Title'); ?><span class="icon-arrowshort">&nbsp;</span></div>
                </th>
                <th id="Privacy" class="ui-sort" ng-click="orderByField = 'Privacy'; reverseSort = !reverseSort; sortBY('Privacy')">                           
                    <div class=""><?php echo lang('privacy'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
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
                        <img ng-src="{{Pages.EventCover}}" >
                    </a>
                    <a href="#" class="name">{{Pages.Title}}</a>
                </td>
                <td ng-bind="Pages.Privacy"></td>
                <td ng-bind="Pages.CreatedDate"></td>
                <td ng-bind="Pages.CreatedBy.FirstName+' '+Pages.CreatedBy.LastName"></td>
                <td>
                    <a href="#"  ng-click="set_page_data(Pages);" class="user-action" onClick="userActiondropdown()">
                        <i class="icon-setting">&nbsp;</i>
                    </a>
                </td>
            </tr>   
            </tbody>
        </table>
        
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="total_events" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="total_events" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
            <li><a href="javascript:void(0);" onclick="openPopDiv('delete_popup', 'bounceOutDown');"><?php echo lang('Delete');?></a></li>
            <li><a href="javascript:void(0);" ng-show="IsFeatured == '0'" onclick="openPopDiv('feature_popup', 'bounceOutDown');"><?php echo lang('FeatureEvent');?></a></li>   
            <li><a href="javascript:void(0);" ng-show="IsFeatured == '1'" onclick="openPopDiv('feature_remove_popup', 'bounceOutDown');"><?php echo lang('RemoveFeatureEvent');?></a></li>   
            <!-- <li><a href="javascript:void(0);">Block</a></li> -->
            
        </ul>
        <!--/Actions Dropdown menu-->
    </div>

    <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
</div>
</section>

<input type="hidden" value="<?php //echo $UserStatus; ?>" id="hdnUserStatus">
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
<input type="hidden"  name="" id="pageName" value="<?php //echo $page_name;?>"/>
