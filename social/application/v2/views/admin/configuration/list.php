<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('GlobalConfigurationManagement'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="ConfigurationCtrl" id="ConfigurationCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang('GlobalConfigurationManagement'); ?></span> ({{totalConfiguration}})</h2>
        <div class="info-row-right"></div>
    </div>
    <!--/Info row-->

    <!--/Info row-->
    <div class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover config_table">
                <tr>
                    <th id="ConfigurationName" class="ui-sort selected" ng-click="orderByField = 'ConfigurationName'; reverseSort = !reverseSort; sortBY('ConfigurationName')">                           
                        <div class="shortdiv sortedDown">Configuration Name<span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th id="DataTypeName" class="ui-sort" ng-click="orderByField = 'DataTypeName'; reverseSort = !reverseSort; sortBY('DataTypeName')">
                        <div class="shortdiv">Data Type<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                    </th>
                    <th id="ConfigValue" class="ui-sort" ng-click="orderByField = 'ConfigValue'; reverseSort = !reverseSort; sortBY('ConfigValue')">
                        <div class="shortdiv">Current Value<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th>Actions</th>
                </tr>

                <tr class="rowtr" ng-repeat="configurationlist in listData[0].ObjConfig" ng-if="configurationlist.BUConfigID != 30 && configurationlist.BUConfigID != 31 ">
                    <td>{{configurationlist.ConfigurationName}}</td>                
                    <td>{{configurationlist.DataTypeName}}</td>
                    <td>{{configurationlist.currentValue}}</td>
                    <td>
                        <?php if(in_array(getRightsId('configuration_management_change_event'), getUserRightsData($this->DeviceType))){ ?>
                            <a href="javascript:void(0);" ng-click="changeConfigValue(configurationlist);"><?php echo lang('Change'); ?></a>
                        <?php } ?>
                    </td>
                </tr>                  
            </table>
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

        
        <span id="result_message" class="result_message">There is no record to show.</span>
    </div>
    
    <!--Popup for add/edit IP details -->
    <div class="popup communicate animated" id="updateConfigurationSettingPopup">
        <div class="popup-title"><?php echo lang('ChangeValue'); ?>  -  {{ConfigurationName}} <i class="icon-close" onClick="closePopDiv('updateConfigurationSettingPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content loader_parent_div">
            <i class="loader_ele btn_loader_overlay"></i>
            <div class="communicate-footer row-flued">
                <div class="from-subject">
                    <label for="CurrentConfigValue" class="label"><?php echo lang('CurrentValue'); ?></label>
                    <div class="text-field paddingright" ng-hide="showSelectbox">
                        <textarea class="config_textarea" name="CurrentConfigValue" id="CurrentConfigValue" rows="15" placeholder="Current Value">{{ConfigValue}}</textarea>                        
                    </div>
                    <div ng-show="showSelectbox" style="min-height: 120px;">
                        <select class="w160" chosen data-disable-search="true" data-ng-options="item for item in ConfigSelectOptions track by item" name="CurrentConfigValue" id="CurrentConfigValue" ng-model="selectedConfigValue">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="error-holder configerrormsg" ng-show="showConfigError" style="color: #CC3300;">{{errorConfigMessage}}</div>
               </div>                
                <div class="clearfix"></div>
                <div class="form-control padtb10" id="dvaddthisip">
                    <label class="label iplabel"><b>Help text:</b> {{Description}}</label>
                </div>
            </div>        
            <button ng-click="UpdateConfigurationDetails()" class="button float-right" type="submit"><?php echo lang('Save_Lower'); ?></button>
            <button class="button wht float-right" onclick="closePopDiv('updateConfigurationSettingPopup', 'bounceOutUp');"><?php echo lang('Cancel'); ?></button>
        </div>
    </div>
    <!--Popup end add/edit IP details -->
</div>
</section>