<div ng-cloak id="sendReq" class="user-profile-completion" ng-show="(profileUser.FriendStatus == '3') || (profileUser.FriendStatus == '4' && profileUser.ShowFriendsBtn == '1')">
    <h3 ng-if="profileUser.FriendStatus == '3'">Respond to {{FirstName}}’s friend request
            <i ng-click="profileUser.ShowFriendsBtn = 0;" onclick="$('#sendReq').hide();">
                <svg height="10px" width="10px" class="svg-icons">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#closeIcn'}}"></use>
                </svg>
            </i>
        </h3>
    <div ng-if="profileUser.FriendStatus == '3'" class="completion-content">
        <!-- <div><small ng-bind="profileUser.RequestDate"></small></div> -->
        <div class="button-footer m-t-10">
            <button class="btn btn-default btn-sm pull-right" ng-click="denyRequest(profileUser.UserGUID)">Deny</button>
            <button class="btn btn-primary btn-sm pull-right" ng-click="acceptRequest(profileUser.UserGUID)">Accept</button>
        </div>
    </div>
    <h3 ng-if="profileUser.FriendStatus == '4' && profileUser.ShowFriendsBtn == '1'">You know {{FirstName}}? or wanna be friends? 
            <i onclick="$('#sendReq').hide();">
                <svg height="10px" width="10px" class="svg-icons">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#closeIcn'}}"></use>
                </svg>
            </i>
        </h3>
    <div ng-if="profileUser.FriendStatus == '4' && profileUser.ShowFriendsBtn == '1'" class="completion-content">
        <!-- <div><small ng-bind="profileUser.RequestDate"></small></div> -->
        <div class="button-footer m-t-10">
            <button class="btn btn-default btn-sm pull-right" ng-click="profileUser.ShowFriendsBtn = 0;" onclick="$('#sendReq').hide();">No</button>
            <button class="btn btn-primary btn-sm pull-right" ng-click="profileUser.ShowFriendsBtn = 0;sendRequest(profileUser.UserGUID)">Yes</button>
        </div>
    </div>
</div>