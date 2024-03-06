<?php
$selectall_permission = 0;
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a><a target="_self" href="<?php echo base_url('admin/analytics/media_analytics') ?>"><?php echo lang('Analytics'); ?></a></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('MediaAnalytics_MediaAnalytics'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--/Bread crumb-->
<section class="main-container">
<div ng-controller="MediaAnalyticsCtrl" id="MediaAnalyticsCtrl" ng-init="mediaAnalyticsReport();" class="container">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('MediaAnalytics_UserContentReportStatement'); ?></span> ({{totalMediaCount}})</h2>
        <div class="info-row-right mediaright">            
            <?php if(in_array(getRightsId('analytic_download_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="btn-link download_link" ng-click="downloadMediaAnalyticsData();">
                    <ins class="buttion-icon"><i class="icon-download"></i></ins>
                    <span><?php echo lang('User_Index_Download'); ?></span>
                </a>
            <?php } ?>
            <div class="text-field search-field" data-type="focus">
                <div class="search-block">
                    <input type="text" value="" id="searchField">
                    <div class="search-remove">
                        <i class="icon-close10" id="clearText">&nbsp;</i>
                    </div>
                </div> 
                <input type="button" id="mediaAnalyticSearch" ng-click="searchMediaUsers();" class="icon-search search-btn">
            </div>

            <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){
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
                    <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="SetMultipleUserStatus('block');"><?php echo lang("User_Index_Block"); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                        <li><a href="javascript:void(0);" ng-click="SetMultipleUserStatus('delete');"><?php echo lang("User_Index_Delete"); ?></a></li>
                    <?php } ?>
                </ul>
                <div class="total-count-view"><span class="counter">0</span> </div>
            </div>

        </div>
    </div>
    <!--/Info row-->

    <div class="user-mediadetail">
        <section class="user-detial">
            <div class="float-left">
                <label class="label media-label">Media</label>
                <ul class="payment-total-list">
                    <li class="blue login-view"><label>{{mediaReport.total_media}}</label>
                        <span><?php echo lang('TotalMedia'); ?> ({{mediaReport.total_size}})</span>
                    </li>
                    <li class="green"><label>{{mediaReport.video_count}}</label>
                        <span><?php echo lang('User_UserProfile_Videos'); ?> ({{mediaReport.video_size}})</span>
                    </li>
                    <li class="red"><label>{{mediaReport.picture_count}}</label>
                        <span><?php echo lang('Media_Pictures'); ?> ({{mediaReport.picture_size}})</span>
                    </li>                
                </ul>
            </div>
            <div class="float-right">
                <label class="label text-right media-label">Abused Media</label>
                <ul class="payment-total-list">
                    <li class="green"><label>{{mediaReport.abuse_count}}</label>
                        <span><?php echo lang('TotalAbused'); ?> ({{mediaReport.abuse_size}})</span>
                    </li>
                    <li class="yellow"><label>{{mediaReport.abuse_video_count}}</label>
                        <span><?php echo lang('Media_Video'); ?> ({{mediaReport.abuse_video_size}})</span>
                    </li>
                    <li class="red"><label>{{mediaReport.abuse_picture_count}}</label>
                        <span><?php echo lang('Media_Pictures'); ?> ({{mediaReport.abuse_picture_size}})</span>
                    </li>
                </ul>
            </div>
        </section>
    </div>

    <div class="row-flued">
        <div class="panel panel-secondary">
                <div class="panel-body">
                <!-- Pagination -->
                    <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                    <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                <!-- Pagination -->
                        <table class="table table-hover media_analytic_table">
                            <tbody>
                            <tr>
                                <th id="username" class="ui-sort selected" ng-click="orderByField = 'username'; reverseSort = !reverseSort; sortBY('username')">                           
                                    <div class="shortdiv sortedDown">Name<span class="icon-arrowshort">&nbsp;</span></div>
                                </th>
                                <th id="location" class="ui-sort" ng-click="orderByField = 'location'; reverseSort = !reverseSort; sortBY('location')">
                                    <div class="shortdiv">Location<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                                </th>
                                <th id="size" class="ui-sort" ng-click="orderByField = 'size'; reverseSort = !reverseSort; sortBY('size')">
                                    <div class="shortdiv">Size<span class="icon-arrowshort hide">&nbsp;</span></div>
                                </th>
                                <th id="uploaded" class="ui-sort" ng-click="orderByField = 'uploaded'; reverseSort = !reverseSort; sortBY('uploaded')">
                                    <div class="shortdiv">Uploaded<span class="icon-arrowshort hide">&nbsp;</span></div>
                                </th>
                                <th id="flagged" class="ui-sort" ng-click="orderByField = 'flagged'; reverseSort = !reverseSort; sortBY('flagged')">
                                    <div class="shortdiv">Flagged<span class="icon-arrowshort hide">&nbsp;</span></div>
                                </th>
                                <th id="deleted" class="ui-sort" ng-click="orderByField = 'deleted'; reverseSort = !reverseSort; sortBY('deleted')">
                                    <div class="shortdiv">Deleted<span class="icon-arrowshort hide">&nbsp;</span></div>
                                </th>
                                <th>Actions</th>
                            </tr>

                            <tr class="rowtr" ng-repeat="medialist in listData[0].ObjUsers" ng-class="{selected : isSelected(medialist),notselected:isNotSelected(medialist)}" ng-init="medialist.indexArr=$index" ng-click="selectCategory(medialist);">
                                <td>
                                    <a href="#" ng-click="viewUserProfile(medialist.userguid)" class="thumbnail40" title="Click to view profile" rel="tipsynw">                                        
                                        <img ng-src="{{medialist.profilepicture}}" >
                                    </a>
                                    <a href="#" class="name" ng-click="viewUserProfile(medialist.userguid)">{{medialist.username}}</a>
                                </td>
                                <td>{{medialist.location}}</td>
                                <td class="media_active">{{medialist.size}}</td>
                                <td class="media_active">{{medialist.uploaded}}</td>
                                <td class="media_active">{{medialist.flagged}}</td>
                                <td class="media_active">{{medialist.deleted}}</td>
                                <td>
                                    <a href="#"  ng-click="SetUser(medialist);" class="user-action" onClick="userActiondropdown()">
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
                <?php if(in_array(getRightsId('user_profile'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionViewProfile"><a href="javascript:void(0);" ng-click="viewUserProfile(userlist.userguid)"><?php echo lang("User_Index_ViewProfile"); ?></a></li> 
                <?php } ?>
                <?php if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionBlock" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1 || currentUserStatusId == 3 || currentUserStatusId == 4"><a ng-click="SetSingleUserStatus('block');" href="javascript:void(0);"><?php echo lang('MediaAnalytics_BlockUser'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionDelete" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>')>-1 || currentUserStatusId == 3"><a ng-click="SetSingleUserStatus('delete');" href="javascript:void(0);"><?php echo lang("User_Index_Delete"); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->
        

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
    
    <input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
    <input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
    <input type="hidden" name="hdnSelectallPermission" id="hdnSelectallPermission" value="<?php echo $selectall_permission; ?>"/>
        
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
</div>
</section>
