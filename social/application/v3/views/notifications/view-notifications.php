<!--Container-->
<div class="container wrapper" ng-controller="NotificationCtrl" ng-init="getAllNotifications()">
    <div class="row">
        <aside class="col-sm-12 col-xs-12" ng-cloak>
            <div class="notification-wrapper panel">
                <div class="notify-header">
                    <h4 class="col-sm-6">{{::lang.notifications}}</h4>
                    <ul class="sublisting">
                        <li ng-cloak ng-if="TotalUnreadAll > 0"><a ng-click="markAllAsRead()">{{::lang.notify_mark_all_as_read}}</a></li>
                        <li ng-cloak ng-show="TotalUnreadAll > 0 && show_all_notifys == 0"><a ng-click="get_unread_notification('all')">{{::lang.notify_show_unread}} <span ng-bind="'(' + TotalUnreadAll + ')'"></span></a></li>
                        <li ng-cloak ng-show="show_all_notifys == 1"><a ng-click="show_all_notification(1);">{{::lang.notify_show_all}}</a></li>
                        <li><a href="{{::BaseUrl}}notification/settings">{{::lang.settings}}</a></li>
                    </ul>
                </div>
                <div class="notify-inner">
                    <ul class="notification-list">
                        <li class="read text-center" ng-if="(AllNotifications.length === 0)">
                            {{::lang.notify_blank_msg}}
                        </li>
                        <li ng-repeat="notifi in AllNotifications track by $index" repeat-done="repeatDoneBCard();" 
                            ng-class="{'read': (notifi.StatusID == '17') ,'unread': (notifi.StatusID !== '17') , 'notify-date' : (notifi.DateOnly == '1')}" 
                            ng-show="isObj(notifi)"  
                            repeat-done="notificationRepeatDone();"> 
                            <span ng-if="notifi.DateOnly" ng-bind="notifi.NewDate"> </span> 
                            <span ng-if="!notifi.DateOnly">
                                <i class="{{notifi.Class}}"></i> 
                                <a ng-init="prevent_event();"
                                   ng-href="{{notifi.IsLink==1 ? notifi.Link : ''}}"
                                   ng-click="notifi.IsLink == 0 ? readNotification(notifi) : ''" target="_self"> 
                                    <figure class="thumb50 loadbusinesscard" entitytype="user" entityguid="{{notifi.UserGUID}}"> 
                                        <img err-SRC="{{AssetBaseUrl}}img/profiles/default-148.png" ng-if="notifi.ProfilePicture !== '' && notifi.ProfilePicture !== 'user_default.jpg'" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + notifi.ProfilePicture}}"  >
                                        <img err-Name="{{notifi.P1[0].FirstName + ' ' + notifi.P1[0].LastName}}" ng-if="(notifi.ProfilePicture == '' || notifi.ProfilePicture == 'user_default.jpg')"   ng-src="{{AssetBaseUrl}}img/profiles/">
                                    </figure>
                                    <div class="description">
                                        <div class="post-thumb" ng-if="notifi.Album.length"> 
                                            <img  ng-src="{{ImageServerPath}}upload/wall/220x220/{{getThumbImage(notifi.Album[0].ImageName)}}"> 
                                        </div>
                                        <div class="list-desc"> 
                                            <span ng-bind="notifi.UserName" class="user-name loadbusinesscard" entitytype="user" entityguid="{{notifi.UserGUID}}"></span> 
                                            <span class="font-medium default-color" ng-bind-html="to_trusted(notifi.NotificationText, notifi.NotificationTypeID);"></span> 
                                            <span ng-if="notifi.Summary !== ''" ng-cloak> 
                                                <span class="post-msz" ng-bind-html="html_parse(notifi.Summary)"></span>
                                            </span> 
                                            <span class="hrs" ng-bind="date_format((notifi.CreatedDate))"></span>
                                            <ul class="subnav-btn accept-{{notifi.UserGUID}}" ng-if="notifi.ShowAcceptDeny == '1'" ng-cloak>
                                                <li title="{{::lang.notify_accept}}" ng-click="acceptRequestNote(notifi, $index)" data-toggle="tooltip">
                                                    <i class="icon-n-accept"></i>
                                                </li>
                                                <li title="{{::lang.notify_deny}}" ng-click="denyRequestNote(notifi, $index)" data-toggle="tooltip">
                                                    <i class="icon-n-deny"></i>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </a>
                                <i class="icon-n-read" ng-if="notifi.StatusID != '17'"  data-toggle="tooltip" data-original-title="{{::lang.notify_mark_as_read}}" ng-click="readNotification(notifi, $index)" ng-cloak>&nbsp;</i> 
                            </span> 
                        </li>
                    </ul>
                </div>
            </div>
            <div class="loader" ng-if="all_scroll_busy"> &nbsp; </div>
        </aside>
    </div>
</div>
<!--//Container-->

<input type="hidden" id="NotificationPageNo" value="1" />
<script type="text/javascript">
    window.onload = function (e) {
        $('#NotificationPageNo').val(1);
    };
</script>
