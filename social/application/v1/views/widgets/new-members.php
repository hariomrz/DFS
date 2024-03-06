<div data-ng-init="getNewMebers(5,0,0); " ng-cloak ng-show="newMember.length>0 && IsReminder==0" class="hidden-xs panel panel-default">
    <div class="panel-heading p-heading">
        <h3>NEW MEMBER</h3>
    </div>
    <div class="panel-body">
        <div style="display:none;" class="new-member-loader">
            <div class="spinner32"></div>
        </div>
        <ul class="list-group removed-peopleslist middle-listings">
            <li ng-repeat="Member in newMember" repeat-done="triggerTooltip()" class="list-group-item">
                <figure>
                    <a target="_self" entitytype="user" entityguid="{{Member.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{Member.ProfileURL}}" target="_self"> 
                        <img ng-if="Member.ProfilePicture!==''" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{Member.ProfilePicture}}" class="img-circle"   err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  /> 
                    </a>
                </figure>
                <div class="description">
                    <a target="_self" entitytype="user" entityguid="{{Member.UserGUID}}" class="a-link name loadbusinesscard" ng-href="<?php echo site_url() ?>{{Member.ProfileURL}}" ng-bind="Member.FirstName+' '+Member.LastName" target="_self"></a>
                    <div ng-cloak class="location ellipsis" style="width:155px;" ng-if="Member.CityName !== '' && Member.CountryName !== '' ">
                        <i>
                            <svg height="16px" width="16px" class="svg-icons">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                            </svg>
                        </i>
                        <span ng-bind="Member.CityName+', '+Member.CountryName"></span>
                    </div>
                    <div ng-cloak class="location ellipsis" style="width:155px;" ng-if="Member.CityName !== '' && Member.CountryName == '' ">
                        <i>
                            <svg height="16px" width="16px" class="svg-icons">
                                 <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                            </svg>
                        </i>
                        <span ng-bind="Member.CityName"></span>
                    </div>
                    <a target="_self" ng-cloak class="request-status" ng-click="getMutualFriends(Member.UserGUID);" ng-if="Member.MutualFriends>1" ng-bind="Member.MutualFriends+' mutual friends'"></a>
                    <a target="_self" ng-cloak class="request-status" ng-click="getMutualFriends(Member.UserGUID);" ng-if="Member.MutualFriends==1" ng-bind="Member.MutualFriends+' mutual friend'"></a>
                    <div ng-cloak ng-if="Member.ShowFollowBtn=='1'" class="button-wrap-sm">
                        <button ng-click="toggleFollowUser(Member.UserGUID)" ng-bind="Member.FollowStatus" class="btn btn-default btn-xs"></button>
                    </div>
                </div>
                <ul class="subnav-btn positon-ab">
                    <li ng-cloak ng-if="Member.ShowFriendsBtn=='1' && Member.FriendStatus=='4'" ng-click="sendRequest(Member.UserGUID,'peopleyoumayknow')"  data-toggle="tooltip" data-original-title="Add Friend"><i class="icon-n-memeber"></i></li>
                    <li ng-cloak ng-if="Member.ShowFriendsBtn=='1' && Member.FriendStatus=='2'" ng-click="rejectRequest(Member.UserGUID,'peopleyoumayknow')"  data-toggle="tooltip" data-original-title="Request sent"><i class="icon-n-rq-sent"></i></li>
                </ul>
                <div class="m-t-10" ng-bind="Member.Introduction"></div>
                <a target="_self" class="remove"><i class="icon-remove" ng-click="nextMember()"></i></a>
            </li>
        </ul>
    </div>
</div>
