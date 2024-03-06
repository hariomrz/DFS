<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li>
                        <?php echo lang('AnalyticsTools_Tools'); ?>
                    </li>
                    <li>/</li>
                    <li><span><?php echo lang('ManageUsers'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">
    <div class="container" ng-controller="RoleUsersCtrl" id="RoleUsersCtrl">
        <!--Info row-->
        <div class="info-row row-flued">
            <h2><span id="spnh2"><?php echo lang('ManageUsers'); ?></span> ({{totalRoleUsers}})</h2>
            <div class="info-row-right rightdivbox">
                <?php if(in_array(getRightsId('addusers'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="button float-right marl10" id="roleClick" onClick="$('#AddRoleUser').slideDown();" ng-click="AddNewUser();">
                    <?php echo lang('AddNewUser'); ?>
                </a>
                <?php } ?>
                <div class="select w160 marl10">
                    <select class="w160" chosen data-disable-search="true" data-ng-options="item.Name for item in roleOptData track by item.RoleID" name="PermissionRole" id="PermissionRole" ng-model="selectedRole" ng-change="selectChangeEvent();">
                        <option value=""></option>
                    </select>
                    <!--<select id="PermissionRole" name="PermissionRole" ng-model="selectedRole" ng-change="selectChangeEvent();">
                    <option ng-repeat="role in roleOptData" value="{{role.RoleID}}" repeat-done="layoutDone();">{{role.Name}}</option>
                </select>-->
                </div>
                <div id="selectallbox" class="text-field selectbox">
                    <span>
                    <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                </span>
                    <label for="selectAll">
                        <?php echo lang("Select_All"); ?>
                    </label>
                </div>
                <div id="ItemCounter" class="items-counter">
                    <ul class="button-list">
                        <?php if(in_array(getRightsId('deleteusers'), getUserRightsData($this->DeviceType))){ ?>
                        <li>
                            <a href="javascript:void(0);" ng-click="SetMultipleUserStatus('delete');">
                                <?php echo lang("Delete"); ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                    <div class="total-count-view"><span class="counter">0</span> </div>
                </div>
            </div>
        </div>
        <!--/Info row-->
        <!-- Add user under Action click-->
        <div class="panel m-t" id="AddRoleUser" style="display:none;">
            <div class="panel-body">
                <div class="category-left">
                    <table class="addcategory-table rolestable">
                        <tr>
                            <td class="valign">
                                <label class="label">
                                    <?php echo lang('first_name'); ?> <span class="required">*</span></label>
                            </td>
                            <td>
                                <div>
                                    <div class="form-group clearfix" data-type="focus">
                                        <input type="text" class="form-control" name="FirstName" id="FirstName" value="">
                                    </div>
                                    <div class="error-holder usrerror" ng-if="errorFName">{{errorFName}}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="valign">
                                <label class="label">
                                    <?php echo lang('last_name'); ?> <span class="required">*</span></label>
                            </td>
                            <td>
                                <div>
                                    <div class="form-group clearfix" data-type="focus">
                                        <input type="text" class="form-control" name="LastName" id="LastName" value="">
                                    </div> 
                                    <div class="error-holder usrerror" ng-if="errorLName">{{errorLName}}</div> 
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="valign">
                                <label class="label">
                                    <?php echo lang('Email'); ?> <span class="required">*</span></label>
                            </td>
                            <td>
                                <div>
                                    <div class="form-group clearfix" data-type="focus">
                                        <input type="text" class="form-control" name="Email" id="Email" value="">
                                    </div> 
                                    <div class="error-holder usrerror" ng-if="errorEmail">{{errorEmail}}</div> 
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="valign">
                                <label class="label">
                                    <?php echo lang('UserName'); ?> <span class="required">*</span></label>
                            </td>
                            <td>
                                <div>
                                    <div class="form-group clearfix" data-type="focus">
                                        <input type="text" class="form-control" name="Username" id="Username" value="">
                                    </div> 
                                    <div class="error-holder usrerror" ng-if="errorUsername">{{errorUsername}}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="valign">
                                <label class="label">
                                    <?php echo lang('Role'); ?> <span class="required">*</span></label>
                            </td>
                            <td>
                                <div class="form-group">
                                    <div class="border-box clearfix scrollvert150">
                                        <div id="RoleListOpt">
                                            <ul class="list-unstyled">
                                                <li ng-repeat="role in roleListData">
                                                    <div class="roles-select selectbox" ng-class="{'focus': userRoles.indexOf(role.RoleID) != -1}">
                                                        <span ng-class="{'icon-checked': userRoles.indexOf(role.RoleID) != -1}">
                                                              <input ng-checked="userRoles.indexOf(role.RoleID) != -1" checklist-model="userRoles" onclick="childchecks(this);" type="checkbox" class="globalCheckbox" value="{{role.RoleID}}">
                                                          </span>
                                                        <label for="C{{role.RoleID}}">{{role.Name}}</label>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="clearfix">&nbsp;</div>
                                        </div>
                                    </div>
                                    <div class="error-holder usrerror" ng-if="errorRole">{{errorRole}}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <div class="button-group">
                                    <button ng-click="SaveRoleUser();" class="btn btn-primary pull-left" type="submit" id="btnUserSubmit">
                                        <?php echo lang('Submit'); ?>
                                    </button>
                                    <a class="btn btn-default btn-link pull-left" onClick="$('#AddRoleUser').slideUp();">
                                        <?php echo lang('Cancel'); ?>
                                    </a>
                                    <input type="hidden" name="hdnUserID" id="hdnUserID" value="" />
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- Add user under Action click-->
        <div class="showing_region">
            <div class="showing-result">
                <?php echo lang('ManageUser_statement'); ?>
            </div>
        </div>
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                    <!-- Pagination -->
                    <div class="showingdiv">
                        <label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label>
                    </div>
                    <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false"></ul>
                    <!-- Pagination -->
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>
                                    <?php echo lang('first_name'); ?>
                                </th>
                                <th>
                                    <?php echo lang('last_name'); ?>
                                </th>
                                <th>
                                    <?php echo lang('Email'); ?>
                                </th>
                                <th>
                                    <?php echo lang('Role'); ?>
                                </th>
                                <th>
                                    <?php echo lang('Action'); ?>
                                </th>
                            </tr>
                            <tr class="rowtr" ng-repeat="userdata in listData[0].ObjUsers" ng-class="{selected : isSelected(userdata)}" ng-init="userdata.indexArr=$index" ng-click="selectCategory(userdata);">
                                <td>{{userdata.FirstName}}</td>
                                <td>{{userdata.LastName}}</td>
                                <td>{{userdata.Email}}</td>
                                <td>{{userdata.UserRoles}}</td>
                                <td>
                                    <a href="javascript:;" ng-click="SetUserDetail(userdata);" class="user-action" onClick="userActiondropdown()">
                                    <i class="icon-setting">&nbsp;</i>
                                </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination -->
                    <div class="showingdiv">
                        <label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label>
                    </div>
                    <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false"></ul>
                    <!-- Pagination -->
                </div>
            </div>
            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu  userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if(in_array(getRightsId('viewusers'), getUserRightsData($this->DeviceType))){ ?>
                <li>
                    <a ng-click="ViewEditUser(userDetail,'view');" href="javascript:void(0)" title="View" class="btn btn-md">
                        <?php echo lang("View"); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if(in_array(getRightsId('editusers'), getUserRightsData($this->DeviceType))){ ?>
                <li>
                    <a ng-click="ViewEditUser(userDetail,'edit');" href="javascript:void(0)" title="Edit" class="btn btn-md">
                        <?php echo lang("Edit"); ?>
                    </a>
                </li>
                <?php } ?>
                <?php if(in_array(getRightsId('deleteusers'), getUserRightsData($this->DeviceType))){ ?>
                <li>
                    <a ng-hide="currentUserRoleId ==<?php echo ADMIN_ROLE_ID; ?>" ng-click="deleteUser(userDetail);" href="javascript:void(0)" title="Delete" class="btn btn-md">
                        <?php echo lang("Delete"); ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
        <div class="popup confirme-popup animated" id="confirmeUserPopup">
            <div class="popup-title">
                <?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeUserPopup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p class="text-center">
                    <?php echo lang('Sure_Delete'); ?> ?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onclick="closePopDiv('confirmeUserPopup', 'bounceOutUp');">
                        <?php echo lang('Confirmation_popup_No'); ?>
                    </button>
                    <button class="button" ng-click="updateUserStatus()">
                        <?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="popup confirme-popup animated" id="confirmeMultipleUserPopup">
            <div class="popup-title">
                <?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p class="text-center">
                    <?php echo lang('Sure_Delete'); ?> ?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');">
                        <?php echo lang('Confirmation_popup_No'); ?>
                    </button>
                    <button class="button" ng-click="updateMultipleUsersStatus()">
                        <?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>