<!--Info row-->
<div ng-controller="PageListCtrl" id="PageListCtrl" ng-cloak>
    <div ng-init="business_req_view = 1">
        <div class="info-row row-flued">
            
            <h2><?php echo lang('business_request'); ?></h2>
            
            <div class="info-row-right rightdivbox" >
                
                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" ng-model="search_business_req_model" value="" id="searchBRequestField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText" ng-click="reset_search_business_req();">&nbsp;</i>
                        </div>
                    </div>
                    <input type="button" id="searchButton" ng-click="search_business_req();" class="icon-search search-btn">
                </div>

                <!--div id="ItemCounter" class="items-counter">
                    <ul class="button-list">
                        <?php if (in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))) { ?>
                            <li><a href="javascript:void(0);" ng-hide="userStatus == 3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                        <?php } ?>
                    </ul>
                    <div class="total-count-view"><span class="counter">0</span> </div>
                </div-->
            </div>

            <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p class="text-center">{{confirmationMessage}}</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="updateBRequestStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                    </div>
                </div>
            </div> 
            <!--Popup end Delete page  -->
            <div class="popup confirme-popup animated" id="confirmeMultipleUniversityPopup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p class="text-center"><?php echo lang('Sure_Delete') ?></p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="delete_multiple_promotion()"><?php echo lang('Confirmation_popup_Yes');   ?></button>
                    </div>
                </div>
            </div>
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div>


<!-- <div class="showingdiv blog-publish"><label>All</label> <span>(15)</span><span class="devider-line">|</span><label>Published</label> <span>(6)</span></div> -->
                <div data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                <table class="users-table page" id="userlist_table">
                    <tbody>
                        <tr>
                            <th class="ui-sort" id="BusinessName" ng-click="orderByField = 'business_name';
                                reverseSort = !reverseSort;
                                sortBRequestBY('BusinessName')">                           
                    <div class="shortdiv sortedDown"><?php echo 'Business Name' ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                            <th id="BusinessFirstName" class="ui-sort" ng-click="orderByField = 'business_firstname';
                                        reverseSort = !reverseSort;
                                        sortBRequestBY('BusinessFirstName')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('breq_firstname'); ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    </th>
                            <th id="BusinessLastName" class="ui-sort" ng-click="orderByField = 'business_lastname';
                                        reverseSort = !reverseSort;
                                        sortBRequestBY('BusinessLastName')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('breq_lastname'); ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort" id="BusinessEmail"  ng-click="orderByField = 'business_email';
                                reverseSort = !reverseSort;
                                sortBRequestBY('BusinessEmail')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo 'Email'; ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <!--th class="ui-sort">
                        <?php //echo 'URL'; ?>
                    </th-->
                    <th class="ui-sort">
                        <?php echo 'Phone'; ?>
                    </th>
                    
                    <th class="ui-sort selected" id="CreatedDate" ng-click="orderByField = 'created_date';
                                reverseSort = !reverseSort;
                                sortBRequestBY('CreatedDate')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo 'Date'; ?>
                        <span class="icon-arrowshort ">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort" id="Status" ng-click="orderByField = 'status_text';
                                reverseSort = !reverseSort;
                                sortBRequestBY('Status')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('Status') ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th><?php echo lang('Actions') ?></th>
                    </tr>
                    <tr class="rowtr" ng-repeat="Request in requestListData" ng-init="Request.indexArr = $index" >
                        <td ng-bind="Request.business_name"></td>
                        <td ng-bind="Request.business_firstname"></td>
                        <td ng-bind="Request.business_lastname"></td>
                        <td ng-bind="Request.business_email"></td>
                        <!--td ng-bind="Request.business_url"></td-->
                        <td ng-bind="Request.business_phone"></td>
                        <td ng-bind="Request.created_date"></td>
                        <td ng-bind="(Request.status_id == '2') ? 'Pending' : Request.status_text"></td>
                        <td>
                            <a href="#"  ng-click="set_busi_request_data(Request);" class="user-action" onClick="userActiondropdown()">
                                <i class="icon-setting">&nbsp;</i>
                            </a>
                        </td>
                    </tr>   
                    </tbody>
                </table>

                <div data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>

                <!--Actions Dropdown menu-->
                <ul class="action-dropdown userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                    <li data-ng-show="CurrentBRequestData.status_id!=18"><a href="<?php echo base_url().'admin/pages/create?breq='?>{{CurrentBRequestData.communication_id}}"><?php echo lang("page_create"); ?></a></li>
                    <li ><a href="<?php echo base_url().'admin/pages/requestdetail/'?>{{CurrentBRequestData.communication_id}}"><?php echo lang("View"); ?></a></li>
                    <li id="ActionActive" data-ng-show="CurrentBRequestData.status_id!=3"><a ng-click="SetStatus(3);" href="javascript:void(0);"><?php echo lang('User_Index_Delete'); ?></a></li>
                </ul>
                <!--/Actions Dropdown menu-->
            </div>

            <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
        
    </div>
</div>