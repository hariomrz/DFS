<div class="panel panel-striped" ng-init="get_category_experts_list()" ng-cloak>
    <div ng-if="ListExpertMembers.length>0">
    <div class="panel-heading p-heading">
        <h3 ng-bind="lang.w_category_experts"></h3>
    </div>
    <div class="panel-body">
        <ul class="listing-group list-group-v5">
            <li ng-repeat="list in listObj = ListExpertMembers" ng-hide="list.length > 0" class="ng-scope">
                <div class="list-items-sm">
                    <div class="list-inner">
                        <figure>
                            <a target="_self" ng-href="{{BaseUrl + list.ProfileUrl}}" entitytype="user" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard"><img ng-src="{{ImageServerPath + 'upload/profile/220x220/' + list.ProfilePicture}}" err-Name="{{list.Name}}" class="img-circle"  ></a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs">
                                <a target="_self" entitytype="user" entityguid="{{list.ModuleEntityGUID}}" class="text-black loadbusinesscard" ng-href="{{BaseUrl + list.ProfileUrl}}" ng-bind="list.Name"></a>
                            </h4>
                            <ul class="list-activites text-xs">
                                <li ng-if="list.Discussions == 1" ng-bind="list.Discussions + ' Discussion'"> </li>
                                <li ng-if="list.Discussions > 1" ng-bind="list.Discussions + ' Discussions'"> </li>
                                <li ng-if="list.TotalFollowers == 1" ng-bind="list.TotalFollowers + ' Follower'"></li>
                                <li ng-if="list.TotalFollowers > 1" ng-bind="list.TotalFollowers + ' Followers'"></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    </div>
</div>