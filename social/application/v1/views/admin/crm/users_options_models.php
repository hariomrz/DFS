<div class="notify notify-default crm_on_check_div" style="display: none;" id="crm_check_div_footer"> 
    <div class="notify-text">
        <span class="count user_count_crm" ng-show="allUserSelected == 0"></span>
        <span class="count" ng-show="allUserSelected == 1">{{getSelectedUsersCount()}}</span>

        <span class="text">users selected</span>
    </div>
    <div class="notify-option">

        <ul class="notify-tab">

            

            <li ng-if="showingFilterData.StatusID != 3 && showingFilterData.StatusID != 4" data-toggle="tooltip" uib-tooltip="Block selected user(s)">
                <a onclick="SetStatusCrmModel(4);" ng-click="resetUserName()" >
                    <span class="icon">
                        <i class="ficon-block-user"></i>
                    </span> 
                </a>
            </li>   
            
            <li ng-if="showingFilterData.StatusID == 4" data-toggle="tooltip" uib-tooltip="Unblock selected user(s)">
                <a onclick="SetStatusCrmModel(2);" ng-click="resetUserName()" >
                    <span class="icon">
                        <i class="ficon-block-user"></i>
                    </span> 
                </a>
            </li>   
            
            <li ng-if="showingFilterData.StatusID != 3"  data-toggle="tooltip" uib-tooltip="Delete selected user(s)">
                <a onclick="SetStatusCrmModel(3);" ng-click="resetUserName()">
                    <span class="icon">
                        <i class="ficon-bin"></i>
                    </span> 
                </a>
            </li> 
             <li ng-if="showingFilterData.StatusID != 3 && showingFilterData.StatusID != 4" data-toggle="tooltip" uib-tooltip="Send Push notification to selected user(s)">
                <a ng-click="showSendNotificationSelectedModel(0)">
                    <span class="icon">
                        <i class="ficon-email"></i>
                    </span> 
                </a>
            </li>
              <li ng-if="showingFilterData.StatusID != 3 && showingFilterData.StatusID != 4" data-toggle="tooltip" uib-tooltip="Send SMS to selected user(s)">
                <a ng-click="showSendNotificationSelectedModel(1)">
                    <span class="icon">
                        <i class="ficon-phone"></i>
                    </span> 
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="delete_popup_confirm_box">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title">Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete {{DeletingUserTxt}} ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" ng-click="deleteUser();">YES</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="delete_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> </h4>
            </div>
            <div class="modal-body">
                <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
            </div>
            <div class="modal-footer">
                <button class="button wht" data-dismiss="modal"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button"  ng-click="ChangeStatus('delete_popup');" id="button_on_delete" name="button_on_delete">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button> 
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="block_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> </h4>
            </div>
            <div class="modal-body">
                <p>
                    <span ng-if="showingFilterData.StatusID != 4"><?php echo lang('Sure_Block'); ?> </span>
                    <span ng-if="showingFilterData.StatusID == 4"> Are you sure you want to unblock </span>
                    <b>{{currentUserName}}</b>?
                </p>
            </div>
            <div class="modal-footer">
                <button class="button wht" data-dismiss="modal"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="ChangeStatus('block_popup');" id="button_on_block" name="button_on_block">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--Popup end Block a user  -->


<!--Popup for UnBlock a user  -->

<div class="modal fade" tabindex="-1" role="dialog" id="unblock_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> </h4>
            </div>
            <div class="modal-body">
                <p><?php echo lang('Sure_Unblock'); ?> <b>{{currentUserName}}</b>?</p>
            </div>
            <div class="modal-footer">
                <button class="button wht" data-dismiss="modal"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="ChangeStatus('unblock_popup');" id="button_on_unblock" name="button_on_unblock">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Popup end UnBlock a user  -->

<!--Popup for Approve a user  -->

<div class="modal fade" tabindex="-1" role="dialog" id="approve_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> </h4>
            </div>
            <div class="modal-body">
                <p><?php echo lang('Sure_Approve'); ?> <b>{{currentUserName}}</b> ?</p>
            </div>
            <div class="modal-footer">
                <button class="button wht" data-dismiss="modal"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="ChangeStatus('approve_popup');" id="button_on_approve" name="button_on_approve">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--Popup end Approve a user  -->

<!--Popup for unsuspended a user  -->

<div class="modal fade" tabindex="-1" role="dialog" id="suspended_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        <i class="ficon-cross"></i>
                    </span>
                </button>
                <h4 class="modal-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> </h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to unsuspend this account.</p>
            </div>
            <div class="modal-footer">
                <button class="button wht" data-dismiss="modal"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="ChangeStatus('suspended_popup');" id="button_on_suspended" name="button_on_suspended">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--Popup end Approve a user  -->
<!--Popup for pushnotification a user  -->
<div class="popup confirme-popup animated" id="pushnotification_popup">
    <div class="popup-title">{{popupTitle}}&nbsp;</i></div>
    <div class="popup-content">
        <form role="form" ng-submit="sendNotificationSelectedUser();">
                    <div class="modal-body has-padding">
                        <div class="form-group">
                            <label >Title</label><span class="color-red">*</span>
                            <input type="text" class="form-control" name="noti_title" id="noti_title" ng-model="userObj.notification_title" placeholder="Title" autocomplete="off" />
                            <label for="noti_title" class="error hide" id="noti_title_error"></label>
                        </div>
                        <div class="form-group mojis-start-textarea"> 
                            <label data-ng-bind="lang.message"></label>
                             
                            <textarea autofocus class="form-control" id="message" name="message" placeholder="Message" ng-model="userObj.notification_text" maxlength="300" >  
                            </textarea>
                            <label for="message" class="error hide" id="message_error"></label>
                        </div>
                        <div class="form-group">
                            <label class="label">Activity ID</label>
                            <input class="form-control" type="text" id="ActivityGUID" name="ActivityGUID" placeholder="Activity ID" ng-model="userObj.ActivityGUID">
                            </textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal" onclick="closePopDiv('pushnotification_popup', 'bounceOutUp');" ng-click="userObj={};userObj.user_unique_id=[];deselectUser();" data-ng-bind="lang.close"></button>
                        <button ng-disabled= (!userObj.notification_title) type="submit" class="btn btn-primary"><i class=""></i>Send</button>
                    </div>
                </form>
    </div>
</div>
<!--Popup end pushnotification a user  -->


    <div class="modal fade" id="ward_feature" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" ng-click="close_mark_as_feature_modal('ward_feature');"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4>Mark as Featured User for</h4>
                </div>
                <div class="modal-body custom-scroll scroll-md">
                	<div class="popup-content" style="padding: 0;">
                        <div class="form-group"> 
                            <label for="" class="label">About {{currentUserName}} </label>
                            <textarea class="form-control" name="fabout" id="fabout" placeholder="Enter description about {{currentUserName}}" ng-model="fabout" style="min-height: 60px;" ></textarea>                                
                        </div>
                        <div class="form-group"> 
                            <ul ng-if="ward_list">
                                <li ng-repeat="(key, item) in ward_list">
                                        <label ng-if="item.WID == 1" class="checkbox checkbox-inline checkbox-block" ng-click="select_ward(item.WID);">
                                            <input  type="checkbox"  value="{{item.WID}}" id="ward_feature_chk" class="ward_feature_checkbox">
                                            <span class="label"></span>
                                        </label>
                                        <label ng-if="item.WID != 1"  class="checkbox checkbox-inline checkbox-block" ng-click="select_ward(item.WID);">
                                            <input type="checkbox" value="{{item.WID}}" id="ward_feature_chk_{{item.WID}}" class="ward_feature_checkbox" >
                                            <span class="label"></span>
                                        </label>&nbsp;<p ng-if="item.WID == 1" style="display: inline-block;">ALL WARD - Entire Indore</p>
                                        <p ng-if="item.WID != 1" style="display: inline-block;">WARD {{item.WNumber}} - {{item.WName}}</p>
                                </li>
                            </ul>
                            <ul ng-if="!ward_list">
                                <p>No wards available</p>
                            </ul>
                        </div>
                
                    </div>
                	<button class="button btn pull-right EditTag ward-visibility-save-btn" ng-click="mark_user_as_feature();">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vip_user" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" ng-click="close_mark_as_vip_modal('vip_user');"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4>Mark as VIP User</h4>
                </div>
                <div class="modal-body custom-scroll scroll-md">
                	<div class="popup-content" style="padding: 0;">
                        <div class="form-group"> 
                            <label for="" class="label">About {{currentUserName}} </label>
                            <textarea class="form-control" name="fabout" id="fabout" placeholder="Enter description about {{currentUserName}}" ng-model="fabout" style="min-height: 60px;" ></textarea>                                
                        </div>                
                    </div>
                	<button class="button btn pull-right  vip-save-btn" ng-click="mark_user_as_vip();">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="association_user" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" ng-click="close_mark_as_vip_modal('association_user');"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4>Mark as Association User</h4>
                </div>
                <div class="modal-body custom-scroll scroll-md">
                	<div class="popup-content" style="padding: 0;">
                        <div class="form-group"> 
                            <label for="" class="label">About {{currentUserName}} </label>
                            <textarea class="form-control" name="fabout" id="fabout" placeholder="Enter description about {{currentUserName}}" ng-model="fabout" style="min-height: 60px;" ></textarea>                                
                        </div>                
                    </div>
                	<button class="button btn pull-right  association-save-btn" ng-click="mark_user_as_association();">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="top_contributor_message" ng-cloak data-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close dis-cret-m" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close" ng-click="resetPopup();"></i></span></button>
                    <h4>Send Notification to Top Contributors</h4>
                </div>
                <div class="modal-body">
                <div class="popup-content">
                    <div class="communicate-footer row-flued">

                        <div class="from-subject">
                            <label for="subjects" class="label">Redirect At</label>
                            <div> 
                                <select data-chosen="" data-disable-search="true"  ng-options="PUrl.MKey as PUrl.Name for PUrl in Urls" data-ng-model="topContributorObj.Url" ng-change="show_url_option()">
                                    
                                </select>                        
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_redirect}}</div>
                        </div>
                        
                        <div ng-if="UrlOptions==1" class="from-subject">
                            <label for="subjects" class="label">Activity ID</label>
                            <div class="text-field">
                                <input type="text" ng-model="topContributorObj.ActivityGUID">
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_activity}}</div>
                        </div>

                        <div ng-if="UrlOptions==6" class="from-subject">
                            <label for="subjects" class="label">User ID</label>
                            <div class="text-field">
                                <input type="text" ng-model="topContributorObj.UserGUID">
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_user}}</div>
                        </div>

                     <!--   <div ng-if="UrlOptions==5" class="from-subject">
                            <label for="subjects" class="label">URL</label>
                            <div class="text-field">
                                <input type="text" ng-model="announcement.CustomURL">
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_custom_url}}</div>
                        </div>
-->
                        <div ng-if="UrlOptions==7" class="from-subject">
                            <label class="label">Select Quiz</label>    
                            <div>
                                <tags-input 
                                    ng-model="topContributorObj.Quiz" 
                                    display-property="Name" 
                                    placeholder="Select quiz" 
                                    on-tag-added="addQuizAdded($tag)"
                                    on-tag-removed="addQuizAdded($tag)"
                                    max-tags=1
                                    replace-spaces-with-dashes="false" 
                                    add-from-autocomplete-only="true"
                                    template="tagsTemplate">
                                    <auto-complete source="getQuiz($query)" load-on-focus="true" min-length="0" max-results-to-show="25" ></auto-complete>
                                </tags-input>
                                <script type="text/ng-template" id="tagsTemplate">
                                    <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                        <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                        <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                    </div>
                                </script>                                    
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_quiz}}</div>
                        </div>

                        <div ng-if="UrlOptions==2" class="from-subject">
                            <label class="label">Post Tag</label>    
                            <div>
                                <tags-input 
                                    ng-model="topContributorObj.Tag" 
                                    display-property="Name" 
                                    placeholder="Select post tag" 
                                    on-tag-added="addTagAdded($tag)"
                                    on-tag-removed="addTagAdded($tag)"
                                    max-tags=1
                                    replace-spaces-with-dashes="false" 
                                    add-from-autocomplete-only="true"
                                    template="tagsTemplate">
                                    <auto-complete source="getActivityTags($query,'ACTIVITY')" load-on-focus="true" min-length="0" max-results-to-show="15" ></auto-complete>
                                </tags-input>
                                <script type="text/ng-template" id="tagsTemplate">
                                    <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                        <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                        <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                    </div>
                                </script>                                    
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_tag}}</div>
                        </div>

                        <div ng-if="UrlOptions==4" class="from-subject">
                            <label class="label">Classified Category</label>    
                            <div>
                                <tags-input 
                                    ng-model="topContributorObj.Tag" 
                                    display-property="Name" 
                                    placeholder="Select classified category" 
                                    on-tag-added="addTagAdded($tag)"
                                    on-tag-removed="addTagAdded($tag)"
                                    max-tags=1
                                    replace-spaces-with-dashes="false" 
                                    add-from-autocomplete-only="true"
                                    template="tagsTemplate">
                                    <auto-complete source="loadTagCategories($query, 6)" load-on-focus="true" min-length="0" max-results-to-show="15" ></auto-complete>
                                </tags-input>
                                <script type="text/ng-template" id="tagsTemplate">
                                    <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                        <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                        <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                    </div>
                                </script>                        
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_tag}}</div>
                        </div>
                        <div class="from-subject">
                            <label for="subjects" class="label">Title</label>
                            <div class="text-field">
                                <input type="text" maxlength="140" data-req-maxlen="140" ng-model="topContributorObj.Title">
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_title}}</div>
                        </div>
                        <div class="from-subject"> 
                            <label class="label" for="subject">Message</label>
                            <div class="text-field ">
                                <textarea class="textarea"  ng-model="topContributorObj.Description"></textarea> 
                            </div>
                            <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_description}}</div>
                        </div>
                                                
                        <div class="from-subject" style="height: 50px;">
                            <button class="button wht dis-cret-m"  ng-click="resetPopup()">Cancel</button>
                            <button class="button dis-cret-m" ng-click="send_top_contributor_notification();">Send</button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>  

</div>


</section>

<input type="hidden" value=""  id="hdnUserStatus">
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>








<div class="modal fade" tabindex="-1" role="dialog" id="communicate_single_user" ng-controller="messageCtrl"> 
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
                    <span aria-hidden="true"><i class="icon-close"></i></span> 
                </button>
                <h4 class="modal-title"><?php echo lang('User_Index_Communicate'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="user-detial-block">
                    <a class="user-thmb" href="javascript:void(0);">
                        <img ng-src="{{user.ProfilePictureUrl}}" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser"></a>
                    <div class="overflow">
                        <a class="name-txt" href="javascript:void(0);" id="lnkUserName">{{user.Name}}</a>
                        <div class="dob-id">
                            <span id="spnProcessDate">Member Since: {{user.Membersince}} </span><br>
                            <a id="lnkUserEmail" href="javascript:void(0);">{{user.Email}} </a>
                        </div>
                    </div>
                </div>
                <div class="communicate-footer row-flued">
                    <div class="form-group">
                        <label for="subjects" class="label">Subject</label>
                        <input type="text" class="form-control" value="" name="Subject" id="emailSubject" >
                        <div class="error-holder" ng-show="showError" style="color: #CC3300;" ng-bind="errorMessage"></div>
                    </div>
                    <div class="text-msz editordiv">
                        <?php //echo $this->ckeditor->editor('description', @$default_value); ?>
                        <textarea id="description" name="description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                        <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;" ng-bind="errorBodyMessage"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button ng-click="sendEmail(user, 'users')" class="btn btn-primary pull-right" type="submit" id="btnCommunicateSingle"><?php echo lang('Submit'); ?></button>
            </div>
        </div>
    </div>
</div>            




<div class="communicate-morelist">
    <div id="dvtipcontent" class="tip-content"> <i class="icon-tiparrow">&nbsp;</i> </div>
</div>
<!--Popup for Communicate/send message to a multiple user -->






<div class="modal fade" tabindex="-1" role="dialog" id="communicateMultiple" ng-controller="messageCtrl"> 
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
                    <span aria-hidden="true"><i class="icon-close"></i></span> 
                </button>
                <h4 class="modal-title"><?php echo lang('User_Index_Communicate'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="user-detial-block">
                    <div class="multiple-comunicate" id="dvmorelist"></div>        
                </div>
                <div class="communicate-footer row-flued">
                    <div class="form-group">
                        <label class="label" for="subject">Subject</label>
                    <div class="text-field">
                        <input type="text" value="" name="Subject" id="Subject" >
                    </div>
                    <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
                        
                        
                    </div>
                    <div class="text-msz editordiv">
                        <textarea id="communication_description" name="communication_description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                        <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;" ng-bind="errorBodyMessage"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="hdnUsersId" id="hdnUsersId" value=""/>
            <button ng-click="sendEmailToMultipleUsers('users')" class="button float-right" type="submit" id="btnCommunicateMultiple"><?php echo lang('Submit'); ?></button>
            </div>
        </div>
    </div>
</div>            









<div class="popup communicate animated" id="communicateMultiple_0000" ng-controller="messageCtrl" ng-if="0">    
    <div class="popup-title"><?php echo lang('User_Index_Communicate'); ?> <i class="icon-close" onClick="closePopDiv('communicateMultiple', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content loader_parent_div">
        <i class="loader_communication btn_loader_overlay"></i>
        <div class="multiple-comunicate" id="dvmorelist"></div>        
        <div class="communicate-footer row-flued">
            <div class="from-subject">                
                <label class="label" for="subject">Subject</label>
                <div class="text-field">
                    <input type="text" value="" name="Subject" id="Subject" >
                </div>
                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
            </div>
            <div class="text-msz editordiv">
                <?php //echo $this->ckeditor->editor('communication_description', @$default_value); ?>
                <textarea id="communication_description" name="communication_description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;" ng-bind="errorBodyMessage"></div>
            </div>
            <input type="hidden" name="hdnUsersId" id="hdnUsersId" value=""/>
            <button ng-click="sendEmailToMultipleUsers('users')" class="button float-right" type="submit" id="btnCommunicateMultiple"><?php echo lang('Submit'); ?></button>
        </div>
    </div>
</div>
<!--Popup end Communicate/send message to a multiple user -->

<div class="popup confirme-popup animated" id="confirmeMultipleUserPopup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p class="text-center">{{confirmationMessage}}</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onclick="closePopDiv('confirmeMultipleUserPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button" ng-click="updateUsersStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
        </div>
    </div>
</div>
