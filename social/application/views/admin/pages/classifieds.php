<!--Info row-->
<div ng-controller="PageListCtrl" id="PageListCtrl" ng-cloak>
    <div ng-init="classifiedlist_view = 1;">
        <div class="info-row row-flued">
            <h2><?php echo lang('classifieds'); ?></h2>
            <div class="info-row-right rightdivbox" >
                <a class="button float-right marl10" href="<?php echo base_url(); ?>admin/pages/createclassified"><?php echo lang('classified_create') ?></a>


                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" ng-model="searchKey" value="" id="searchField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText" ng-click="reset_text_search();">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="searchButton" ng-click="search_pages();" class="icon-search search-btn">
                </div>
            </div>
            
            <div class="info-row">
                
                <a ng-show="ShowResetSearch == 1" class="button float-right marl10" ng-click="reset_page_search();">Reset Search</a>
                
                <div class="right-filter">
                    <label class="label"><?php echo lang('sub_category'); ?></label>
                    <select data-chosen="" ng-change="search_pages();" data-disable-search="true"  
                            data-ng-model="PageSearchSubCategory"
                            ng-options="SCat.category_id as SCat.name for SCat in SubCategoryData">
                        <option value=""></option>
                    </select>
                </div>
                <div class="right-filter" ng-init="PageCategories('', 'ParentCategory', 'CreateClassified')">
                    <label class="label"><?php echo lang('ParentCategory'); ?></label>
                    <select  data-chosen="" data-disable-search="true"  
                            data-ng-model="PageSearchCategory"
                            ng-change="PageSearchSubCategory=''; search_pages(); PageCategories(PageSearchCategory, 'SubCategory', 'CreateClassified'); "
                            ng-options="PCat.category_id as PCat.name for PCat in PCategoryData">
                        <option value=""></option>
                    </select>
                </div>
                <div class="right-filter">
                    <label class="label"><?php echo lang('location'); ?></label>
                    <div class="text-field search-field" data-type="focus">
                        
                        <input  ng-focus="LocationInitialize('SearchLocationID');" type="text" name="Location" id="SearchLocationID" placeholder="<?php echo lang('location'); ?>" 
                                ng-model='PageSearchLocation' 
                               >
                        
                    </div>
                </div>

                <?php if (in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))) { ?>
                    <!-- <div id="selectallbox" class="text-field selectbox" >
                        <span>
                            <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                        </span>
                        <label for="selectAll"><?php echo lang("Select_All"); ?></label>
                    </div> -->
                <?php } ?>
                <div id="ItemCounter" class="items-counter">
                    <ul class="button-list">
                        <?php //if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <!-- <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php //echo lang("User_Index_Communicate");   ?></a></li> -->
                        <?php //} ?>
                        <?php //if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <!-- <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php //echo lang("User_Index_Block");   ?></a></li> -->   
                        <?php //} ?>
                        <?php if (in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))) { ?>
                            <li><a href="javascript:void(0);" ng-hide="userStatus == 3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                        <?php } ?>
                        <?php //if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-show="userStatus==4" ng-click="SetMultipleUserStatus('unblock');"><?php //echo lang("User_Index_Unblock");   ?></a></li> -->
                        <?php //} ?>
                        <?php //if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <!-- <li><a href="javascript:void(0);" ng-show="userStatus==1" ng-click="SetMultipleUserStatus('approve');"><?php //echo lang("User_Index_Approve");   ?></a></li> -->
                        <?php //} ?>
                    </ul>
                    <div class="total-count-view"><span class="counter">0</span> </div>
                </div>

            </div>
            
            <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p class="text-center">{{confirmationMessage}}</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="updateStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
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
                        <button class="button" ng-click="delete_multiple_page()"><?php echo lang('Confirmation_popup_Yes');   ?></button>
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
                        <th id="Title" class="ui-sort" ng-click="orderByField = 'P.Title';
                                    reverseSort = !reverseSort;
                                    sortBY('Title')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('Title') ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort" id="CreatedBy"  ng-click="orderByField = 'CreatedBy';
                                reverseSort = !reverseSort;
                                sortBY('CreatedBy')">                           
                    <div class="shortdiv sortedDown">
                        Created By
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort selected" id="CreatedDate"  ng-click="orderByField = 'P.CreatedDate';
                                reverseSort = !reverseSort;
                                sortBY('CreatedDate')">                           
                    <div class="shortdiv sortedDown">
                        Date
                        <span class="icon-arrowshort ">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort" id="NoOfFavourites" ng-click="orderByField = 'P.NoOfFavourites';
                                reverseSort = !reverseSort;
                                sortBY('NoOfFavourites')">                           
                    <div class="shortdiv sortedDown"><?php echo lang('num_of_favourite') ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    
                    <th class="ui-sort" id="IsCommunityFeed" ng-click="orderByField = 'P.IsCommunityFeed';
                                reverseSort = !reverseSort;
                                sortBY('IsCommunityFeed')">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('is_community_feed') ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th class="ui-sort" id="">                           
                    <div class="shortdiv sortedDown">
                        <?php echo lang('Status') ?>
                        <span class="icon-arrowshort hide">&nbsp;</span>
                    </div>
                    </th>
                    <th><?php echo lang('Actions') ?></th>
                    </tr>
                    <tr class="rowtr" ng-repeat="Page in listData" ng-class="{selected : isSelected(Page)}" ng-init="Page.indexArr = $index" ><!-- ng-click="selectPage(Page);" -->
                        <td ng-bind="Page.Title"></td>
                        <td ng-bind="Page.CreatedBy"></td>
                        <td ng-bind="Page.CreatedDate"></td>
                        <td ng-bind="Page.NoOfFavourites"></td>
                        <td ng-bind="(Page.CategoryID != CommunityCategoryID) ? '' : ((Page.IsCommunityFeed == '1') ? 'Yes': 'No')"></td>
                        <td ng-bind="(Page.StatusID == '2') ? 'Publish' : ((Page.StatusID == '4') ? 'Unpublish' : Page.Status)"></td>
                        <td>
                            <a href="#"  ng-click="set_page_data(Page);" class="user-action" onClick="userActiondropdown()">
                                <i class="icon-setting">&nbsp;</i>
                            </a>
                        </td>
                    </tr>   
                    </tbody>
                </table>

                <div data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>

                <!--Actions Dropdown menu-->
                <ul class="action-dropdown userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                    <li ><a href="<?php echo base_url(); ?>admin/pages/editclassified/{{CurrentPageData.PageGUID}}"><?php echo lang("Edit"); ?></a></li>
                    
                    <li id="ActionInactive" data-ng-show="CurrentPageData.StatusID==2"><a ng-click="SetStatus(4);" href="javascript:void(0);"><?php echo lang('MakeUnpublish'); ?></a></li>
                    <li id="ActionActive" data-ng-show="CurrentPageData.StatusID==10 || CurrentPageData.StatusID==4"><a ng-click="SetStatus(2);" href="javascript:void(0);"><?php echo lang('MakePublish'); ?></a></li>
                    <li id="ActionDelete" ><a ng-click="SetStatus(3);" href="javascript:void(0);"><?php echo lang('User_Index_Delete'); ?></a></li>
                    
                    <li ng-if="CurrentPageData.CategoryID == CommunityCategoryID" data-ng-show="CurrentPageData.IsCommunityFeed==0"><a ng-click="set_classified_communityfeed();" href="javascript:void(0);"><?php echo lang('make_community_feed'); ?></a></li>
                    <li ng-if="CurrentPageData.CategoryID == CommunityCategoryID" data-ng-show="CurrentPageData.IsCommunityFeed==1"><a ng-click="set_classified_communityfeed();" href="javascript:void(0);"><?php echo lang('remove_community_feed'); ?></a></li>
                     
                </ul>
                <!--/Actions Dropdown menu-->
            </div>

            <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
    </div>
</div>