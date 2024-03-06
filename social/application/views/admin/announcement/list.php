<!-- Main Content -->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span>Announcement</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Info row-->
<section class="main-container">
<div ng-controller="announcementController" id="announcementController" class="container">
    <div ng-init="list_view=1;">
        <div class="info-row row-flued">
            <h2>Announcement</h2>
            <div class="info-row-right rightdivbox" >
                <a href="javascript:void(0);" class="btn-link" ng-click="add_new_message()">
                                <ins class="buttion-icon" style="margin: 0;"><i class="icon-add">&nbsp;</i></ins> <span>Add Announcement</span> </a>
              

                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" ng-model="search_blog_model" value="" id="searchField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText" ng-click="blog_reset_search();">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="searchButton" ng-click="search_blog();" class="icon-search search-btn">
                </div>

                <div id="ItemCounter" class="items-counter">
                    <ul class="button-list">
                        <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li><a href="javascript:void(0);" ng-hide="userStatus==3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                        <?php } ?>
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
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table">
                    <tbody>
                    <tr>  
                        <th id="Title" class="ui-sort selected" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('Title')">                           
                            <div class="shortdiv sortedDown">
                            Title
                           <span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="Description" class="ui-sort selected" ng-click="orderByField = 'Description'; reverseSort = !reverseSort; sortBY('Description')">                           
                            <div class="shortdiv sortedDown">
                            Message Text
                            <span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="EntityType" class="ui-sort selected" ng-click="orderByField = 'EntityType'; reverseSort = !reverseSort; sortBY('EntityType')">                           
                            <div class="shortdiv sortedDown">
                           Type
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                      
                        <th class="ui-sort" id="Status"  ng-click="orderByField = 'Status'; reverseSort = !reverseSort; sortBY('Status')">                           
                            <div class="shortdiv sortedDown">
                            <?php echo lang('Status')?>
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th class="ui-sort" id="CreatedDate" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">                           
                            <div class="shortdiv sortedDown">Published On
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                       
                        <th><?php echo lang('Actions')?></th>
                    </tr>
                    <tr class="rowtr" ng-repeat="Data in listData" ng-init="Data.indexArr=$index">
                        <td ng-bind="Data.Title"></td>
                        <td ng-bind="Data.Description"></td>
                        <td>
                            <span ng-if="Data.EntityType ==2 ">Welcome Message</span>
                            <span ng-if="Data.EntityType ==4 ">Announcement</span>
                            <span ng-if="Data.EntityType ==3 ">Introduction Text</span>
                        </td>
                        <td ng-bind="Data.Status"></td>
                        <td ng-bind="Data.CreatedDate"></td>
            
                        <td>
                            <a href="#"  ng-click="set_data(Data);" class="user-action" onClick="userActiondropdown()">
                                <i class="icon-setting">&nbsp;</i>
                            </a>
                        </td>
                    </tr>   
                    <tr id="noresult_td" ng-if="listData.length==0"><td colspan="7"><div class="no-content text-center"><p>No record found</p></div></td></tr>
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
                    <li ><a ng-click="edit_data();" href="javascript:void(0);"><?php echo lang("Edit"); ?></a></li>   
                       <li><a ng-click="save_announcement('DRAFT');" href="javascript:void(0);" ng-if="announcement.Status=='PUBLISHED'">Draft</a></li>   
                    <li><a ng-click="save_announcement('PUBLISHED');" href="javascript:void(0);" ng-if="announcement.Status=='DRAFT'">Publish</a></li>   
                    <li ng-if="announcement.EntityType!=2 && announcement.EntityType!=3"><a onclick="openPopDiv('delete_popup', 'bounceInDown');" href="javascript:void(0);"><?php echo lang("User_Index_Delete"); ?></a></li> 
                </ul>
                <!--/Actions Dropdown menu-->

            <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
    </div>
    <div class="popup communicate animated" id="addNewMessage">
        <div class="popup-title"><span ng-if="announcement.EntityType ==2 ">Welcome Message</span>
                    <span ng-if="announcement.EntityType ==4 ">Announcement</span>
                    <span ng-if="announcement.EntityType ==3 ">Introduction Text</span> <i class="icon-close" ng-click="cance_action()" onClick="closePopDiv('addNewMessage', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <div class="communicate-footer row-flued">
                   <div class="from-subject">
                <label for="subjects" class="label">Title</label>
                <div class="text-field">
                    <input type="text" ng-model="announcement.Title">
                </div>
                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
            </div>
                <div class="from-subject"> 
                    <label class="label" for="subject">Message text</label>
                    <div class="text-field ">
                        <textarea class="textarea" ng-model="announcement.Description"></textarea> 
                    </div>
                    <div class="clearfix">&nbsp;</div>
                    <div class="error-holder usrerror">{{Error.error_description}}</div>
                    <div class="clearfix">&nbsp;</div>
                </div> 
                <button class="button wht" onClick="closePopDiv('addNewMessage', 'bounceOutUp');" ng-click="cance_action()">Cancel</button>
                <button class="button" ng-click="save_announcement('PUBLISHED');">Publish</button>
                <button ng-if="announcement.BlogGUID== '' || announcement.Status=='DRAFT' "  class="button" ng-click="save_announcement('DRAFT');">Draft</button>
            </div>
        </div>
    </div>
</div>
</section>