<div class="notify notify-default crm_on_check_div" style="display: none;" id="crm_check_div_footer"> 
    <div class="notify-text">
        <span class="count user_count_crm" ng-show="allUserSelected == 0"></span>
        <span class="count" ng-show="allUserSelected == 1">{{getSelectedUsersCount()}}</span>

        <span class="text">subscribers selected</span>
    </div>
    <div class="notify-option">

        <ul class="notify-tab">


            <li>
                <a  data-toggle="tooltip" 
                    uib-tooltip="Create List" 
                    ng-click="openNewsletterGroups()"
                    ng-class="(footerActiveTab == 'newsletter_group') ? 'active' : ''"
                    >
                    <span class="icon">
                        <i class="ficon-create-list  f-22"></i>
                    </span> 
                </a>
            </li>


            <li>
                <a ng-click="resetUserName();deleteUserConfirmBox()"  
                   uib-tooltip="Delete selected subscribers"
                   ng-class="(footerActiveTab == 'newsletter_delete_user') ? 'active' : ''"
                   >
                    <span class="icon">
                        <i class="ficon-bin"></i>
                    </span> 
                </a>
            </li>
        </ul>
    </div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="delete_popup">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" ng-click="footerActiveTab = ''">
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
                <button class="button wht" data-dismiss="modal"  ng-click="footerActiveTab = ''"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button"  ng-click="ChangeStatus('delete_popup');" id="button_on_delete" name="button_on_delete">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button> 
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>




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






<script id="pagination_template.html" type="text/ng-template"> 
                                     
            <li role="menuitem" ng-if="::boundaryLinks" ng-class="{disabled: noPrevious()||ngDisabled}" class="pagination-first">
                <a href class="page-link" aria-label="Previous"  ng-click="selectPage(1, $event)" ng-disabled="noPrevious()||ngDisabled" uib-tabindex-toggle>
                    <span aria-hidden="true"><i class="ficon-arrow-left">                            </i></span>
                            <span class="sr-only">Previous</span>
        </a                        >
    </li>
                    <li role="menuitem" ng-if="::directionLinks" ng-class="{disabled: noPrevious()||ngDisabled                    }" class="pagination-prev">
                <a href class="page-link" aria-label="Previous"  ng-click="selectPage(page - 1, $event)" ng-disabled="noPrevious(                        )||ngDisabled" uib-tabindex-toggle>
                    <span aria-hidden="true"><i class="ficon-arrow-left">                            </i></span>
                            <span class="sr-only">Previous</span>
        </a>                                                       
    </li>
                    <li role="menuitem" ng-repeat="page in pages track by $index" ng-class="{active: page.active,disabled: ngDisabled&&!page.active}" class="pagination-page"><a href ng-click="selectPage(page.number, $event)" ng-disabled="ngDisabled&&!page.active" uib-tabindex-toggle>{{page.text}}</a></li>
            <li role="menuitem" ng-if="::directionLinks" ng-class="{disabled: noNext()||ngDisabled}" class="pagination-next">
                <a class="page-link" href aria-label="Next"  ng-click="selectPage(page + 1, $event)" ng-disabled="noNext()||ngDisabled" uib-tabindex-toggle>
                    <span aria-hidden="true"><i class="ficon-arrow-right">                            </i></span>
                            <span class="sr-only">                    Next</span>
                </a>                             
            </li>
            <li role="menuitem" ng-if="::boundaryLinks" ng-class="{disabled: noNext()||ngDisabled}" class="pagination-last">
                <a class="page-link" href aria-label="Next"  ng-click="selectPage(totalPages, $event)" ng-disabled="noNext()||ngDisabled" uib-tabindex-toggle>
                    <span aria-hidden="true"><i class="ficon-arrow-right">                            </i></span>
                            <span class="sr-only">Next</span>
        </a>                                             
    </li>
    
</script>



 <div ng-include="newsletter_profile_view"></div>
 <div ng-include="newsletter_group_view"></div>                
 
 <div ng-include="newsletter_users_upload_view"></div>                



    <input type="hidden" value=""  id="hdnUserStatus">
    <input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
    <input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
    <input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>
