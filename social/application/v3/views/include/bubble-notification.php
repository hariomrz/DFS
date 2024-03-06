<!-- Bubble Notification  -->
<div id="bubbleNotify" ng-cloak ng-class="(bubble_notification_list.length > 4) ? 'more-notification' : '';" class="bubble-notification visible-lg">
    <div class="bubble-header" data-toggle="collapse" href="#bubbleScroll" aria-expanded="true" aria-controls="bubbleScroll" ng-cloak ng-show="bubble_notification_list.length > 4" >
        <i class="icon-n-bell"></i> {{::lang.notify_group_notification}} <span ng-bind="'(' + bubble_notification_list.length + ')'"></span>
        <i class="icon-n-toggle-arrow"></i>
    </div>
    <div class="collapse in" id="bubbleScroll" aria-expanded="true">
        <div class="bubble-scroll mCustomScrollbar ">
            <ul class="notification-list">
                <li ng-cloak ng-if="bubble_notification_list.length > 0" ng-repeat="notify in bubble_notification_list" ng-click="redirectUrl(BaseUrl + notify.Link, notify.IsLink); mediaRightcommentscrl();">
                    <i class="{{notify.Class}}"></i>
                    <figure class="thumb50">
                        <a ng-if="notify.Members.length == 0">
                            <img err-Name="{{notify.ProfileName}}" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + notify.ProfilePicture}}"  >
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
                            <span class="font-medium" ng-bind-html="to_trusted(notify.NotificationText, notify.NotificationTypeID);"></span>
                            <span class="post-msz" ng-bind-html="html_parse(notify.Summary, 1)"></span>
                            <span class="hrs" ng-bind="date_format((notify.CreatedDate))"></span>
                        </div>
                    </div>
                    <i ng-click="removeBubbleNotification(notify.NotificationGUID)" class="icon-n-close-w">&nbsp;</i>
                </li>
            </ul>            
        </div>
        <a ng-click="removeBubbleNotification('')" ng-cloak ng-show="(bubble_notification_list.length > 5) ? 'more-notification' : '';" class="removeAll">{{lang.notify_remove_all}}</a>
    </div>
</div>
<!-- //Bubble Notification  -->