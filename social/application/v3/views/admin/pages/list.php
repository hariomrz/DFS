<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Pages</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
    <div class="container" ng-controller="pagesCtrl" id="pagesCtrl">
        <!--Info row-->
        <div class="info-row row-flued">
            <h2><span id="spnh2">Pages</span></h2>
            <div class="info-row-right rightdivbox">
                <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" value="" id="searchPagesField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="searchPagesButton" class="icon-search search-btn">
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
                    <table class="table table-hover universities" id="userlist_table">
                    <tbody>
                        <tr>
                            <th id="Title" class="ui-sort" ng-click="orderByField = 'Title'; reverseSort = !reverseSort; sortBY('Title')">                           
                                <div class="shortdiv sortedDown"><?php echo 'Name'; ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="UserName" class="ui-sort" ng-click="orderByField = 'UserName'; reverseSort = !reverseSort; sortBY('UserName')">                           
                                <div class="shortdiv sortedDown"><?php echo 'Created By'; ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="createddate" class="ui-sort" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('createddate')">                           
                                <div class="shortdiv sortedDown">Created On<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th  id="IsVerified" style="width:9%" class="ui-sort" ng-click="orderByField = 'IsVerified'; reverseSort = !reverseSort; sortBY('IsVerified')">
                                <div class="shortdiv">Verification<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="sportsAction" class="ui-sort">                           
                                <div class="shortdiv sortedDown">Actions<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                        </tr>
                        <tr class="rowtr" ng-repeat="Organization in OrganizationlistData[0].ObjPages" ng-class="{
                                    selected : isSelectedOrganization(Organization)
                                }" ng-init="Organization.indexArr = $index" ng-click="selectOrganization(Organization);">
                            <!--<td ng-bind="$index+1"></td>-->
                            <td ng-bind="Organization.Title"></td>
                            <td ng-bind="Organization.CreatedBy"></td>
                            <td ng-bind="Organization.createddate"></td>
                            <td ng-click="set_organization_data(Organization);">
                                <button ng-if="Organization.VerificationRequest==1 && Organization.IsVerified != '1'" class="button wht" ng-click="confirmVerify(Organization, $index);">Verify</button>
                                <button ng-if="Organization.VerificationRequest==1 && Organization.IsVerified == '1'" class="button" ng-click="confirmUnVerify(Organization, $index);">Verified</button>
                            </td>
                            <td>
                                <a href="#"  ng-click="set_organization_data(Organization);" class="user-action" onClick="userActiondropdown()">
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
                <li><a href="javascript:void(0);" onclick="SetStatus(3);"><?php echo lang('delete'); ?></a></li>
            </ul>
            <!--/Actions Dropdown menu-->
        </div>

                <!--Popup for Delete a user  -->
                <div class="popup confirme-popup animated zindex-popup-lg" id="delete_popup">
                    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
                    <div class="popup-content">
                        <p><?php echo lang('Sure_Delete'); ?> ?</p>
                        <div class="communicate-footer text-center">
                            <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                            <button class="button" ng-click="remove_page();" id="button_on_delete" name="button_on_delete">
                                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <!--Popup end Delete a user  -->

                <!--Popup for add/remove users  -->
                <div class="popup confirme-popup animated" id="organization_popup">
                    <div class="popup-title"> View/Edit Organization <i class="icon-close" onClick="closePopDiv('organization_popup', 'bounceOutUp');">&nbsp;</i></div>
                    <div class="popup-content">
                        <form method="post" name="sportpositionform" id="update_sport_form" autocomplete="off">


                            <div class="form-group">
                                <label class="control-label">Add users as admin</label>
                                <div class="input-group">
                                    <!-- <tags-input ng-model="Tags" add-on-paste="true"  key-property="UserID" add-from-autocomplete-only="true" class="bootstrap" placeholder="Add User" display-property="Name" replace-spaces-with-dashes="false" > -->
                                    <tags-input class="form-control" ng-model="Tags" add-from-autocomplete-only="true" class="bootstrap"  placeholder="Add User" display-property="Name" key-Property="UserID" replace-spaces-with-dashes="false">
                                        <auto-complete source="loadUsersTags($query)"></auto-complete>
                                    </tags-input>  
                                    <a class="button input-group-addon btn-addon" ng-click="add_users(Tags);" id="button_add_sport_position" name="button_add_sport_position">
                                        <span class="loading-button">&nbsp;</span>Add User
                                    </a>                                
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" ng-bind="ErrorMsg"></div>
                                <div class="success-text" ng-bind="SuccessMsg"></div>
                                <div class="clearfix">&nbsp;</div>


                            </div>

                        </form>
                        <div class="table-responsive-vertical">
                            <table class="users-table registered-user">
                                <tr>
                                    <th>UserName</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                <tr ng-repeat="Creator in PageCreator">
                                    <td><span ng-bind="Creator.UserName"></span></td>
                                    <td><span ng-if="Creator.ModuleRoleID == 7">Creator</span><span ng-if="Creator.ModuleRoleID == 8">Admin</span><span ng-if="Creator.ModuleRoleID == 9">Follower</span></td>
                                    <td ng-if="PageCreator.length > 1"><a href="javascript:void(0);" ng-click="setcreatorData(Creator);" onclick="openPopDiv('delete_popup', 'bounceOutDown');" >Remove</a></td>
                                    <!-- <td ng-if="PageCreator.length>1"><a href="javascript:void(0);" ng-click="remove_user(Creator);">Remove</a></td> -->
                                    <td ng-if="PageCreator.length <= 1">Remove</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <!--Popup end for add/remove users  -->

                <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>            
    
        <input type="hidden" value="<?php //echo $UserStatus;     ?>" id="hdnUserStatus">
        <input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
        <input type="hidden" name="hdnSportID" id="hdnSportID" value="">
        <input type="hidden" name="hdnSportPageType" id="hdnSportPageType" value="Position">

        <input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
        <input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
        <input type="hidden"  name="" id="pageName" value="<?php //echo $page_name;    ?>"/>
        <!--Popup for featured a user  -->
        <div class="popup confirme-popup animated" id="verify_popup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('verify_popup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p>Are you sure you want to verify {{currentUserName}}?</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onClick="closePopDiv('verify_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button"  ng-click="ChangeVerifyStatus('verify_popup', 1);" id="button_on_featured" name="button_on_featured">
                        <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div>
        <!--Popup end featured a user  -->

        <!--Popup for unfeatured a user  -->
        <div class="popup confirme-popup animated" id="unverify_popup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('unverify_popup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p>Are you sure you want to unverify {{currentUserName}}?</p>

                <div class="communicate-footer text-center">
                    <button class="button wht" onClick="closePopDiv('unverify_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button"  ng-click="ChangeVerifyStatus('unverify_popup', 0);" id="button_on_unfeatured" name="button_on_unfeatured">
                        <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                    </button>
                </div>
            </div>
        </div>
        <!--Popup end unfeatured a user  -->
    </div>
</section>