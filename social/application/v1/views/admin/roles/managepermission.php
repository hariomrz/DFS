<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><?php echo lang('AnalyticsTools_Tools'); ?></li>
                    <li>/</li>
                    <li><span><?php echo lang('ManagePermission_ManagePermission'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->

<section class="main-container">
    <div class="container" ng-controller="RolePermissionCtrl" id="RolePermissionCtrl">
        <!--Info row-->
        <div class="info-row row-flued">
            <h2><span id="spnh2"><?php echo lang('ManagePermissions'); ?></span> ({{totalRolePermission}})</h2>
            <div class="info-row-right rightdivbox">
                <div class="pull-right" style="width: 120px;">
                    <select chosen data-disable-search="true" data-ng-options="item.ApplicationName for item in appListData track by item.ApplicationID" name="RoleApplication[]" id="RoleApplication" ng-model="selectedApplication" ng-change="selectChangeEvent();">
                    </select>
                </div>
                <!--<div class="text-field select w160">
                    <select id="RoleApplication" name="RoleApplication[]" ng-model="selectedApplication" style="display: none;" ng-change="selectChangeEvent();">
                        <option ng-repeat="application in appListData" value="{{application.ApplicationID}}" repeat-done="layoutDone();">{{application.ApplicationName}}</option>
                    </select>
                </div>-->
            </div>
        </div>
        <!--/Info row-->
        <div class="showing_region">
           <div class="showing-result"><?php echo lang('ManagePermission_statement'); ?></div>
        </div>
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                    <table class="table table-hover  permission_table">
                        <tbody>
                            <tr>
                                <!--<th><?php echo lang('Application'); ?></th>-->
                                <th><?php echo lang('ActionGroup'); ?></th>
                                <th><?php echo lang('Action'); ?></th>
                                <th><?php echo lang('PermissionToRoles'); ?></th>
                                <th><?php echo lang('Change'); ?></th>
                            </tr>

                            <tr class="rowtr" ng-repeat="permissionlist in listData[0].ObjRoles">
                                <!--<td>{{permissionlist.application}}</td>-->
                                <td>{{permissionlist.action_group}}</td>
                                <td>{{permissionlist.action}}</td>
                                <td>{{permissionlist.permission_roles}}</td>
                                <td>
                                    <?php if(in_array(getRightsId('changepermsissions'), getUserRightsData($this->DeviceType))){ ?>
                                        <a ng-hide="permissionlist.right_id==''" class="add-remove add-btn marr10" href="javascript:void(0);" ng-click="changeRolePermissionForm(permissionlist);">
                                            <i rel="tipsy" original-title="Add" class="icon-add"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <span id="result_message" class="result_message">There is no record to show.</span>
        </div>
    
        <!--Change Permissions Popup-->
        <div class="popup modal-dialog confirme-popup animated" id="changePermissions">
             
            <div class="modal-header">
                <button type="button" class="close" onClick="closePopDiv('changePermissions', 'bounceOutUp');">
                    <span aria-hidden="true"><i class="ficon-cross"></i></span>
                </button>                 
                <h4 class="modal-title"><?php echo lang('ChangePermissions'); ?></h4>
            </div>
            <div class="modal-body">
                     <h4>
                        <?php echo lang('ManagePermission_ChangePermission'); ?>
                            <br>
                            <label id="lblAppName">{{application_name}}</label>
                            &gt;
                            <label id="lblActionName">{{app_action}}</label>
                      </h4>

                    <label class="label"><?php echo lang('ManagePermission_AddRemoveRoles'); ?></label>
                    <div class="role-listing" id="RoleList">                    
                        <div ng-repeat="rolerights in permissionRoleData" class="roles-select selectbox" ng-class="{'focus': rolerights.RoleRightID}">
                            <span ng-class="{'icon-checked': rolerights.RoleRightID}">
                                <input ng-checked="rolerights.RoleRightID" type="checkbox" id="C{{rolerights.RoleID}}" class="globalCheckbox" onclick="childchecks(this);" value="{{rolerights.RoleID}}">
                            </span>
                            <label for="C{{rolerights.RoleID}}">{{rolerights.Name}}</label>
                        </div>                              
                        <div class="clearfix">&nbsp;</div>
                    </div> 
                    <div class="error-holder">{{errorRoleMessage}}</div>
            </div>
            <div class="modal-footer">
                <div class="pull-right">
                    <button value="<?php echo lang('Cancel'); ?>" class="btn btn-default" onClick="closePopDiv('changePermissions', 'bounceOutUp');"><?php echo lang('Cancel'); ?></button>
                    <button ng-click="SaveRolePermissions();" class="btn btn-primary" type="submit" id="btnRoleSubmit"><?php echo lang('Save_Lower'); ?></button>
                </div>
            </div> 
        </div>
        <!--Change Permissions Popup-->
</div>
</section>