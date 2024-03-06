<?php if($content_view=='forum/wall'){ ?>
<div ng-cloak id="CategoryDetails" ng-init="get_category_details();">
    <div class="col-sm-8">
        <div class="list-items-lg">
            <div class="list-inner">
                <figure>
                    <!-- <a><img src="../assets/img/dummy7.jpg" class="img-circle"  ></a> -->
                     <a><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+category_detail.ProfilePicture}}" class="img-circle"  ></a>
                </figure>
                <div class="list-item-body">
                    <h4 class="list-heading-xlg regular m-b-xs">{{category_detail.Name}}
                        <a target="_self" class="btn btn-xs btn-primary outline btn-xs-padding" ng-click="follow_category(category_detail.ForumCategoryID,category_detail.ForumID,2)" ng-if="!category_detail.Permissions.IsMember && !category_detail.Permissions.IsAdmin" ng-cloak>
                            <span class="icons">
                                <svg width="12px" height="12px" class="svg-icons">
                                    <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnFollowers'}}"></use>
                                </svg>
                            </span>
                            <span class="text" ng-bind="lang.w_follow"></span>
                        </a>

                        <a target="_self" class="btn btn-primary btn-xs btn-text btn-text-lg following btn-xs-padding" ng-cloak="" ng-click="unfollow_category(category_detail.ForumCategoryID,forum.ForumID,2);" ng-if="category_detail.Permissions.IsMember && !category_detail.Permissions.IsAdmin" ng-cloak>
                            <span class="icons">
                                <i class="ficon-check"></i>
                            </span>
                            </span><span class="text"><span ng-bind="lang.following"></span></span>
                        </a>

                    </h4>
                    <p ng-bind="category_detail.Description"></p>
                    <ul class="list-activites text-off">
                        <li class="members-talking" ng-bind-html="get_members_talking(category_detail.Members)"></li>
                        <!-- <li><a>Pankaj K,</a> <a>Rupal J,</a> <a>Rohit S</a> <span class="regular">and</span> <a>Akshay J</a> <span class="regular">are talking</span></li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>                          
    <div class="col-sm-4">
        <ul class="list-count">
            <li>
                <span class="count" ng-bind="category_detail.NoOfMembers"></span>
                <span class="text" ng-if="category_detail.NoOfMembers<=1" ng-bind="lang.member"></span>
                <span class="text" ng-if="category_detail.NoOfMembers>1" ng-bind="lang.members"></span>
            </li>
            <li>
                <span class="count" id="DiscussionCount" ng-bind="category_detail.NoOfDiscussions"></span>
                <span class="text" ng-if="category_detail.NoOfDiscussions<=1" ng-bind="lang.w_discussion"></span>
                <span class="text" ng-if="category_detail.NoOfDiscussions>1" ng-bind="lang.w_discussions"></span>
            </li>
        </ul>
    </div> 
</div> 
<?php } else{ ?>

<div ng-cloak class="col-sm-12" id="CategoryDetails" ng-init="get_category_details();">
    <div class="panel panel-secondary">
        <div class="panel-body pad30">
            <div class="row p-b">
                <div class="col-md-8 col-sm-7 col-xs-10">
                    <div class="list-items-lg">
                        <div class="list-inner">
                            <figure>
                                <a><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+category_detail.ProfilePicture}}" class="img-circle"  ></a>
                            </figure>
                            <div class="list-item-body">
                                <h4 class="list-heading-xxlg ellipsis" ng-bind="category_detail.Name"></h4>
                                <p class="ellipsis semi-bold" ng-bind="category_detail.Description"></p>
                                <ul class="list-activites">
                                    <li>
                                        <span ng-if="category_detail.NoOfMembers==1" ng-bind="category_detail.NoOfMembers+' Member'"></span>
                                        <span ng-if="category_detail.NoOfMembers>1" ng-bind="category_detail.NoOfMembers+' Members'"></span>
                                    </li>
                                    <li>
                                        <span ng-if="category_detail.NoOfDiscussions==1" ng-bind="category_detail.NoOfDiscussions+' Discussion'"></span>
                                        <span ng-if="category_detail.NoOfDiscussions>1" ng-bind="category_detail.NoOfDiscussions+' Discussions'"></span>
                                    </li>
                                    <li class="members-talking" ng-bind-html="get_members_talking(category_detail.Members)"></li>
                                </ul>
                                <!-- <ul class="list-activites">
                                </ul> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-5 col-xs-2" ng-cloak="" ng-if="!category_detail.Permissions.IsSuperAdmin">
                    <ul class="pull-right list-icons">
                        <li>
                            <button ng-cloak="" ng-click="follow_category(category_detail.ForumCategoryID,category_detail.ForumID,2)" ng-if="!category_detail.Permissions.IsMember" class="btn btn-default btn-exceptional" ng-cloak ng-bind="lang.follow">
                            </button>
                            <button ng-cloak="" ng-click="unfollow_category(category_detail.ForumCategoryID,forum.ForumID,2);" class="btn btn-primary btn-exceptional btn-text btn-text-lg following" ng-if="category_detail.Permissions.IsMember" ng-cloak>
                                <span class="icons">
                                    <i class="ficon-check"></i>
                                </span><span class="text"><span ng-bind="lang.following"></span></span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="collapse in">
                <ul class="listing-group list-group-hover list-group-bordered list-group-30 p-t" ng-if="category_detail.SubCategory.length>0">
                    <li ng-cloak ng-repeat="sub_cat in category_detail.SubCategory">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="list-items-md border-vertical">
                                    <div class="list-inner">
                                        <figure>
                                            <a target="_self" ng-href="{{BaseURL+sub_cat.FullURL}}"><img err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+sub_cat.ProfilePicture}}" class="img-circle"  ></a>
                                        </figure>
                                        <div class="list-item-body">
                                            <ul class="pull-right list-icons">
                                                <li ng-if="!sub_cat.Permissions.IsAdmin" class="dropdown">
                                                    <button ng-click="follow_category(sub_cat.ForumCategoryID,category_detail.ForumID,1)" ng-if="!sub_cat.Permissions.IsMember" class="btn btn-default btn-xs" ng-bind="lang.follow">
                                                    </button>
                                                    <button ng-click="unfollow_category(sub_cat.ForumCategoryID,forum.ForumID,1);" class="btn btn-brand btn-xs btn-text btn-text-lg following" ng-if="sub_cat.Permissions.IsMember" ng-cloak>
                                                        <span class="icons">
                                                            <i class="ficon-check"></i>
                                                        </span><span class="text"><span ng-bind="lang.following"></span></span>
                                                    </button>
                                                </li>
                                                <!-- ng-if="category_data.Permissions.IsAdmin" -->
                                                <li ng-if="sub_cat.Permissions.IsAdmin" tootip data-toggle="tooltip" data-placement="top" title="Manage Subcategory" class="dropdown">
                                                    <a target="_self" class="icon" data-toggle="dropdown">
                                                        <svg height="20px" width="20px" class="svg-icons">
                                                            <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnSettings'}}"></use>
                                                        </svg>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a target="_self" href="{{BaseUrl+'community/members_settings/'+sub_cat.ForumID+'/'+sub_cat.ForumCategoryID}}" ng-bind="lang.members_settings">
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a target="_self" data-toggle="modal" data-target="#addSubCategory" ng-click="get_forum_category_list(category_detail.ForumID,category_detail.ForumCategoryID,category_detail); set_current_forum_id(category_detail.ForumID); prefill_subcat(category_detail,sub_cat.ForumCategoryID)" ng-bind="lang.edit_details">
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a target="_self" ng-click="delete_category(sub_cat.ForumCategoryID,1)" ng-bind="lang.delete_category">
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                            <a target="_self" ng-href="{{BaseURL+sub_cat.FullURL}}" class="list-heading-md text-black ellipsis" ng-bind="sub_cat.Name"></a>
                                            <p class="ellipsis" ng-bind="sub_cat.Description"></p>
                                            <ul class="list-activites">
                                                <li>
                                                    <span ng-if="sub_cat.NoOfMembers==1" ng-bind="sub_cat.NoOfMembers+' Member'"></span>
                                                    <span ng-if="sub_cat.NoOfMembers>1" ng-bind="sub_cat.NoOfMembers+' Members'"></span>
                                                </li>
                                                <li>
                                                    <span ng-if="sub_cat.NoOfDiscussions==1" ng-bind="sub_cat.NoOfDiscussions+' Discussion'"></span>
                                                    <span ng-if="sub_cat.NoOfDiscussions>1" ng-bind="sub_cat.NoOfDiscussions+' Discussions'"></span>
                                                </li>
                                            </ul>
                                            <ul class="list-activites text-muted">
                                                <li ng-bind-html="get_members_talking(sub_cat.Members)"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="subcategory-list list-group-close" ng-if="sub_cat.FeaturedPost.length>0">
                                    <div class="list-items-sm">
                                        <div class="list-inner">  
                                            <figure ng-click="redirectToBaseLink(sub_cat.FeaturedPost[0].ActivityURL)" class="text-base">
                                                <a target="_self" class="loadbusinesscard" entitytype="user" entityguid="{{sub_cat.FeaturedPost[0].UserGUID}}" ng-href="{{BaseUrl+sub_cat.FeaturedPost[0].ProfileURL}}">
                                                <img err-Name="{{sub_cat.FeaturedPost[0].FirstName+' '+sub_cat.FeaturedPost[0].LastName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+sub_cat.FeaturedPost[0].ProfilePicture}}" class="img-circle"  >
                                                </a>
                                            </figure>
                                            <div class="list-item-body">     
                                                <div class="text-base">
                                                    <a target="_self" ng-click="redirectToBaseLink(sub_cat.FeaturedPost[0].ActivityURL)" ng-bind-html="textToLink(sub_cat.FeaturedPost[0].PostContent,1)" class="text-black"></a>
                                                </div>
                                                <p class="text-sm"><span class="text-off" ng-bind="lang.by+' '"></span><a target="_self" entitytype="user" entityguid="{{sub_cat.FeaturedPost[0].UserGUID}}" ng-href="{{BaseUrl+sub_cat.FeaturedPost[0].ProfileURL}}" class="loadbusinesscard semi-bold text-black" ng-bind-html="sub_cat.FeaturedPost[0].FirstName+' '+sub_cat.FeaturedPost[0].LastName"></a></p>
                                                <div class="feed-post-activity">
                                                    <ul class="feed-like-nav">
                                                        <li tooltip data-placement="top" data-original-title="Like"  class="iconlike " ng-class="{'active' :sub_cat.FeaturedPost[0].IsLike==1}">
                                                            <svg height="16px" width="16px" class="svg-icons">
                                                                <use xlink:href="{{SiteURL+'assets/img/sprite.svg#iconLike'}}"></use>
                                                            </svg>
                                                        </li>
                                                        <li class="view-count" ng-if="category_data.FeaturedPost[0].NoOfLikes>0" ng-bind="sub_cat.FeaturedPost[0].NoOfLikes">
                                                        </li>
                                                        <li tooltip data-placement="top" data-original-title="Comment">
                                                        <svg height="18px" width="18px" class="svg-icon">
                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnComment'}}"></use>
                                                        </svg>
                                                        </li>
                                                        <li class="view-count" ng-if="category_data.FeaturedPost[0].NoOfComments>0" ng-bind="sub_cat.FeaturedPost[0].NoOfComments"> </li>
                                                    </ul>                                
                                                </div>
                                            </div>
                                        </div>
                                        <a target="_self" ng-click="get_new_featured_post(forum.ForumID,sub_cat.ForumCategoryID,sub_cat.FeaturedPageNo);" class="list-close">
                                            <span class="icon" tootip data-toggle="tooltip" data-placement="top" title="Remove">
                                               <svg height="16px" width="16px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#closeIcon'}}"></use>
                                               </svg>
                                           </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php } ?>