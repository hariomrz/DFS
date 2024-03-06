<div class="panel panel-striped" ng-cloak ng-if="category_detail.nonFollowCat.length>0 || category_detail.FollowedCat.length >0">
    <div class="panel-heading p-heading">
        <h3 ng-bind="lang.w_more_in_enterprise"></h3>
    </div>
    <div class="panel-body no-padding">
        <div class="mCustomScrollbar max-ht400 global-scroll">
            <ul class="listing-group list-group-v5 list-striped list-hover" ng-if="category_detail.nonFollowCat.length >0">
                <li ng-repeat="(key,subCat) in category_detail.nonFollowCat">
                    <div class="list-items-sm">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" ng-href="{{BaseURL+subCat.FullURL}}">
                                    <a><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+subCat.ProfilePicture}}" class="img-circle"  ></a>
                                </a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xs"><a target="_self" ng-href="{{BaseURL+subCat.FullURL}}" class="text-black" ng-bind="subCat.Name"></a></h4>
                                <ul class="list-activites text-xs">
                                    <li ng-if="subCat.NoOfDiscussions > 1" ng-bind="subCat.NoOfDiscussions+' Discussions'"></li>
                                    <li ng-if="subCat.NoOfDiscussions <= 1" ng-bind="subCat.NoOfDiscussions+' Discussion'"></li>
                                    <li ng-if="subCat.NoOfMembers > 1" ng-bind="subCat.NoOfMembers+' Members'"> </li>
                                    <li ng-if="subCat.NoOfMembers <= 1" ng-bind="subCat.NoOfMembers+' Member'"> </li>
                                </ul>
                            </div>
                        </div>
                        <div class="list-item-action">
                            <div class="list-item-content">
                                <a target="_self" class="btn btn-xs btn-primary outline" ng-click="follow_category(subCat.ForumCategoryID,subCat.ForumID,1)" ng-if="!subCat.Permissions.IsMember" ng-cloak>
                                    <span class="icons">
                                        <svg width="12px" height="12px" class="svg-icons">
                                            <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnFollowers'}}"></use>
                                        </svg>
                                    </span>
                                    <span class="text" ng-bind="lang.w_follow"></span>
                                </a>

                                <a target="_self" class="btn btn-xs btn-primary outline" ng-cloak="" ng-click="unfollow_category(subCat.ForumCategoryID,forum.ForumID,1);" ng-if="subCat.Permissions.IsMember" ng-cloak>
                                    <span class="icons">
                                        <i class="ficon-check"></i>
                                    </span>
                                    <span class="text" ng-bind="lang.w_unfollow_caps"></span>
                                </a>

                            </div>
                        </div>                                            
                    </div>
                </li>
            </ul>
            <div class="panel-footer" ng-if="category_detail.nonFollowCat.length>0 && category_detail.FollowedCat.length>0">
                <a target="_self" class="text-off"  data-toggle="collapse" data-target="#followingCategories" ng-bind="category_detail.FollowedCatMoreText"></a>
            </div>
            <div ng-class="category_detail.nonFollowCat.length>0?'collapse':''" id="followingCategories" ng-if="category_detail.FollowedCat.length >0">
                <ul class="listing-group list-group-v5 list-striped list-hover">
                    <li ng-repeat="(key,subCat) in category_detail.FollowedCat">
                    <div class="list-items-sm">
                        <div class="list-inner">
                            <figure>
                                <a target="_self" ng-href="{{BaseURL+subCat.FullURL}}">
                                    <!-- <img src="./assets/img/dummy1.jpg"  > -->
                                    <a><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+subCat.ProfilePicture}}" class="img-circle"  ></a>
                                </a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xs"><a target="_self" ng-href="{{BaseURL+subCat.FullURL}}" class="text-black" ng-bind="subCat.Name"></a></h4>
                                <ul class="list-activites text-xs">
                                    <li ng-bind="subCat.NoOfDiscussions+' Discussions'"></li>
                                    <li ng-bind="subCat.NoOfMembers+' Members'"> </li>
                                </ul>
                            </div>
                        </div>
                        <div class="list-item-action">
                            <div class="list-item-content">
                                <a target="_self" class="btn btn-xs btn-primary outline" ng-click="follow_category(subCat.ForumCategoryID,subCat.ForumID,1)" ng-if="!subCat.Permissions.IsMember" ng-cloak>
                                    <span class="icons">
                                        <svg width="12px" height="12px" class="svg-icons">
                                            <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnFollowers'}}"></use>
                                        </svg>
                                    </span>
                                    <span class="text" ng-bind="lang.w_follow"></span>
                                </a>

                                <a target="_self" class="btn btn-xs btn-primary outline" ng-cloak="" ng-click="unfollow_category(subCat.ForumCategoryID,forum.ForumID,1);" ng-if="subCat.Permissions.IsMember" ng-cloak>
                                    <span class="icons">
                                        <i class="ficon-check"></i>
                                    </span>
                                    <span class="text" ng-bind="lang.w_unfollow_caps"></span>
                                </a>

                            </div>
                        </div>                                            
                    </div>
                </li>
                </ul>
            </div>
        </div>
    </div>
</div>