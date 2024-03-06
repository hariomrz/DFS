<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('User_UserProfile_Ips'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container"  ng-controller="IpsCtrl" id="IpsCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2">{{pageHeading}} </span> ({{totalIps}})</h2>
        <div class="info-row-right">
            <?php if(in_array(getRightsId('ips_add_event'), getUserRightsData($this->DeviceType))){ ?>
                <a href="javascript:void(0);" ng-click="AddIpDetailsPopUp()" class="btn-link">
                    <ins class="buttion-icon"><i class="icon-add"></i></ins>
                    <span><?php echo lang('Add'); ?></span>
                </a>
            <?php } ?>
            <ul class="sub-nav matop10 media_right_filter">
                <?php if(in_array(getRightsId('ips_admin'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a id="adminips" href="javascript:void(0);" ng-click="loadIPs(1);" class="selected"><?php echo lang('ADMIN'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('ips_user'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a id="userips" href="javascript:void(0);" ng-click="loadIPs(0);"><?php echo lang('USER'); ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <!--/Info row-->

    <div class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover ips_table">
                <tr>
                    <th id="ip" class="ui-sort selected" ng-click="orderByField = 'ip'; reverseSort = !reverseSort; sortBY('ip')">                           
                        <div class="shortdiv sortedDown"><?php echo lang('IPs_Address'); ?><span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th id="status" class="ui-sort" ng-click="orderByField = 'status'; reverseSort = !reverseSort; sortBY('status')">
                        <div class="shortdiv"><?php echo lang('Status'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th><?php echo lang('Actions'); ?></th>
                </tr>

                <tr ng-repeat="iplist in listData[0].ObjIP|orderBy:orderByField:reverseSort">
                    <td>
                        <p>{{iplist.ip}}</p>
                    </td>
                    <td>{{iplist.status}}</td>
                    <td>
                        <a href="javascript:void(0);" ng-click="SetIpDetail(iplist);" class="smtp_action" onClick="smtpActionDropdown()">
                            <i class="icon-setting">&nbsp;</i>
                        </a>
                    </td>
                </tr>
            </table>
            <div id="ipdenieddiv"></div>
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">  
                <?php if(in_array(getRightsId('ips_inactive_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionInactive" ng-hide="IpStatus==1;"><a ng-click="SetIpStatus('inactive');" href="javascript:void(0);"><?php echo lang('MakeInactive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('ips_active_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionActive" ng-hide="IpStatus==2;"><a ng-click="SetIpStatus('active');" href="javascript:void(0);"><?php echo lang('MakeActive'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('ips_edit_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionEdit"><a ng-click="EditIpDetailsPopUp()" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('ips_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionDelete" ng-hide="isDefaultIP==1"><a ng-click="SetIpStatus('delete')" href="javascript:void(0);"><?php echo lang('Delete'); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->

        </div>

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    
    <!--Popup for add/edit IP details -->
    <div class="popup communicate animated" id="addIpPopup">
        <div class="popup-title"><?php echo lang('AddIPAddress'); ?> <i class="icon-close" onClick="closePopDiv('addIpPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content loader_parent_div">
            <i class="loader_ip btn_loader_overlay"></i>
            <div class="communicate-footer row-flued">
                <input type="hidden" name="allowedipid" id="allowedipid" value="{{allowedipid}}"/>
                <div class="from-subject">
                    <label for="ip_address" class="label"><?php echo lang('IPAddress'); ?> </label>
                    <div class="text-field">
                        <input type="text" name="ip_address" id="ip_address" placeholder="<?php echo lang('EnterIPAddress'); ?>" value="{{ip_address}}" maxlength="45">
                    </div>
                    <div class="error-holder" ng-show="showIpError" style="color: #CC3300;">{{errorIpMessage}}</div>
                </div>
                <div class="from-subject">
                    <label for="description" class="label"><?php echo lang('Description'); ?></label>
                    <div class="text-field">
                        <textarea maxlength="1000" class="input_textarea" name="description" id="description" rows="15" placeholder="<?php echo lang('Description'); ?>">{{description}}</textarea>
                    </div>
               </div>
                <!--<div class="text-field selectbox focus">
                    <span class="icon-checked">
                        <input type="checkbox" id="chkActive" class="globalCheckbox" checked="checked">
                    </span>
                    <label for="chkActive"><?php echo lang('Active'); ?></label>
                </div>-->
                <div class="clearfix"></div>
                <div class="form-control padtb10" id="dvaddthisip">
                    <label class="label iplabel">Users current IP</label>
                    <label id="lblHostAddress" class="label iplabel"><?php echo $_SERVER['REMOTE_ADDR']; ?></label>
                    <a ng-click="SetIp();" id="lnkAddIp" href="javascript:void(0);"><?php echo lang('Add'); ?></a>
                </div>
            </div>        
            <button ng-click="CreateIpDetails()" class="button float-right" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
            <button class="button wht float-right" ng-click="ClearIpData();" onclick="closePopDiv('addIpPopup', 'bounceOutUp');"><?php echo lang('Cancel'); ?></button>
        </div>
    </div>
    <!--Popup end add/edit IP details -->
    
    <!--Popup for change ip status -->
    <div class="popup confirme-popup animated" id="confirmeIpSettingPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeIpSettingPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeIpSettingPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateIPStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>      
    <!--Popup for change ip status -->
</div>
</section>