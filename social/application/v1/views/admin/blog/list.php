<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Blogs</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div class="container" ng-controller="blogController" id="blogController">
    <div ng-init="list_view=1;">
        <div class="info-row row-flued">
            <h2><?php echo lang('blogs');?></h2>
            <div class="info-row-right rightdivbox" >
                <a class="button float-right marl10" href="<?php echo base_url();?>admin/blog/create"><?php echo lang('create_blog')?></a>
              

                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" ng-model="search_blog_model" value="" id="searchField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText" ng-click="blog_reset_search();">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="searchButton" ng-click="search_blog();" class="icon-search search-btn">
                </div>

                <?php if(in_array(getRightsId('communicate_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
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
                            <!-- <li><a href="javascript:void(0);" ng-click="CommunicateMultipleUsers();"><?php //echo lang("User_Index_Communicate"); ?></a></li> -->
                        <?php //} ?>
                        <?php //if(in_array(getRightsId('block_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <!-- <li><a href="javascript:void(0);" ng-show="userStatus==2" ng-click="SetMultipleUserStatus('block');"><?php //echo lang("User_Index_Block"); ?></a></li> -->   
                        <?php //} ?>
                        <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li><a href="javascript:void(0);" ng-hide="userStatus==3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                        <?php } ?>
                        <?php //if(in_array(getRightsId('unblock_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <!-- <li><a href="javascript:void(0);" ng-show="userStatus==4" ng-click="SetMultipleUserStatus('unblock');"><?php //echo lang("User_Index_Unblock"); ?></a></li> -->
                        <?php //} ?>
                        <?php //if(in_array(getRightsId('approve_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <!-- <li><a href="javascript:void(0);" ng-show="userStatus==1" ng-click="SetMultipleUserStatus('approve');"><?php //echo lang("User_Index_Approve"); ?></a></li> -->
                        <?php //} ?>
                    </ul>
                    <div class="total-count-view"><span class="counter">0</span> </div>
                </div>
                
            </div>
            <!--Popup for Delete a user  -->
            <div class="popup confirme-popup animated" id="delete_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="delete_blog();" id="button_on_delete" name="button_on_delete">
                            <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <!--Popup end Delete a user  -->
            <div class="popup confirme-popup animated" id="confirmeMultipleUniversityPopup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p class="text-center"><?php echo lang('Sure_Delete')?></p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="delete_multiple_blogs()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                    </div>
                </div>
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
                    <table class="table table-hover blog" id="userlist_table">
                    <tbody>
                    <tr>
                        <th id="Title" class="ui-sort selected" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('Title')">                           
                            <div class="shortdiv sortedDown">
                            <?php echo lang('Title')?>
                            <span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th class="ui-sort">                           
                            <div class="shortdiv sortedDown" ><?php echo lang('Author')?><span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th class="ui-sort" id="CreatedDate"  ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">                           
                            <div class="shortdiv sortedDown">
                            Date
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th class="ui-sort" id="NoOfLikes" ng-click="orderByField = 'NoOfLikes'; reverseSort = !reverseSort; sortBY('NoOfLikes')">                           
                            <div class="shortdiv sortedDown"><?php echo lang('num_of_like')?>
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                         <th class="ui-sort" id="NoOfComments" ng-click="orderByField = 'NoOfComments'; reverseSort = !reverseSort; sortBY('NoOfComments')">                           
                            <div class="shortdiv sortedDown">
                            <?php echo lang('num_of_comments')?>
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th class="ui-sort" id="">                           
                            <div class="shortdiv sortedDown">
                            <?php echo lang('Status')?>
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th><?php echo lang('Actions')?></th>
                    </tr>
                    <tr class="rowtr" ng-repeat="Blog in listData" ng-class="{selected : isSelected(Blog)}" ng-init="Blog.indexArr=$index" ng-click="selectCategory(Blog);">
                        <td ng-bind="Blog.Title"></td>
                        <td ng-bind="Blog.Author"></td>
                        <td ng-bind="Blog.CreatedDate"></td>
                        <td ng-bind="Blog.NoOfLikes"></td>
                        <td ng-bind="Blog.NoOfComments"></td>
                        <td ng-bind="Blog.Status"></td>
                        <td>
                            <a href="#"  ng-click="set_blog_data(Blog);" class="user-action" onClick="userActiondropdown()">
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
                    <li ><a ng-click="edit_blog_show();" href="javascript:void(0);"><?php echo lang("Edit"); ?></a></li>   
                    <li ><a onclick="openPopDiv('delete_popup', 'bounceInDown');" href="javascript:void(0);"><?php echo lang("User_Index_Delete"); ?></a></li>
                    <li ><a  ng-if="university_data.StatusID==2" ng-click="verify_university_toggle();" href="javascript:void(0);"><?php echo lang("verify"); ?></a></li>
                    <li ><a  ng-if="university_data.StatusID==5" ng-click="verify_university_toggle();" href="javascript:void(0);"><?php echo lang("unverified"); ?></a></li>
            </ul>
                <!--/Actions Dropdown menu-->
        </div>
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>
</div>
</section>