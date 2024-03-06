<div class="panel m-b" ng-init="get_most_active_users();">
    <div class="panel-heading p-heading">
        <h3 class="bold" ng-bind="lang.MostActiveUsers_MostActiveUsers"></h3>
    </div>
    <div class="panel-body no-padding">
        <div class="bx-slider-fluid" ng-class="(active_users.length=='1') ? 'single-slide' : '' ;" ng-cloak>
            <ul ng-cloak class="listing-group vertical" data-uix-bxslider="mode:'horizontal', pager:false, minSlides:2, maxSlides:7, slideWidth: 160, slideMargin:13, infiniteLoop: false, hideControlOnEnd: true" >
                <li ng-repeat="user in active_users" ng-cloak data-notify-when-repeat-finished>
                    <div class="list-items-md">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" ng-href="{{BaseUrl+user.ProfileUrl}}"><img ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" err-Name="{{user.FirstName+' '+user.LastName}}" class="img-circle"  ></a>
                            </figure>
                            <div class="list-item-body">
                                <a target="_self" class="list-heading-base bold" ng-href="{{BaseUrl+user.ProfileUrl}}" ng-bind="user.FirstName+' '+user.LastName"></a>
                                <div class="text-off semi-bold" ng-if="user.MutualFriendCount==0" ng-bind="lang.w_no_mutual_friend"></div>
                                <div class="text-off semi-bold" ng-if="user.MutualFriendCount==1" ng-bind="user.MutualFriendCount+' Mutual Friend'"></div>
                                <div class="text-off semi-bold" ng-if="user.MutualFriendCount>1" ng-bind="user.MutualFriendCount+' Mutual Friends'"></div>
                                <div class="text-off semi-bold" ng-if="user.Discussions==1" ng-bind="user.Discussions+' Discussion'"></div>
                                <div class="text-off semi-bold" ng-if="user.Discussions>1" ng-bind="user.Discussions+' Discussions'"></div>
                            </div>
                        </div>
                        <div class="listing-footer">
                            <button ng-click="toggle_follow(user.UserGUID)" class="btn btn-primary btn-xs" ng-if="user.FollowStatus=='1'" ng-cloak>
                                <span class="icons">
                                 <i class="ficon-check"></i>
                            </span>{{::lang.following}}
                            </button>
                            <a target="_self" class="btn btn-default btn-xs" ng-click="toggle_follow(user.UserGUID)" ng-if="user.FollowStatus=='2'" ng-bind="lang.follow"></a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>        
    </div>
</div>