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
             <li data-toggle="tooltip" uib-tooltip="Send Push notification to selected user(s)">
                <a ng-click="showSendNotificationSelectedModel(0)">
                    <span class="icon">
                        <i class="ficon-email"></i>
                    </span> 
                </a>
            </li>
              <li data-toggle="tooltip" uib-tooltip="Send SMS to selected user(s)">
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



<!--Popup for Delete a user  -->
<!--<div class="popup confirme-popup animated" id="delete_popup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
        <div class="communicate-footer text-center">
            <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
            <button class="button"  ng-click="ChangeStatus('delete_popup');" id="button_on_delete" name="button_on_delete">
                <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
            </button>
        </div>
    </div>
</div>-->
<!--Popup end Delete a user  -->


<!--Popup for Block a user  -->

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
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('pushnotification_popup', 'bounceOutUp');">&nbsp;</i></div>
    <div class="popup-content">
        <form role="form" ng-submit="sendNotificationSelectedUser();">
                    <div class="modal-body has-padding">
                        <div class="form-group">
                            <label data-ng-bind="lang.selected_email"></label>
                            <textarea class="form-control" id="selected_user_name" disabled="" name="selected_email" ng-bind="userObj.selected_user_name">
                            </textarea>
                        </div>
                        <label for="selected_user_name" class="error hide" id="selected_user_name_error"></label>
                        
                    <!--<div class="form-group">
                            <label data-ng-bind="lang.subject"></label>
                            <input type="text" class="form-control" id="subject" name="subject" ng-model="userObj.subject">
                            <label for="subject" class="error hide" id="subject_error"></label>
                        </div> -->
                        <!---------------------smiley will apear here ----->

                        <!-- Start tab section-->
                        <div class="mojis-tab">
                            <div class="">
                                <div class="navbar-tabs">
                                    <ul class="tabs-nav clearfix">
                                        <li class="active"><a href="#General"  data-toggle="tab" aria-expanded="true">🙂</a></li>
                                        <li class=""><a href="#Activities" data-toggle="tab" aria-expanded="false">🇦🇨</a></li>
                                        <!-- <li class=""><a href="#Communication" data-toggle="tab" aria-expanded="false">😀</a></li>
                                        <li class=""><a href="#Notes"  data-toggle="tab" aria-expanded="false">😀</a></li>
                                        <li class=""><a href="#Usage" data-toggle="tab" aria-expanded="false">😀</a></li> -->
                                    </ul>
                                </div>
                                <div class="tab-block">
                                    <div class="tab-content">
                                        <div class="tab-pane fade activities-tabs active in" id="General">
                                            <div class="tab-block-div">
                                                <ul>
                                                    <li><a href="#" ng-click="insertAtCaret('😀')">  😀</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😃')">  😃</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😄')">  😄</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😁')">  😁</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😆')">  😆</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😅')">  😅</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤣')">  🤣</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😂')">  😂</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙂')">  🙂</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙃')">  🙃</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😉')">  😉</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😊')">  😊</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😇')">  😇</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥰')">  🥰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😍')">  😍</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤩')">  🤩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😘')">  😘</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😗')">  😗</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😚')">  😚</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😙')">  😙</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😋')">  😋</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😛')">  😛</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😜')">  😜</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤪')">  🤪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😝')">  😝</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤑')">  🤑</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤗')">  🤗</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤭')">  🤭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤫')">  🤫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤔')">  🤔</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤐')">  🤐</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤨')">  🤨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😐')">  😐</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😑')">  😑</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😶')">  😶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😏')">  😏</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😒')">  😒</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙄')">  🙄</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😬')">  😬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤥')">  🤥</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😌')">  😌</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😔')">  😔</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😪')">  😪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤤')">  🤤</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😴')">  😴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😷')">  😷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤒')">  🤒</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤕')">  🤕</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤢')">  🤢</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤮')">  🤮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤧')">  🤧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥵')">  🥵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥶')">  🥶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥴')">  🥴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😵')">  😵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤯')">  🤯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤠')">  🤠</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥳')">  🥳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😎')">  😎</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤓')">  🤓</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🧐')">  🧐</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😕')">  😕</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😟')">  😟</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙁')">  🙁</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😮')">  😮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😯')">  😯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😲')">  😲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😳')">  😳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🥺')">  🥺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😦')">  😦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😧')">  😧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😨')">  😨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😰')">  😰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😥')">  😥</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😢')">  😢</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😭')">  😭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😱')">  😱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😖')">  😖</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😣')">  😣</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😞')">  😞</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😓')">  😓</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😩')">  😩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😫')">  😫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😤')">  😤</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😡')">  😡</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😠')">  😠</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤬')">  🤬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😈')">  😈</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👿')">  👿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💀')">  💀</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💩')">  💩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤡')">  🤡</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👹')">  👹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👺')">  👺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👻')">  👻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👽')">  👽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👾')">  👾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤖')">  🤖</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😺')">  😺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😸')">  😸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😹')">  😹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😻')">  😻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😼')">  😼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😽')">  😽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙀')">  🙀</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😿')">  😿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('😾')">  😾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙈')">  🙈</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙉')">  🙉</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙊')">  🙊</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💯')">  💯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💢')">  💢</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💥')">  💥</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💫')">  💫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💦')">  💦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💨')">  💨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🕳')">  🕳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💣')">  💣</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💬')">  💬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🗨')">  🗨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🗯')">  🗯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💭')">  💭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💤')">  💤</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👋')">  👋</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤚')">  🤚</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🖐')">  🖐</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('✋')">  ✋</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🖖')">  🖖</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👌')">  👌</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('✌')">  ✌</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤞')">  🤞</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤟')">  🤟</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤘')">  🤘</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤙')">  🤙</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👈')">  👈</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👉')">  👉</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👆')">  👆</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🖕')">  🖕</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👇')">  👇</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('☝')">  ☝</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👍')">  👍</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👎')">  👎</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('✊')">  ✊</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👊')">  👊</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤛')">  🤛</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤜')">  🤜</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👏')">  👏</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙌')">  🙌</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👐')">  👐</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤲')">  🤲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤝')">  🤝</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🙏')">  🙏</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('✍')">  ✍</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💅')">  💅</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🤳')">  🤳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('💪')">  💪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🦵')">  🦵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🦶')">  🦶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👂')">  👂</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👃')">  👃</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🧠')">  🧠</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🦷')">  🦷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🦴')">  🦴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👀')">  👀</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👁')">  👁</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👅')">  👅</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👄')">  👄</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🗣')">  🗣</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👤')">  👤</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('👥')">  👥</a></li>
                                                </ul>
                                            </div>
                                         </div>
                                         <div class="tab-pane fade" id="Activities">
                                              <div class="tab-block-div">
                                                <ul>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇨')"> 🇦🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇩')"> 🇦🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇪')"> 🇦🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇫')"> 🇦🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇬')"> 🇦🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇮')"> 🇦🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇱')"> 🇦🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇲')"> 🇦🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇴')"> 🇦🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇶')"> 🇦🇶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇷')"> 🇦🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇸')"> 🇦🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇹')"> 🇦🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇺')"> 🇦🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇼')"> 🇦🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇽')"> 🇦🇽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇦🇿')"> 🇦🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇦')"> 🇧🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇧')"> 🇧🇧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇩')"> 🇧🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇪')"> 🇧🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇫')"> 🇧🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇬')"> 🇧🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇭')"> 🇧🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇮')"> 🇧🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇯')"> 🇧🇯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇱')"> 🇧🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇲')"> 🇧🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇳')"> 🇧🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇴')"> 🇧🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇶')"> 🇧🇶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇷')"> 🇧🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇸')"> 🇧🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇹')"> 🇧🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇻')"> 🇧🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇼')"> 🇧🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇾')"> 🇧🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇧🇿')"> 🇧🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇦')"> 🇨🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇨')"> 🇨🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇩')"> 🇨🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇫')"> 🇨🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇬')"> 🇨🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇭')"> 🇨🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇮')"> 🇨🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇰')"> 🇨🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇱')"> 🇨🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇲')"> 🇨🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇳')"> 🇨🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇴')"> 🇨🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇵')"> 🇨🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇷')"> 🇨🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇺')"> 🇨🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇻')"> 🇨🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇼')"> 🇨🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇨🇽')"> 🇨🇽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇪')"> 🇩🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇬')"> 🇩🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇯')"> 🇩🇯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇰')"> 🇩🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇲')"> 🇩🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇴')"> 🇩🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇩🇿')"> 🇩🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇦')"> 🇪🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇨')"> 🇪🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇪')"> 🇪🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇬')"> 🇪🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇭')"> 🇪🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇷')"> 🇪🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇸')"> 🇪🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇹')"> 🇪🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇪🇺')"> 🇪🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇮')"> 🇫🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇯')"> 🇫🇯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇰')"> 🇫🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇲')"> 🇫🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇴')"> 🇫🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇫🇷')"> 🇫🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇦')"> 🇬🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇧')"> 🇬🇧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇩')"> 🇬🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇪')"> 🇬🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇫')"> 🇬🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇬')"> 🇬🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇭')"> 🇬🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇮')"> 🇬🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇱')"> 🇬🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇲')"> 🇬🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇳')"> 🇬🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇵')"> 🇬🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇶')"> 🇬🇶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇷')"> 🇬🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇸')"> 🇬🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇹')"> 🇬🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇺')"> 🇬🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇼')"> 🇬🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇬🇾')"> 🇬🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇰')"> 🇭🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇲')"> 🇭🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇳')"> 🇭🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇷')"> 🇭🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇹')"> 🇭🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇭🇺')"> 🇭🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇨')"> 🇮🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇩')"> 🇮🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇪')"> 🇮🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇱')"> 🇮🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇲')"> 🇮🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇳')"> 🇮🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇴')"> 🇮🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇶')"> 🇮🇶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇷')"> 🇮🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇸')"> 🇮🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇮🇹')"> 🇮🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇯🇪')"> 🇯🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇯🇲')"> 🇯🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇯🇴')"> 🇯🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇯🇵')"> 🇯🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇪')"> 🇰🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇬')"> 🇰🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇭')"> 🇰🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇮')"> 🇰🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇲')"> 🇰🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇳')"> 🇰🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇵')"> 🇰🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇷')"> 🇰🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇼')"> 🇰🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇾')"> 🇰🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇰🇿')"> 🇰🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇦')"> 🇱🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇧')"> 🇱🇧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇨')"> 🇱🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇮')"> 🇱🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇰')"> 🇱🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇷')"> 🇱🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇸')"> 🇱🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇹')"> 🇱🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇺')"> 🇱🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇻')"> 🇱🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇱🇾')"> 🇱🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇦')"> 🇲🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇨')"> 🇲🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇩')"> 🇲🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇪')"> 🇲🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇫')"> 🇲🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇬')"> 🇲🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇭')"> 🇲🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇰')"> 🇲🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇱')"> 🇲🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇲')"> 🇲🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇳')"> 🇲🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇴')"> 🇲🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇵')"> 🇲🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇶')"> 🇲🇶</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇷')"> 🇲🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇸')"> 🇲🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇹')"> 🇲🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇺')"> 🇲🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇻')"> 🇲🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇼')"> 🇲🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇽')"> 🇲🇽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇾')"> 🇲🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇲🇿')"> 🇲🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇦')"> 🇳🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇨')"> 🇳🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇪')"> 🇳🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇫')"> 🇳🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇬')"> 🇳🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇮')"> 🇳🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇱')"> 🇳🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇴')"> 🇳🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇵')"> 🇳🇵</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇷')"> 🇳🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇺')"> 🇳🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇳🇿')"> 🇳🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇴🇲')"> 🇴🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇦')"> 🇵🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇪')"> 🇵🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇫')"> 🇵🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇬')"> 🇵🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇭')"> 🇵🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇰')"> 🇵🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇱')"> 🇵🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇲')"> 🇵🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇳')"> 🇵🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇷')"> 🇵🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇸')"> 🇵🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇹')"> 🇵🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇼')"> 🇵🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇵🇾')"> 🇵🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇶🇦')"> 🇶🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇷🇪')"> 🇷🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇷🇴')"> 🇷🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇷🇸')"> 🇷🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇷🇺')"> 🇷🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇷🇼')"> 🇷🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇦')"> 🇸🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇧')"> 🇸🇧</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇨')"> 🇸🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇩')"> 🇸🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇪')"> 🇸🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇬')"> 🇸🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇭')"> 🇸🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇮')"> 🇸🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇯')"> 🇸🇯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇰')"> 🇸🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇱')"> 🇸🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇲')"> 🇸🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇳')"> 🇸🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇴')"> 🇸🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇷')"> 🇸🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇸')"> 🇸🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇹')"> 🇸🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇻')"> 🇸🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇽')"> 🇸🇽</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇾')"> 🇸🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇸🇿')"> 🇸🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇦')"> 🇹🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇨')"> 🇹🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇩')"> 🇹🇩</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇫')"> 🇹🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇬')"> 🇹🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇭')"> 🇹🇭</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇯')"> 🇹🇯</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇰')"> 🇹🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇱')"> 🇹🇱</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇲')"> 🇹🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇳')"> 🇹🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇴')"> 🇹🇴</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇷')"> 🇹🇷</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇹')"> 🇹🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇻')"> 🇹🇻</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇼')"> 🇹🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇹🇿')"> 🇹🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇦')"> 🇺🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇬')"> 🇺🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇲')"> 🇺🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇳')"> 🇺🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇸')"> 🇺🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇾')"> 🇺🇾</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇺🇿')"> 🇺🇿</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇦')"> 🇻🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇨')"> 🇻🇨</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇪')"> 🇻🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇬')"> 🇻🇬</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇮')"> 🇻🇮</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇳')"> 🇻🇳</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇻🇺')"> 🇻🇺</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇼🇫')"> 🇼🇫</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇼🇸')"> 🇼🇸</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇽🇰')"> 🇽🇰</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇾🇪')"> 🇾🇪</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇾🇹')"> 🇾🇹</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇿🇦')"> 🇿🇦</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇿🇲')"> 🇿🇲</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🇿🇼')"> 🇿🇼</a></li>
                                                    <li><a href="#" ng-click="insertAtCaret('🏴')">󠁧󠁢</a></li>󠁥󠁮󠁧󠁿   🏴󠁧󠁢󠁥󠁮󠁧󠁿

                                                </ul>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="Communication">
                                               <div class="tab-block-div">
                                                <ul>
                                                    <li>
                                                        <a href="#">
                                                            😋
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="Notes">
                                              <div class="tab-block-div">
                                                <ul>
                                                    <li>
                                                        <a href="#">
                                                            😬
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>                         
                                       <div class="tab-pane fade" id="Usage">
                                              <div class="tab-block-div">
                                                <ul>
                                                    <li>
                                                        <a href="#">
                                                          🤑
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li><li>
                                                        <a href="#">
                                                            😃
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                      </div>                      
                                    </div>
                                </div>
                            </div>
                         </div>
                        

  
                        <!--End tab section-->
                        
                        <!---------------------smiley will apear here ----->

                        <div class="form-group mojis-start-textarea"> 
                            <label data-ng-bind="lang.message"></label>
                             <a  ng-if="userObj.isSms!=1" class="mojis-start" href="#" ng-click="openEmojiBox()">☺</a>
                            <textarea autofocus class="form-control" id="message" name="message" ng-model="userObj.notification_text" >  
                            </textarea>
                            <label for="message" class="error hide" id="message_error"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal" onclick="closePopDiv('pushnotification_popup', 'bounceOutUp');" ng-click="userObj={};userObj.user_unique_id=[];deselectUser();" data-ng-bind="lang.close"></button>
                        <button type="submit" class="btn btn-primary"><i class=""></i>Send</button>
                    </div>
                </form>
    </div>
</div>
<!--Popup end pushnotification a user  -->

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
