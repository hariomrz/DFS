<?php 
if(isset($_GET['errorStatus']))
    $errorStatus = $_GET['errorStatus'];
else
    $errorStatus = '';

$log_type = 'pending';
if(in_array(getRightsId('support_request_listing_pending'), getUserRightsData($this->DeviceType))){ 
    $log_type = "pending";
}else if(in_array(getRightsId('support_request_listing_ignored'), getUserRightsData($this->DeviceType))){
    $log_type = "ignored";
}else if(in_array(getRightsId('support_request_listing_completed'), getUserRightsData($this->DeviceType))){
    $log_type = "completed";
}

$selectall_permission = 0;
?>
<div ng-controller="SupportCtrl" id="SupportCtrl" ng-init="loadErrorLogByType('<?php echo $log_type; ?>')">
    <!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
            <li><a target="_self" href="<?php echo base_url('admin/support'); ?>"><?php echo lang('SupportandErrorLog_SupportRequestListing'); ?></a></li>
            <li><i class="icon-rightarrow">&nbsp;</i></li>
            <li><a href="javascript:void(0);" class="selected">{{errorSectionText}}</a></li>
           <li class="sub-navigation dropdown">
                <button class="btn btn-default btn-sm" data-toggle="dropdown"><i class="icon-arrow"></i> </button>
                <ul class="dropdown-menu">
                    <?php if(in_array(getRightsId('support_request_listing_completed'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="licompleted" ng-click="setErrorLogStatus(2);" class=""><a href="javascript:void(0)"><?php echo lang('SupportandErrorLog_Completed'); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('support_request_listing_ignored'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="liignored" ng-click="setErrorLogStatus(4);" class=""><a href="javascript:void(0)"><?php echo lang('SupportandErrorLog_Ignored'); ?></a></li>
                    <?php } ?>
                    <?php if(in_array(getRightsId('support_request_listing_pending'), getUserRightsData($this->DeviceType))){ ?>
                        <li id="lipending" ng-click="setErrorLogStatus(1);" class="selected"><a href="javascript:void(0)"><?php echo lang('SupportandErrorLog_Pending'); ?></a></li>
                    <?php } ?>
                </ul>
            </li>
        </ul> 
</div>
        </div>
    </div>
</div>
    <!--/Bread crumb-->
    <section class="main-container">
    <div class="container">

<div class="info-row row-flued">
        <h2><span id="spnh2">{{errorSectionText}}</span> ({{totalLogs}})</h2>
        <div class="info-row-right rightdivbox">
            <ul class="sub-nav matop10 media_right_filter">
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(0);" class="selected"><?php echo lang('SupportandErrorLog_All'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(3);" class=""><?php echo lang('SupportandErrorLog_Feature'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(2);" class=""><?php echo lang('SupportandErrorLog_ReportedError'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(1);" class=""><?php echo lang('SupportandErrorLog_ServerError'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(4);" class=""><?php echo lang('SupportandErrorLog_Other'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="filterErrorLogs(5);" class=""><?php echo lang('SupportandErrorLog_Query'); ?></a></li>
            </ul>
            
            <div class="support_action_div">
                <?php if(in_array(getRightsId('support_request_listing_export_to_excel_event'), getUserRightsData($this->DeviceType))){ ?>
                    <a href="javascript:void(0);" class="btn-default btn btn-sm download_link m-r-sm" ng-click="downloadErrorLogs();">
                        <ins class="buttion-icon"><i class="icon-download">&nbsp;</i></ins>
                        <span><?php echo lang("User_Index_Download"); ?></span>
                    </a>
                <?php } ?>
                
                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" value="" id="searchField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="supportErrorSearch" ng-click="searchSupportError();" class="icon-search search-btn">
                </div>
                
                <?php if(in_array(getRightsId('support_request_listing_delete_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('support_request_listing_complete_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('support_request_listing_ignore_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('support_request_listing_unignore_event'), getUserRightsData($this->DeviceType))){ 
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
                        <?php if(in_array(getRightsId('support_request_listing_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li><a href="javascript:void(0);" ng-click="changeMultipleErrorLogStatus('delete');"><?php echo lang("Delete"); ?></a></li>
                        <?php } ?>
                        <?php if(in_array(getRightsId('support_request_listing_complete_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li ng-hide="errorStatus == 2"><a href="javascript:void(0);" ng-click="changeMultipleErrorLogStatus('complete');"><?php echo lang("SupportandErrorLog_Complete"); ?></a></li>
                        <?php } ?>
                        <?php if(in_array(getRightsId('support_request_listing_ignore_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li ng-show="errorStatus == 1"><a href="javascript:void(0);" ng-click="changeMultipleErrorLogStatus('ignore');"><?php echo lang("SupportandErrorLog_Ignore"); ?></a></li>
                        <?php } ?>
                        <?php if(in_array(getRightsId('support_request_listing_unignore_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li ng-show="errorStatus == 4"><a href="javascript:void(0);" ng-click="changeMultipleErrorLogStatus('unignore');"><?php echo lang("SupportandErrorLog_UnIgnore"); ?></a></li>
                        <?php } ?>
                    </ul>
                    <div class="total-count-view"><span class="counter">0</span> </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <!--/Info row-->

    <div id="errorloglistdiv" class="row-flued clear">
        <div class="panel panel-secondary">
                <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover support_error_table">
                <tbody>
                <tr>
                    <th id="Title" class="ui-sort" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('Title')">                           
                        <div class="shortdiv">Title<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="ErrorType" class="ui-sort" ng-click="orderByField = 'ErrorType'; reverseSort = !reverseSort; sortBY('ErrorType')">
                        <div class="shortdiv">Type<span class="icon-arrowshort hide">&nbsp;</span></div>                           
                    </th>
                    <th id="CreatedDate" class="ui-sort selected" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">
                        <div class="shortdiv sortedUp">Created Date<span class="icon-arrowshort">&nbsp;</span></div>
                    </th>
                    <th id="OperatingSystem" class="ui-sort" ng-click="orderByField = 'OperatingSystem'; reverseSort = !reverseSort; sortBY('OperatingSystem')">
                        <div class="shortdiv">Operating System<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="IPAddress" class="ui-sort" ng-click="orderByField = 'IPAddress'; reverseSort = !reverseSort; sortBY('IPAddress')">
                        <div class="shortdiv">IP Address<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="ErrorDescription" class="ui-sort" ng-click="orderByField = 'ErrorDescription'; reverseSort = !reverseSort; sortBY('ErrorDescription')">
                        <div class="shortdiv">Description<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th id="ErrorFiles" class="ui-sort" ng-click="orderByField = 'ErrorFiles'; reverseSort = !reverseSort; sortBY('ErrorFiles')">
                        <div class="shortdiv">Files<span class="icon-arrowshort hide">&nbsp;</span></div>
                    </th>
                    <th>Actions</th>
                </tr>

                <tr class="rowtr" ng-repeat="errorlog in listData[0].ObjErrors" ng-class="{selected : isSelected(errorlog)}" ng-init="errorlog.indexArr=$index" ng-click="selectCategory(errorlog);">
                    <td>{{errorlog.Title}}</td>
                    <td>{{errorlog.ErrorType}}</td>
                    <td>{{errorlog.CreatedDate}}</td>
                    <td>{{errorlog.OperatingSystem}}</td>
                    <td>{{errorlog.IPAddress}}</td>
                    <td>{{errorlog.sort_description}}</td>
                    <td class="filestd">
                        <img ng-show="errorlog.ErrorFiles > 0" src="<?php echo ASSET_BASE_URL ?>admin/img/fileimage.jpg" >
                        <label ng-show="errorlog.ErrorFiles == 0">No Files</label>
                    <td>
                        <a href="javascript:void(0);"  ng-click="SetErrorLog(errorlog);" class="user-action" onClick="userActiondropdown()">
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
            <ul class="dropdown-menu  userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if(in_array(getRightsId('support_request_listing_suppport_request_view'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionView"><a href="javascript:void(0);" ng-click="viewErrorLog()"><?php echo lang("SupportandErrorLog_View"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('support_request_listing_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionDelete"><a href="javascript:void(0);" ng-click="changeErrorLogStatus('delete')"><?php echo lang("Delete"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('support_request_listing_complete_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionComplete" ng-hide="currentLogStatusId == 2"><a href="javascript:void(0);" ng-click="changeErrorLogStatus('complete')"><?php echo lang("SupportandErrorLog_Complete"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('support_request_listing_ignore_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionIgnore" ng-hide="currentLogStatusId == 4 || currentLogStatusId == 2"><a href="javascript:void(0);" ng-click="changeErrorLogStatus('ignore')"><?php echo lang("SupportandErrorLog_Ignore"); ?></a></li>
                <?php } ?>
                <?php if(in_array(getRightsId('support_request_listing_unignore_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li id="ActionIgnore" ng-show="currentLogStatusId == 4"><a href="javascript:void(0);" ng-click="changeErrorLogStatus('unignore')"><?php echo lang("SupportandErrorLog_UnIgnore"); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
    <div id="accessdenieddiv"></div>
    
    <!--Popup for change error log status -->
    <div class="popup confirme-popup animated" id="confirmeErrorLogPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeErrorLogPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeErrorLogPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateErrorLogStatus('confirmeErrorLogPopup')"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>      
    <!--Popup for change error log status -->

    <!--Popup for change multiple logs status -->
    <div class="popup confirme-popup animated" id="confirmeMultipleErrorLogPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleErrorLogPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeMultipleErrorLogPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateErrorLogStatus('confirmeMultipleErrorLogPopup')"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>      
    <!--Popup for change multiple logs status -->
    
    <input type="hidden"  name="errorStatus" id="errorStatus" value="<?php echo $errorStatus; ?>"/>
    <input type="hidden" name="hdnSelectallPermission" id="hdnSelectallPermission" value="<?php echo $selectall_permission; ?>"/>
</div>
</section>