<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><?php echo lang('AnalyticsTools_Tools'); ?></li>
                    <li>/</li>
                    <li><span><?php echo lang('Role_ManageRole'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--/Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="ManageRolesCtrl" id="ManageRolesCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('Role_ManageRoles'); ?></span> ({{totalRoles}})</h2>
        <div class="info-row-right rightdivbox">
            <?php if(in_array(getRightsId('addroles'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" class="button float-right marl10" id="roleClick" onClick="$('#AddRole').slideDown();" ng-click="AddNewRole();"><?php echo lang('AddNewRole'); ?></a>        
            <?php } ?>
        </div>
    </div>
    <!--/Info row-->
    
    <!--Add New Role-->
    <div class="panel m-t"  id="AddRole" style="display:none;">
      <div class="panel-body">
      <div class="row-flued">
        <div class="add-category">
          <div class="category-left">
            <table class="addcategory-table rolestable">
              <tr>
                  <td class="valign"><label class="label"><?php echo lang('RoleName'); ?> <span class="required">*</span></label></td>
                  <td>
                      <div class="form-group clearfix">
                          <div class="large" data-type="focus">
                              <input type="text" class="form-control" name="RoleName" id="RoleName" value="">
                          </div>
                          <div class="error-holder roleserror" ng-if="errorMessage">{{errorMessage}}</div>
                      </div>
                  </td>
              </tr>
              <tr>
                  <td><label class="label"><?php echo lang('Status'); ?><span class="required">*</span></label></td>
                  <td>
                      <div class="form-group clearfix">
                          <div class="radio_parent_div /*w160*/">
                              <div class="radiodiv">
                                  <input type="radio" name="RoleStatus" id="ActiveRadio" class="role_radio css-radiobox" value="2" checked="checked"/>
                                  <label for="ActiveRadio" class="css-label RadioLabel"><?php echo lang('Active'); ?></label>
                              </div>
                              <div class="radiodiv">
                                  <input type="radio" name="RoleStatus" id="InactiveRadio" class="role_radio css-radiobox" value="1"/>
                                  <label for="InactiveRadio" class="css-label RadioLabel"><?php echo lang('Inactive'); ?></label>
                              </div>  
                          </div>
                      </div>
                  </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>
                  <div class="button-group">
                    <button ng-click="createRole();" class="btn btn-primary pull-left" type="submit" id="btnRoleSubmit"><?php echo lang('Submit'); ?></button>
                    <a class="btn btn-default btn-link pull-left" onClick="$('#AddRole').slideUp();"><?php echo lang('Cancel'); ?></a> 
                  </div>
                </td>
              </tr>            
            </table>
              <input type="hidden"  name="hdnRoleID" id="hdnRoleID" value=""/>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
    </div>

    <!--Add New Role--> 
    
    <!--Manage Permissions under Action click-->
    <div class="row-flued" id="ManagePermissions" style="display:none;">
      <div class="titlebox">
          <h3 class="title"><?php echo lang('ManageRolePermissions'); ?></h3>
      </div>
      <div class="add-category">
        <div class="category-left">
          <table class="addcategory-table rolestable">
            <tr>
              <td class="valign"><label class="label"><?php echo lang('RoleName'); ?></label></td>
              <td><label class="label text-bold role_namep">{{rolename}}</label></td>
            </tr>
            <tr>
              <td class="valign"><label class="label"><?php echo lang('Access'); ?></label></td>
              <td>
                  <div class="border-box scrollvert150">
                    <div class="form-control" id="permissionList">                    
                      <ul class="list-unstyled">
                          <li ng-repeat="rolerights in permissionsData">
                              <div class="roles-select selectbox"> 
                                  <span>
                                      <input onclick="checkPermission(this);childchecks(this);" type="checkbox" class="globalCheckbox" ischild="0" isparent="1" level="{{rolerights.ApplicationID}}" id="parentId{{rolerights.ApplicationID}}" value="{{rolerights.ApplicationID}}">
                                  </span>
                                  <label for="parentId{{rolerights.ApplicationID}}">{{rolerights.ApplicationName}}</label>
                              </div>
                              <ul class="list-unstyled rolecheck level{{rolerights.ApplicationID}}">
                                  <li ng-repeat="rolerights_level1 in rolerights.RoleRights" repeat-done="selectRolePermissions();">
                                      <div class="roles-select selectbox" ng-class="{'focus': rolerights_level1.RoleRightID}"> 
                                          <span class="checkspan " ng-class="{'icon-checked': rolerights_level1.RoleRightID}">
                                              <input ng-checked="rolerights_level1.RoleRightID" onclick="checkPermission(this);childchecks(this);" type="checkbox" class="globalCheckbox" ischild="1" isparent="1" level="{{rolerights_level1.RightID}}" id="parentId{{rolerights_level1.RightID}}" rolerightid="{{rolerights_level1.RoleRightID}}" applicationid="{{rolerights.ApplicationID}}" value="{{rolerights_level1.RightID}}">
                                          </span>
                                          <label for="parentId{{rolerights_level1.RightID}}">{{rolerights_level1.Name}}</label>
                                      </div>
                                      <ul class="list-unstyled level{{rolerights_level1.RightID}}">
                                          <li ng-repeat="rolerights_level2 in rolerights_level1.RoleRights" repeat-done="selectRolePermissions();">
                                              <div class="roles-select selectbox" ng-class="{'focus': rolerights_level2.RoleRightID}"> 
                                                  <span class="checkspan " ng-class="{'icon-checked': rolerights_level2.RoleRightID}">
                                                      <input ng-checked="rolerights_level2.RoleRightID" onclick="checkPermission(this);childchecks(this);" type="checkbox" class="globalCheckbox" id="childId{{rolerights_level2.RightID}}" isparent="0" level="{{rolerights_level1.RightID}}" rolerightid="{{rolerights_level2.RoleRightID}}" applicationid="{{rolerights.ApplicationID}}" value="{{rolerights_level2.RightID}}">
                                                  </span>
                                                  <label for="childId{{rolerights_level2.RightID}}">{{rolerights_level2.Name}}</label>
                                              </div>
                                          </li>
                                      </ul>
                                  </li>
                              </ul>
                          </li>
                      </ul>                    
                      <div class="clearfix">&nbsp;</div>
                    </div>
                </div>                  
                <div class="clearfix">&nbsp;</div>
                <div class="error-holder roleserror">{{errorPermissionMessage}}</div>
                <div class="clearfix">&nbsp;</div>
              </td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><div class="button-block">
                      <input type="submit" class="button float-left" value="<?php echo lang('Submit'); ?>" ng-click="SaveManagedRolePermissions();"/>
                  <a href="javascript:void(0);" class="cancel-link float-left" onClick="$('#ManagePermissions').slideUp();$('#roleClick').slideDown();"><?php echo lang('Cancel'); ?></a> </div></td>
            </tr>
          </table>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!--Manage Permissions under Action click-->
    
    <div class="showing_region">
       <div class="showing-result"><?php echo lang('ManageRole_Statement'); ?></div>
    </div>
    <div class="row-flued">
        <div class="panel panel-secondary">
          <div class="panel-body">
            <!-- Pagination -->
            <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
            <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <th><?php echo lang('RoleName'); ?></th>
                        <th><?php echo lang('Status'); ?></th>
                        <th><?php echo lang('Actions'); ?></th>
                    </tr>

                    <tr class="rowtr" ng-repeat="rolelist in listData[0].ObjRoles|orderBy:orderByField:reverseSort">
                        <td>{{rolelist.rolename}}</td>
                        <td>{{rolelist.status}}</td>
                        <td>
                            <a href="javascript:;"  ng-click="SetRoleDetail(rolelist);" class="user-action" onClick="userActiondropdown()">
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
            <?php if(in_array(getRightsId('viewroles'), getUserRightsData($this->DeviceType))){ ?>
                <li><a ng-click="ViewEditRole(roleDetail,'view');" href="javascript:void(0)" title="View" class="tooltips"><?php echo lang("View"); ?></a></li>
            <?php } ?>
            <?php if(in_array(getRightsId('managerolepermissions'), getUserRightsData($this->DeviceType))){ ?>
                <li><a ng-click="ManageRolePermissions(roleDetail);" href="javascript:void(0)" title="Manage Permissions" class="tooltips"><?php echo lang("ManagePermissionsAction"); ?></a></li>
            <?php } ?>
            <?php if(in_array(getRightsId('editroles'), getUserRightsData($this->DeviceType))){ ?>
                <li><a ng-click="ViewEditRole(roleDetail,'edit');" href="javascript:void(0)" title="Edit" class="tooltips"><?php echo lang("Edit"); ?></a></li>
            <?php } ?>
            <?php if(in_array(getRightsId('deleteroles'), getUserRightsData($this->DeviceType))){ ?>
                <li><a ng-hide="roleDetail.roleid == 1 || roleDetail.roleid == 2" ng-click="deleteRole(roleDetail);" href="javascript:void(0)" title="Delete" class="tooltips"><?php echo lang("Delete"); ?></a></li>
            <?php } ?>
        </ul>
        
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
    
    <div class="popup confirme-popup animated" id="confirmeRolePopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeRolePopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center"><?php echo lang('Sure_Delete'); ?> ?</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeRolePopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateRoleStatus('delete')"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>
    
    <div class="popup confirme-popup animated" id="confirmeDeleteRolePopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeDeleteRolePopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="alert alert-warning">
            There are already {{userCount}} users having this role. Please select a role to assign to these users
        </div>
        <div class="popup-content rolepopupdiv">
            <div class="from-subject">
                <label for="newrole" class="label"><?php echo lang('ManageRole_SelectRole'); ?></label>
                <select class="width100" chosen data-disable-search="true" data-ng-options="item.Name for item in RoleList track by item.RoleID" name="newRole" id="newRole" ng-model="newRole">
                    <option value=""></option>
                </select>
                <!--<div class="text-field">                    
                    <select name="newRole" id="newRole">
                        <option value="">Select Role</option>
                        <option ng-repeat="role in RoleList" value="{{role.RoleID}}" repeat-done="layoutDone();">{{role.Name}}</option>
                    </select>
                </div>-->
                <div class="clear"></div>
                <div class="error-holder" ng-show="showRoleError" style="color: #CC3300;">{{errorRoleMessage}}</div>
            </div>                        
        </div>
        <div class="form-footer">
            <button ng-click="updateRoleStatus('assign')" class="button float-right" type="submit" id="btnSaveRole"><?php echo lang('ManageRole_AssignRole'); ?></button>
            <button ng-click="updateRoleStatus('notassign')" class="button float-right" type="submit" id="btnSaveRole"><?php echo lang('ManageRole_ContinueAnyway'); ?></button>
            <button class="button wht float-right" onclick="closePopDiv('confirmeDeleteRolePopup', 'bounceOutUp');"><?php echo lang('Cancel'); ?></button>
        </div>
    </div>
</div>
</section>