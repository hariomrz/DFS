<div class="panel panel-striped" ng-init="get_top_active_users()" ng-cloak>
    <div ng-if="top_active_user.length > 0">
        <div class="panel-heading p-heading">
            <h3 ng-bind="lang.w_top_members"></h3>
        </div>
        <div class="panel-body">
            <ul class="listing-group list-group-v5">
                <li ng-repeat="user in listObj = top_active_user" ng-hide="user.length > 0" class="ng-scope">
                    <div class="list-items-sm">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" ng-href="{{BaseUrl + user.ProfileUrl}}" entitytype="user" entityguid="{{user.UserGUID}}" class="loadbusinesscard">
                                    <img ng-src="{{ImageServerPath + 'upload/profile/220x220/' + user.ProfilePicture}}" err-Name="{{user.FirstName+' '+user.LastName}}" class="img-circle"  >
                                </a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xs">
                                    <a target="_self" entitytype="user" entityguid="{{user.UserGUID}}" class="text-black ng-binding loadbusinesscard" ng-href="{{BaseUrl + user.ProfileUrl}}" ng-bind="user.FirstName+' '+user.LastName"></a></h4>
                                <ul class="list-activites text-xs">
                                    <li ng-if="user.Discussions == 1" ng-bind="user.Discussions + ' Discussion'"> </li>
                                    <li ng-if="user.Discussions > 1" ng-bind="user.Discussions + ' Discussions'"> </li>
                                    <li ng-if="user.TotalFollowers == 1" ng-bind="user.TotalFollowers + ' Follower'"></li>
                                    <li ng-if="user.TotalFollowers > 1" ng-bind="user.TotalFollowers + ' Followers'"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>