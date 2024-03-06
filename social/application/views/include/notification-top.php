<span ng-if="total_count > 0" class="notify-count" ng-cloak data-toggle="dropdown" ng-click="checkNotificationToggle();">
    <span ng-if="total_count < 100" ng-bind="total_count"></span>
    <span ng-if="total_count > 99">99+</span>
</span>
<span ng-if="total_count <= 0" class="icon ficon-notification" ng-cloak data-toggle="dropdown" ng-click="checkNotificationToggle();">
</span>
<div class="dropdown-menu" data-type="stopPropagation">
    <div class="notification">
        <div class="notify-content">
            <div role="tabpanel">
                <!-- Nav tabs -->
                <ul class="notification-tab">
                    <li class="active">
                        <a data-target="#notify" aria-controls="notify" data-toggle="tab">
                            <span class="space-icon"><i class="ficon-notification"></i></span>
                            {{::lang.notifications}}
                            <span class="count-inner" ng-if="notification_count < 100 && notification_count > 0" ng-bind="'(' + notification_count + ')'"></span>
                            <span class="count-inner" ng-if="notification_count > 99 && notification_count > 0">(99+)</span>
                        </a>
                    </li>
                    <li ng-if="SettingsData.m25 == 1" ng-cloak>
                        <a data-toggle="tab" ng-click="getMessages(); updateUnseenStatus();" aria-controls="msg" data-target="#msg" aria-expanded="false">
                            <span class="space-icon"><i class="ficon-envelope"></i></span>
                            {{::lang.messages}}
                            <span class="count-inner" ng-if="message_count < 100 && message_count > 0" ng-bind="'(' + message_count + ')'"></span>
                            <span class="count-inner" ng-if="message_count > 99 && message_count > 0">(99+)</span>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="notify">
                        <ul class="sublisting">
                            <li ng-show="TotalUnread > 0">
                                <a ng-click="markAllAsRead()">
                                    {{::lang.notify_mark_all_as_read}}
                                </a>
                            </li>
                            <li ng-show="TotalUnread > 0 && show_all_notify == 0">
                                <a ng-click="get_unread_notification()">
                                    {{::lang.notify_show_unread}} <span ng-bind="'(' + TotalUnread + ')'"></span></a>
                            </li>
                            <li ng-show="show_all_notify == 1"><a ng-click="show_all_notification();">{{::lang.notify_show_all}}</a></li>
                            <li>
                                <a href="{{::BaseUrl}}notification/settings">
                                    {{::lang.settings}}
                                </a>
                            </li>
                        </ul>
                        <div class="notification-content" id="notifyscroll">
                            <div class="panel-body nodata-panel" ng-show="nlen == 0" ng-cloak>
                                <div class="nodata-text p-v-elg">
                                    <span class="nodata-media">
                                        <span class="icon">
                                            <i class="ficon-notifications f-60"></i>
                                        </span>
                                    </span>
                                    <h5>{{::lang.notify_blank_msg_heading}}</h5>
                                </div>
                            </div>
                            <ul class="notification-list">
                                <li ng-repeat="notify in notification track by $index" repeat-done="repeatDoneBCard();" ng-init="prevent_event()" ng-click="readNotification(notify)" ng-class="{'read': (notify.StatusID == '17') ,'unread': (notify.StatusID !== '17')}" repeat-done="notificationRepeatDone();">
                                    <i class="{{notify.Class}}"></i>
                                    <figure class="thumb50" ng-if="notify.Members.length == 0">
                                        <a class="thumb50 loadbusinesscard" entityguid="{{notify.UserGUID}}" entitytype="user">
                                            <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-if="notify.ProfilePicture !== '' && notify.ProfilePicture !== 'user_default.jpg'" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + notify.ProfilePicture}}"  >

                                            <img err-Name="{{notify.P1[0].FirstName + ' ' + notify.P1[0].LastName}}" ng-if="(notify.ProfilePicture == '' || notify.ProfilePicture == 'user_default.jpg') && notify.P1[0].ModuleID == '3'"   ng-src="{{AssetBaseUrl}}img/profiles/">

                                            <img err-Name="{{notify.P1[0].FirstName + ' ' + notify.P1[0].LastName}}" ng-if="(notify.ProfilePicture == '' || notify.ProfilePicture == 'user_default.jpg') && notify.P1[0].ModuleID == '1'"   ng-src="{{::ImageServerPath}}upload/profile/220x220/group-no-img.jpg">

                                            <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-if="(notify.ProfilePicture == '' || notify.ProfilePicture !== 'user_default.jpg') && notify.P1[0].ModuleID !== '3'" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg"  >
                                        </a>
                                    </figure>
                                    <div ng-if="notify.Members.length > 0" ng-class="(notify.Members.length > 2) ? 'group-thumb' : 'group-thumb-two';" class="m-user-thmb group-thumb">
                                        <span ng-repeat="nfy in notify.Members">
                                            <img  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + nfy.ProfilePicture}}" entitytype="user" ng-if="$index <= 2">
                                        </span>
                                    </div>
                                    <div class="description">
                                        <div class="post-thumb" ng-if="notify.Album.length && notify.Album[0].AlbumName == 'Wall Media'"> <img  ng-src="{{ImageServerPath + 'upload/wall/220x220/' + getThumbImage(notify.Album[0].Media[0].ImageName)}}"> </div>
                                        <div class="post-thumb" ng-if="notify.Album.length && notify.Album[0].AlbumName !== 'Wall Media'"> <img  ng-src="{{ImageServerPath + 'upload/album/220x220/' + getThumbImage(notify.Album[0].Media[0].ImageName)}}"> </div>
                                        <div class="list-desc">
                                            <a ng-bind="notify.UserName"></a>
                                            <span class="font-medium" ng-bind-html="to_trusted(notify.NotificationText, notify.NotificationTypeID);"></span> <span ng-if="notify.Summary !== ''" ng-cloak>

                                                <span class="post-msz" ng-bind-html="html_parse(notify.Summary)"></span></span> <span class="hrs" ng-bind="date_format((notify.CreatedDate))"></span>

                                            <ul class="subnav-btn accept-{{notify.UserGUID}}" ng-if="notify.ShowAcceptDeny == '1'">
                                                <li title="{{::lang.notify_accept}}" data-toggle="tooltip" ng-click="acceptRequestNote(notify, $index, true)">

                                                    <span class="icon">
                                                        <i class="ficon-check"></i>
                                                    </span>
                                                </li>
                                                <li title="{{::lang.notify_deny}}" data-toggle="tooltip" ng-click="denyRequestNote(notify, $index, true)">
                                                    <span class="icon">
                                                        <i class="ficon-cross"></i>
                                                    </span>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <i class="notify-icon-n-read icon-n-read" ng-if="notify.StatusID != '17'" data-toggle="tooltip" title="{{::lang.notify_mark_as_read}}" ng-click="readNotification(notify, $index, 1)">&nbsp;</i> </li>
                            </ul>
                        </div>
                        <div class="loader absolute notification-loader" ng-if="nloader == '1'" style="display:block;">
                            <div class="spinner32"></div>
                        </div>
                        <div class="panel-footer" ng-cloak ng-if="nlen > 0">
                            <a href="notifications" target="_self" class="view-more">
                                {{::lang.view_all}}
                                {{::lang.notifications}}
                            </a>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="msg">
                        <div class="notification-content mCustomScrollbar">
                            <div class="panel-body nodata-panel" ng-if="mlen == 0" ng-cloak>
                                <div class="nodata-text p-v-elg">
                                    <span class="nodata-media">
                                        <span class="icon">
                                            <i class="ficon-messages f-40"></i>
                                        </span>
                                    </span>
                                    <h5>{{::lang.notify_blank_msg_heading}}</h5>
                                    <p class="text-sm-off no-margin">{{::lang.notify_blank_msg}}</p>
                                </div>
                            </div>
                            <ul class="list-group removed-peopleslist">                                                                                                                                                                           
                                <li class="list-group-item" ng-repeat="thread in notify_thread_list" ng-click="redirectUrl(BaseUrl + 'messages/thread/' + thread.ThreadGUID)">
                                    <figure ng-class="(thread.ThreadImageName == '' && thread.Recipients.length > 1) ? 'group-thumb m-user-thmb' : 'm-user-thmb'">
                                        <span ng-if="thread.ThreadImageName == ''" ng-repeat="recipients in thread.Recipients">
                                            <img class="loadbusinesscard" entityguid="{{thread.SenderUserGUID}}" entitytype="user" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + recipients.ProfilePicture}}"  err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg">
                                        </span>
                                        <span>
                                            <img class="loadbusinesscard" entityguid="{{thread.SenderUserGUID}}" entitytype="user" ng-if="thread.EditableThread == '1'" width="50" ng-src="{{ImageServerPath + 'upload/messages/150x150/' + thread.ThreadImageName}}"  err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg">
                                            <img class="loadbusinesscard" entityguid="{{thread.SenderUserGUID}}" entitytype="user" ng-if="thread.EditableThread == '0'" width="50" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + thread.ThreadImageName}}"  err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg">
                                        </span>
                                    </figure>
                                    <div class="description">
                                        <a target="_self" ng-if="thread.Recipients.length == 1" class="name  loadbusinesscard" entityguid="{{thread.Recipients[0].UserGUID}}" entitytype="user" href="javascript:void(0);" ng-bind="thread.ThreadSubject">
                                        </a>
                                        <a target="_self" ng-if="thread.Recipients.length > 1" class="name" href="javascript:void(0);" ng-bind="thread.ThreadSubject">
                                        </a>
                                        <span ng-bind="getMsgBodyHTML(thread.Body, 1)" class="msg-details"></span>
                                        <span class="hrs" ng-bind="date_format(UTCtoTimeZone(thread.InboxUpdated), 1)"></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="people-suggestion-loader loader absolute message-loader" ng-if="mloader == '1'" style="display:block;">
                            <div class="spinner32"></div>
                        </div>
                        <div class="panel-footer" ng-cloak ng-if="mlen > 0">
                            <a href="{{::BaseUrl}}messages" target="_self" class="view-more">
                                {{::lang.compose}}/
                                {{::lang.view_all}}
                                {{::lang.messages}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('include/bubble-notification') ?>
