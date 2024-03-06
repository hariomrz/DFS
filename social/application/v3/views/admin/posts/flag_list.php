
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('index_flags'); ?></span></li>
                    <li>/</li>
                    <li><span><?php echo lang('index_posts'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">

<div class="container" ng-controller="postsCtrl" id="postsCtrl" data-ng-init="getPosts()" >
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang("index_flaged_posts"); ?></span> ({{totalRecord}})</h2>
        <div class="info-row-right rightdivbox">
            <div class="text-field search-field" data-type="focus">
                <div class="search-block">
                    <input type="text" value="" id="searchField">
                    <div class="search-remove">
                        <i class="icon-close10" id="clearText">&nbsp;</i>
                    </div>
                </div> 
                <input type="button" id="searchButtonA" class="icon-search search-btn">
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

                <table class="table table-hover" id="userlist_table">
                    <tbody>
                        <tr>
                            <th id="activity_post" class="ui-sort selected" ng-click="orderByField = 'activity_post'; reverseSort = !reverseSort; sortBY('activity_post')">                           
                                <div class="shortdiv sortedDown">Post <span class="icon-arrowshort">&nbsp;</span></div>
                            </th>
                            <th>
                                Author
                            </th>
                            <th id="created_date" class="ui-sort" ng-click="orderByField = 'created_date'; reverseSort = !reverseSort; sortBY('created_date')">                           
                                <div class="shortdiv sortedDown">Created Date <span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="flag_count" class="ui-sort" ng-click="orderByField = 'flag_count'; reverseSort = !reverseSort; sortBY('flag_count')">
                                <div class="shortdiv sortedDown">Total Flags<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                        <tr class="rowtr" ng-repeat="item in listData" ng-class="{selected : isSelected(item)}" ng-init="item.indexArr = $index" ng-click="selectCategory(userlist);">
                            <td>
                                 <span ng-if="item.activity_post.length <= ShowDescriptionLimit" ng-bind-html="item.activity_post"></span>
                                <span ng-if="item.activity_post.length > ShowDescriptionLimit">{{item.activity_post  | limitTo : ShowDescriptionLimit}}...</span>
                            </td>
                            
                            <td><span ng-bind="item.FirstName + ' ' + item.LastName"></span></td>
                            <td ng-bind="item.created_date"></td>
                            <td ng-bind="item.flag_count"></td>
                            <td>
                                <a href="#"  ng-click="SetItem(item);" class="user-action" onClick="userActiondropdown()">
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
                <li id="ActionEdit"><a ng-click="view_flag_details()"  href="javascript:void(0);"><?php echo lang("view_flag_details"); ?></a></li>
                <!-- <li id="ActionEdit"><a ng-click="view_post(data.Username,data.activity_guid)"  href="javascript:void(0);"><?php //echo lang("view_post"); ?></a></li> -->
                <li id="ActionEdit"><a ng-click="remove_flag(data.activity_guid)"  href="javascript:void(0);"><?php echo lang("remove_flag"); ?></a></li>
                <li id="ActionDelete"><a onclick="SetStatus(3);" href="javascript:void(0);"><?php echo lang("delete_post"); ?></a></li>
            </ul>
            <!--/Actions Dropdown menu-->
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    </div>

    <!--Popup for Delete a user  -->
    <div class="popup confirme-popup animated" id="delete_popup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?><i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p><?php echo lang('Sure_Delete'); ?> <b></b>?</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="delete_post();" type="button" id="advertise_deleted">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>
            </div>
        </div>
    </div>
    <!--Popup end Delete a user  -->

    <div class="popup confirme-popup animated" id="flag_popup">
        <div class="popup-title"><?php echo lang('flags_for'); ?> <i class="icon-close" onClick="closePopDiv('flag_popup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <div class="flag_post_content" ng-bind-html="data.activity_post"></div>
            <table class="users-table registered-user">
                <tr>
                    <th>Flag By</th>
                    <th>Reason</th>
                </tr>
                <tr ng-repeat="item in FlagList">
                    <td><span ng-bind="item.FirstName + ' ' + item.LastName"></span></td>
                    <td ng-bind="item.FlagReason"></td>
                </tr>
            </table>
        </div>
    </div>
</div>
</section>