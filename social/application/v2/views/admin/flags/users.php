<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li>Flag</li>
                    <li>/</li>
                    <li><span><?php echo lang('User_Index_Users'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="main-container">
<div ng-controller="flagCtrl" id="flagCtrl"  class="container">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2"><?php echo lang("flagged_users"); ?></span></h2>
        <div class="info-row-right rightdivbox">

           
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <div data-pagination="" data-total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                <table class="table table-hover" id="userlist_table">
                    <tbody>
                        <tr>
                            <th id="username" class="ui-sort selected" ng-click="orderByField = 'username'; reverseSort = !reverseSort; sortBY('username')">                           
                                <div class="shortdiv sortedDown"><?php echo 'User'; ?><span class="icon-arrowshort">&nbsp;</span></div>
                            </th>

                            <th id="Email" class="ui-sort" ng-click="orderByField = 'Email'; reverseSort = !reverseSort; sortBY('Email')">                           
                                <div class="shortdiv sortedDown"><?php echo 'Email'; ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>

                            <th id="CreatedDate" class="ui-sort" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">                           
                                <div class="shortdiv sortedDown">Registered Date<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="flag_count" class="ui-sort" ng-click="orderByField = 'flag_count'; reverseSort = !reverseSort; sortBY('flag_count')">                         
                                <div class="shortdiv sortedDown">Total Flags<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>

                            <th id="sportaction" class="ui-sort">                           
                                <div class="shortdiv sortedDown">Actions<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                        </tr>
                        <tr class="rowtr" ng-repeat="FlaggedUser in FlaggedUserlistData[0].ObjPages" ng-class="{selected : isSelected(FlaggedUser)}" ng-init="FlaggedUser.indexArr = $index" ng-click="selectFlaggedUser(FlaggedUser);">

                            <td ng-bind="FlaggedUser.Username"></td>
                            <td ng-bind="FlaggedUser.Email"></td>
                            <td ng-bind="FlaggedUser.created_date"></td>
                            <td ng-bind="FlaggedUser.flag_count"></td>
                            <td><a href="#"  ng-click="set_user_flagged_data(FlaggedUser);" class="user-action" onClick="userActiondropdown()">
                                    <i class="icon-setting">&nbsp;</i>
                                </a></td>
                        </tr>   
                       <!--  <span id="result_message" class="result_message"><?php //echo lang("ThereIsNoRecordToShow");  ?></span> -->
                    </tbody>
                </table>

                <div data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>

            </div>
            </div>
                <!--Actions Dropdown menu-->
                <ul class="dropdown-menu userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                    <li id="ActionEdit"><a ng-click="view_flag_details()"  href="javascript:void(0);">Details</a></li>
                    <li><a href="javascript:void(0);" ng-click="view_flagged_user(FlaggedUserProfileURL, FlaggedUserGUID)"><?php echo lang('View'); ?></a></li>
                    <li><a href="javascript:void(0);" onclick="openPopDiv('delete_popup', 'bounceOutDown');">Delete User</a></li>
                    <li><a href="javascript:void(0);" onclick="openPopDiv('block_popup', 'bounceOutDown');">Block User</a></li>
                    <li><a href="javascript:void(0);" onclick="openPopDiv('remove_flag_popup', 'bounceOutDown');">Remove Flag</a></li>   

                </ul>
                <!--/Actions Dropdown menu-->

            <!--Popup for Delete a user  -->
            <div class="popup confirme-popup animated" id="delete_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p>Are you sure you want to delete ?</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="change_flagged_user_status('delete_popup', '3');" id="button_on_delete" name="button_on_delete">
                            <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <!--Popup end Delete a user  -->


            <!--Popup for Block a user  -->
            <div class="popup confirme-popup animated" id="block_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('block_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p>Are you sure you want to block ?</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('block_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="bloack_flagged_user('block_popup', '4');" id="button_on_delete" name="button_on_delete">
                            <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <!--Popup end Block a user  -->

            <!--Popup for remove flag -->
            <div class="popup confirme-popup animated" id="remove_flag_popup">
                <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('remove_flag_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <p>Are you sure you want to remove ?</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('remove_flag_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="change_flag_status('remove_flag_popup', '3');" id="button_on_delete" name="button_on_delete">
                            <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <!--Popup end remove flag -->

            <!--Popup for add/edit sports  -->
            <div id="athletic_type_popup" class="popup changepwd animated">
                <div class="popup-title">{{AthleticPopupName}} <i onclick="closePopDiv('athletic_type_popup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                <div class="popup-content popup-padding">
                    <form method="post" name="athleticTypeform" id="athletic_type_form" autocomplete="off">
                        <div class="form-content">  

                            <div class="form-control">
                                <div data-type="focus" class="text-field">
                                    <span ng-if="athletictype == '2'">
                                        <input type="text" name="athleticTpeName" placeholder="Athletic Type Name" id="athleticTpeName" value="">
                                    </span>
                                    <span ng-if="athletictype == '3'">
                                        <input type="text" name="athleticTpeName" placeholder="Measurement Type Name" id="athleticTpeName" value="">
                                    </span>                                   
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" ng-bind="athleticTypeform.NameError"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>



                            <div class="form-control">
                                <button class="button wht" onClick="closePopDiv('athletic_type_popup', 'bounceOutUp');"><?php echo lang('ChangePassword_popup_Cancel'); ?></button>
                                <button class="button" ng-click="add_athletic_type('athletic_type_popup');" id="button_update_sport" name="button_update_sport">
                                    <span class="loading-button">&nbsp;</span>{{AthleticAddBtnTxt}}
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            <!--Popup end for change password of a user  -->

            <div class="popup confirme-popup animated" id="flag_popup">
                <div class="popup-title"><?php echo lang('flags_for'); ?> {{FlaggedUser}} <i class="icon-close" onClick="closePopDiv('flag_popup', 'bounceOutUp');">&nbsp;</i></div>
                <div class="popup-content">
                    <table class="users-table registered-user">
                        <tr>
                            <th>Flag By</th>
                            <th>Reason</th>
                        </tr>
                        <tr ng-repeat="item in FlagList">
                            <td><span ng-bind="item.Username"></span></td>
                            <td ng-bind="item.FlagReason"></td>
                        </tr>
                    </table>
                </div>
            </div>
             

            <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
    </div>
</div>
</section>
<input type="hidden" value="<?php //echo $UserStatus;  ?>" id="hdnUserStatus">
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden" name="hdnSportID" id="hdnSportID" value="">
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
<input type="hidden" name="hdnFlagType" id="hdnFlagType" value="UserFlag">
<input type="hidden" name="hdnAthleticAchievementTypeID" id="hdnAthleticAchievementTypeID" value="">
<input type="hidden"  name="" id="pageName" value="<?php //echo $page_name; ?>"/>
<input type="hidden" name="hdnachievementType" id="hdnachievementType" value=""/>
<!-- <input type="hidden" name="hdnathleticType" id="hdnathleticType" value="<?php echo $athletictype; ?>"/>
-->