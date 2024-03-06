
<div  ng-controller="CrmUserListCtrl" id="CrmUserListCtrl">




    <?php $this->load->view('admin/crm/users_filter'); ?>


    <div class="container">
        <div class="main-container"> 

            <div class="sm-info" ng-if="isFilterReady() && filterApplied" >
                You are viewing all the

                <span ng-if="showingFilterData.StatusID != 0">
                    <b ng-if="showingFilterData.StatusID == 2"><?php echo lang("User_Index_RegisteredUsers"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 3"><?php echo lang("User_Index_DeletedUsers"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 4"><?php echo lang("User_Index_BlockedUsers"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 1"><?php echo lang("User_Index_WaitingForApproval"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 23">Suspended Users</b>

                    <span ng-if="showingFilterData.Gender != 0">,</span>

                </span>
                <span ng-if="showingFilterData.WN != 'All'">from <b> 
                       
                            {{showingFilterData.WN}}
                            
                    </b> 
                </span>

                <span ng-if="showingFilterData.LastLogin != 0">who were not logged in on APP in last <b> 
                       
                            {{showingFilterData.LastLogin}}
                            
                    </b> Days
                </span>

                <span ng-if="showingFilterData.Gender != 0">
                    <b ng-if="showingFilterData.Gender == 1">Male</b>
                    <b ng-if="showingFilterData.Gender == 2">Female</b>
                    <b ng-if="showingFilterData.Gender == 3">Other</b>
                    members 
                </span>

                <span ng-if="showingFilterData.Locations.length != 0">from <b> 
                        <span ng-repeat="Location in showingFilterData.Locations">
                            {{Location.City}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span>
                    </b> 
                </span>
                
                <span ng-if="showingFilterData.StartDate && showingFilterData.EndDate && showingFilterData.dateRangeFilterOption">
                    , registered  <b ng-bind="showingFilterData.dateRangeFilterOption.label"></b> 
                </span>
                
                <span ng-if="showingFilterData.StartDate && showingFilterData.EndDate && !showingFilterData.dateRangeFilterOption">
                    , registered on <b>{{showingFilterData.StartDate}} - {{showingFilterData.EndDate}}</b> 
                </span>

                <span ng-if="showingFilterData.StartDate && !showingFilterData.EndDate && !showingFilterData.dateRangeFilterOption">
                    , registered after <b>{{showingFilterData.StartDate}}</b> 
                </span>

                <span ng-if="!showingFilterData.StartDate && showingFilterData.EndDate && !showingFilterData.dateRangeFilterOption">
                    , registered before <b>{{showingFilterData.EndDate}}</b> 
                </span>
                
                <span ng-if="showingFilterData.AgeGroupID != 0">
                    , aged between <b>{{ageGroupList[showingFilterData.AgeGroupID - 1].Name}}</b> years
                </span>
                
                <span ng-if="showingFilterData.AgeStart != 0 && showingFilterData.AgeEnd != 0">
                    , aged between <b>{{showingFilterData.AgeStart}} - {{showingFilterData.AgeEnd}}</b> years
                </span>
                
                <span ng-if="showingFilterData.AgeStart != 0 && (showingFilterData.AgeEnd == 0 || showingFilterData.AgeEnd == '')">
                    , aged from <b>{{showingFilterData.AgeStart}} </b> years
                </span>
                
                <span ng-if="showingFilterData.AgeEnd != 0 && (showingFilterData.AgeStart == 0 || showingFilterData.AgeStart == '')">
                    , aged upto <b>{{showingFilterData.AgeEnd}}</b> years
                </span>

                <span ng-if="showingFilterData.TagUserType.length != 0">
                    , of type <b><span ng-repeat="tag in showingFilterData.TagUserType" >
                            {{tag.Name}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span></b> 
                </span>

                <span ng-if="showingFilterData.TagTagType.length != 0">
                    with <b>
                        <span ng-repeat="tag in showingFilterData.TagTagType" >
                            {{tag.Name}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span>
                    </b> tags
                </span>

                <span ng-if="0">
                    , of interests <b>Mountaineering.</b> 
                </span>

                <span ng-if="showingFilterData.AndroidAppVersion != 0">
                    , of Android APP <b>{{showingFilterData.AndroidAppVersion}}</b> 
                </span>

                <span ng-if="showingFilterData.IOSAppVersion != 0">
                    , of iOS APP <b>{{showingFilterData.IOSAppVersion}}</b> 
                </span>

                <a ng-click="applyFilter(1)">Reset</a> 
                | <a data-toggle="collapse"  data-target="#userFilters">Edit</a>
            </div>


            <div class="page-heading">
                <div class="row">
                    <div class="col-sm-4 " >

                    </div>
                    <div class="col-sm-8">
                        <div class="btn-toolbar btn-toolbar-right" >    
                        <button class="btn btn-default" ng-click="top_contributor_message()">Top Contributor Notification</button>                         
                            <button ng-show="userList.length != 0" class="btn btn-default" ng-click="downloadList()"><i class="ficon-download"></i> Download List</button> 
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-heading">
                <div class="row">
                    <div class="col-sm-4 " >
                        <small class="info-text-sm crm_on_check_div" ng-show="!allUserSelected" style="display:none;">
                            <span class="user_count_crm_msg"></span>
                            <span class="show_all_selection_message">
                                <a ng-click="selectUnselectAllUsers(1)">Select all {{totalRecord}} users</a> in lists.
                            </span>                            
                        </small>

                        <small class="info-text-sm crm_on_check_div" ng-if="allUserSelected">
                            All {{getSelectedUsersCount()}} users are selected. 
                            <a ng-click="selectUnselectAllUsers(0)">Unselect All</a>
                        </small>

                    </div>
                    <div class="col-sm-8">
                        <div class="btn-toolbar btn-toolbar-right" ng-show="userList.length != 0">
                            <div class="total-pages" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></div>
                            <nav class="page navigation">
                                <ul 
                                    uib-pagination total-items="totalRecord" items-per-page="numPerPage" 
                                    ng-model="currentPage" max-size="maxSize" 
                                    num-pages="numPages" class="pagination-sm" boundary-links="false" 
                                    ng-change="getThisPage()"
                                    >

                                </ul>
                            </nav>
                            
                        </div>
                    </div>
                </div>
            </div>


            <div class="panel panel-secondary">
                <div class="panel-body">



                    <div class="table-listing">
                        <table class="table table-hover crm-table"> 
                            <thead ng-show="totalRecord">
                                <tr>
                                    <th style="vertical-align: top;"> 
                                        <label class="checkbox checkbox-inline">
                                            <input type="checkbox" value="0" class="userCheckBox" id="headerCheckBoxCrm" >
                                            <span class="label"></span>
                                        </label>
                                    </th>  
                                    <th style="vertical-align: top;" ng-click="orderByField('FirstName')"  ng-class="getOrderByClass('FirstName')" >
                                        Name 
                                        <a class="sort" ng-if="getOrderByClass('FirstName')">
                                            <span class="icn">
                                                <i class="ficon-sort-arrow"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th style="vertical-align: top;">Phone Number</th>
<!--                                    <th>Type</th>
                                    <th>Tags</th>
                                    <th style="vertical-align: top;">Locations</th> 
                                    <th style="vertical-align: top;" ng-click="orderByField('AverageScore')" ng-class="getOrderByClass('AverageScore')">
                                        Activity Score
                                        <a class="sort" ng-if="getOrderByClass('AverageScore')">
                                            <span class="icn">
                                                <i class="ficon-sort-arrow"></i>
                                            </span>
                                        </a>
                                    </th>-->
                                    <th style="vertical-align: top;">App Version <br><?php echo "(A - ".ANDROID_VERSION.")";?><?php echo " (I - ".IOS_VERSION.")";?></th> 
                                    <th style="vertical-align: top;text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr ng-if="totalRecord == 0" >
                                    <td colspan="5" style="text-align: center;">
                                        No Result Found.
                                    </td>
                                </tr>
                                
                                <tr ng-repeat="(key, user) in userList" repeat-done="popOverInit();">
                                    <td>
                                        <label class="checkbox checkbox-inline checkbox-block">
                                            <input type="checkbox" value="{{user.UserID}}" class="userCheckBox">
                                            <span class="label"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="list-group list-group-thumb xs"> 
                                            <div class="list-group-item">
                                                <div class="list-group-body"> 
                                                    <figure class="list-figure" ng-click="getUserPersonaDetail(user.UserID, user.UserGUID, user.Name);">
                                                        <a><img ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/{{user.ProfilePicture}}" class="img-circle img-responsive" ></a>
                                                    </figure>
                                                    <div class="list-group-content">
                                                        <div class="list-group-item-heading ellipsis">                                               
                                                            <label class="ellipsis cursor-pointer" uib-tooltip="{{user.Name}}" ng-click="getUserPersonaDetail(user.UserID, user.UserGUID, user.Name);" ng-bind="user.Name"></label>
                                                            <a uib-tooltip="VIP User" tooltip-append-to-body="true" ng-if="( user.IsVIP == 1 )" class="icn circle-icn circle-primary">
                                                                <i class="ficon-check"></i>
                                                            </a>
                                                            <a uib-tooltip="Association User" tooltip-append-to-body="true" ng-if="( user.IsAssociation == 1 )" class="icn circle-icn circle-primary">
                                                                <i class="ficon-check"></i>
                                                            </a>
                                                            <!-- <span>{{user.AgeGenderTxt}}</span> -->
                                                        </div>
                                                    </div>   
                                                </div>                           
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{user.PhoneNumber}}</td>
                                  <!--  <td>
                                        <ul class="tags-list clearfix">
                                            <li ng-repeat="tagName in user.UserTypeTagsStr.tagStr track by $index" class="tag-primary">
                                                <span>{{tagName}}</span>
                                            </li>

                                            <li ng-if="user.UserTypeTagsStr.tagMoreStr.length > 0" class="tag-primary">
                                                <span 
                                                    data-container="body" 
                                                    data-toggle="popover" 
                                                    data-html="true"
                                                    data-content='{{user.UserTypeTagsStr.tagMoreStrTitle}}'>
                                                    +{{user.UserTypeTagsStr.tagMoreStr.length}}
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="tags-list clearfix">
                                            <li ng-repeat="tagName in user.TagsStr.tagStr track by $index">
                                                <span ng-bind="tagName"></span>
                                            </li>

                                            <li ng-if="user.TagsStr.tagMoreStr.length > 0">
                                                <span 
                                                    data-container="body" 
                                                    data-toggle="popover" 
                                                    data-html="true"
                                                    data-content="{{user.TagsStr.tagMoreStrTitle}}">
                                                    +{{user.TagsStr.tagMoreStr.length}}
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                 
                                    <td>{{user.LocationStr}}</td> 
                                    
                                    <td>{{user.AverageScore}}</td>  -->
                                    <td><span ng-if="user.AndroidAppVersion">A - </span>{{user.AndroidAppVersion}} <span ng-if="user.IOSAppVersion">&nbsp;I - </span>{{user.IOSAppVersion}}</td> 
                                    

                                    <td>
                                        <div class="action-auto-width-height">
                                            
                                            <div class="btn-toolbar btn-toolbar-right dropdown">
                                                <a class="btn btn-xs btn-default btn-icn user-action" 
                                                   data-toggle="dropdown" 
                                                   data-target=".userActiondropdown"
                                                   role="button" aria-expanded="false" 
                                                   ng-click="SetUser(user);" onClick="userActiondropdown()">
                                                    <span class="icn"><i class="ficon-dots"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div> 
                </div>
            </div>
        </div>
    </div>



    <!--Actions Dropdown menu-->
    <ul class="dropdown-menu  dropdown-menu-right userActiondropdown" style="display: none;">
        
        
        <?php if (in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))) { ?>
            <li id="ActionUnblock"  style="display:none;">
                <a onclick="SetStatusCrmModel(2);" href="javascript:void(0);">
                    <?php echo lang("User_Index_Unblock"); ?>
                </a>
            </li>
        <?php } ?>
        <?php if (in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))) { ?>
            <li id="ActionDelete" style="display:none;" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1">
                <a onclick="SetStatusCrmModel(3);" href="javascript:void(0);">
                    <?php echo lang("User_Index_Delete"); ?>
                </a>
            </li>
        <?php } ?>


        <?php if (in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))) { ?>
            <li id="ActionBlock" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1">
                <a onclick="SetStatusCrmModel(4);" href="javascript:void(0);">
                    <?php echo lang("User_Index_Block"); ?>
                </a>
            </li>
        <?php } ?>
     
        <?php if (in_array(getRightsId('change_password_event'), getUserRightsData($this->DeviceType))) { ?>
            <li id="ActionChangePwd" ng-hide="currentUserStatusId == 3">
                <a href="javascript:void(0);" onclick="SetStatusCrmModel(5);">
                    <?php echo lang("User_Index_ChangePassword"); ?>
                </a>
            </li>
        <?php } ?>

            <li id="ActionFeature" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1 && (currentUserStatusId == 3 || currentUserStatusId == 4)">
                <a ng-if="selectedUser.IsFeatured==0" href="javascript:void(0);" onclick="SetStatusCrmModel(7);">
                    Mark as Featured
                </a>
                <a ng-if="selectedUser.IsFeatured==1" href="javascript:void(0);" ng-click="remove_user_as_feature()">
                    Remove as Featured
                </a>
            </li>
            <li id="ActionFeature" ng-if="selectedUser.IsFeatured==1" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1 && (currentUserStatusId == 3 || currentUserStatusId == 4)">
                <a ng-if="selectedUser.IsPinned==0" href="javascript:void(0);" ng-click="set_pinned_feature_user(selectedUser.wf_uid);">
                    Mark as Pinned
                </a>
                <a ng-if="selectedUser.IsPinned==1" href="javascript:void(0);" ng-click="remove_pinned_feature_user(selectedUser.wf_uid)">
                    Remove as Pinned
                </a>
            </li>

            <li id="ActionVip" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1 && (currentUserStatusId == 3 || currentUserStatusId == 4)">
                <a ng-if="selectedUser.IsVIP==0" href="javascript:void(0);" onclick="SetStatusCrmModel(8);">
                    Mark as VIP
                </a>
                <a ng-if="selectedUser.IsVIP==1" href="javascript:void(0);" ng-click="remove_user_as_vip()">
                    Remove as VIP
                </a>
            </li>

            <li id="AssociationVip" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1 && (currentUserStatusId == 3 || currentUserStatusId == 4)">
                <a ng-if="selectedUser.IsAssociation==0" href="javascript:void(0);" onclick="SetStatusCrmModel(9);">
                    Mark as Association
                </a>
                <a ng-if="selectedUser.IsAssociation==1" href="javascript:void(0);" ng-click="remove_user_as_association()">
                    Remove as Association
                </a>
            </li>

            <li id="ActionCopyID" > 
                <a ng-click="copy_user_guid(selectedUser.UserGUID)" href="javascript:void(0);" >
                    Copy User ID
                </a>
            </li>
    </ul>
    <!--/Actions Dropdown menu-->







    <?php $this->load->view('admin/crm/users_options_models'); ?>

    <div ng-controller="UserListCtrl" id="UserListCtrl">
    <?php $this->load->view('admin/users/persona/user_persona'); ?>
    </div>


</div>
