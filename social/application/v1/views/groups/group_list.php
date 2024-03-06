<div data-ng-controller="GroupPageCtrl" id="GroupPageCtrl" ng-init="get_group_categories();">
    <?php
        if(!$this->session->userdata('LoginSessionKey'))
        {
            $this->load->view('include/non-loggedin');
        } else {
    ?>
        <div class="secondary-fixed-nav">
            <div class="secondary-nav">

                <div class="container">
                    <div class="row nav-row">
                        <div class="col-sm-12 main-filter-nav">
                            <nav class="navbar navbar-default navbar-static">
                                <div class="navbar-header visible-xs">
                                    <button class="btn btn-sm btn-default" type="button" data-toggle="collapse" data-target="#filterNav">
                                        <span class="icon"><i class="ficon-filter"></i></span>
                                    </button>
                                </div>
                                <div class="collapse navbar-collapse" id="filterNav">
                                    <ul class="nav navbar-nav filter-nav">
                                        <li ng-cloak ng-show="LoginSessionKey" class="dropdown" ng-init="listing_display_type='All My Groups';sortbyname='<?php echo lang('activity_date'); ?>'">
                                            <a class="" data-toggle="dropdown" role="button">
                                                <?php echo lang('my_groups'); ?> <span ng-bind="listing_display_type"></span></a>
                                            <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height">
                                                <li ng-repeat="(key, value) in [{Name:'All Public Groups',Key:'AllPublicGroups'},{Name:'<?php echo lang('all_my_groups'); ?>',Key:'MyGroupAndJoined'},{Name:'<?php echo lang('groups_i_manage'); ?>',Key:'Manage'},{Name:'<?php echo lang('groups_i_joined'); ?>',Key:'Join'},{Name:'Suggested',Key:'Suggested'}]" ng-cloak>
                                                    <div class="radio" ng-click="my_groups(value.Key,value.Name,1);">
                                                        <input ng-init="$index==1?(role=value.Key):''" data-ng-model="role" id="{{myGroups+key}}" type="radio" name="value.Key" value="{{value.Key}}">
                                                        <label for="{{myGroups+key}}">{{value.Name}}</label>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown">
                                            <a class="" data-toggle="dropdown" role="button">
                                                <?php echo lang('categories'); ?>
                                                <span ng-cloak ng-if="interest_list_checked.length==0"><?php echo lang('all').' '.lang('categories'); ?></span><span ng-cloak ng-if="interest_list_checked.length>0" ng-bind="interest_list_checked[0].Name"></span></a>
                                            <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                                <li class="mCustomScrollbar filter-height no-padding">
                                                    <ul class="p-l-sm">
                                                        <li>
                                                            <label class="checkbox">
                                                                <input type="checkbox" ng-checked="interest_list_checked.length==0" ng-click="emptyArr('interest_list_checked','interest_list'); my_groups('','',true);" value="0">
                                                                <span class="label">All Categories</span>
                                                            </label>
                                                        </li>
                                                        <li ng-repeat="interest in interest_list_checked">
                                                            <label class="checkbox">
                                                                <input ng-click="remove_from_interest(interest,interest.CategoryID); my_groups('','',true);" ng-checked="interest.IsChecked" class="interest-check" type="checkbox" ng-value="interest.CategoryID">
                                                                <span class="label" ng-bind="interest.Name"></span>
                                                            </label>
                                                            <ul class="sub-categories">
                                                                <li ng-repeat="subinterest in interest.Subcategory">
                                                                    <label class="checkbox">
                                                                        <input ng-checked="subinterest.IsChecked" ng-click="my_groups('','',true);" type="checkbox" ng-value="subinterest.CategoryID" class="interest-check">
                                                                        <span class="label" ng-bind="subinterest.Name"></span>
                                                                    </label>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                        <li ng-repeat="interest in interest_list|orderBy:'Name'">
                                                            <label class="checkbox">
                                                                <input ng-click="add_to_interest(interest,interest.CategoryID); my_groups('','',true);" class="interest-check" type="checkbox" ng-value="interest.CategoryID">
                                                                <span class="label" ng-bind="interest.Name"></span>
                                                            </label>
                                                            <ul class="sub-categories">
                                                                <li ng-repeat="subinterest in interest.Subcategory">
                                                                    <label class="checkbox">
                                                                        <input type="checkbox" ng-click="add_to_interest(interest,subinterest.CategoryID); my_groups('','',true);" ng-value="subinterest.CategoryID" class="interest-check">
                                                                        <span class="label" ng-bind="subinterest.Name"></span>
                                                                    </label>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li ng-cloak ng-show="LoginSessionKey" class="dropdown">
                                            <a class="" data-toggle="dropdown" role="button">
                                                <?php echo lang('created_by');?>
                                                <span ng-cloak ng-if="CreatedByLookedMore.length==0">Anyone</span>
                                                <span ng-cloak ng-if="CreatedByLookedMore.length>0" ng-bind="CreatedByLookedMore[0].Name">Anyone</span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                                                <li>
                                                    <tags-input ng-model="CreatedByLookedMore" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="updateGroupOwners();" on-tag-added="updateGroupOwners();">
                                                        <auto-complete source="loadSearchGroupUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10"></auto-complete>
                                                    </tags-input>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown">
                                            <a class="" data-toggle="dropdown" role="button"> Sort By <span ng-bind="sortbyname"></span></a>
                                            <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                                                <li onclick="addActiveClass(this);" ng-class="$index==0?('active'):''" ng-repeat="(key, value) in [{Name:'<?php echo lang('activity_date'); ?>',Key:'LastActivity'},{Name:'<?php echo lang('name'); ?>',Key:'GroupName'},{Name:'<?php echo lang('popularity'); ?>',Key:'Popularity'}]">
                                                    <a ng-init="$index==0?(role=value.Key):''" ng-click="SearchGroup(value.Key,value.Name);">
                                                        <span class="label">{{value.Name}}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        }
    ?>
    <!--Container-->
    <?php $UserGUID =  get_guid_by_id($this->session->userdata('UserID'),3); ?>
    <div class="container wrapper" id="WallPostCtrl" ng-controller="WallPostCtrl">
        <div class="row" ng-cloak>
            <!-- Left Wall-->
            <aside class="col-md-9 col-sm-9 col-xs-12">
                <div class="panel panel-default page-panel fadeInDown" ng-init="my_groups('MyGroupAndJoined','All My Groups');" ng-cloak>
                    <div class="panel-block" style="display:none" id="ShowDataMyGroup">
                        <div class="panel-body  group-listing">                            
                            <!-- List Start -->
                            <ul class="listing-group list-group-hover thumb-68 list-group-bordered">
                                <li ng-repeat="list in MyGrouplist" ng-cloak>
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <div class="list-items-md border-vertical">
                                                <div class="list-inner">
                                                    <figure ng-if="list.Type=='FORMAL'">
                                                        <a entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="{{site_url}}group/{{list.GroupID}}">
                                                            <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle" alt="" title="">
                                                        </a>
                                                    </figure>
                                                    <figure ng-if="list.Type=='INFORMAL'" ng-class="(list.MemberCount>2) ? 'group-thumb' : 'group-thumb-two' ;">
                                                        <a entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="{{site_url}}group/{{list.GroupID}}">
                                                            <img ng-if="list.Type=='INFORMAL' && list.ProfilePicture!='' && list.ProfilePicture!='group-no-img.jpg'" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle" alt="" title="">
                                                            <span ng-repeat="recipients in list.EntityMembers" class="ng-scope">
                                                            <img alt="" err-src="{{SiteUrl}}assets/img/profiles/user_default.jpg" ng-src="{{ImageServerPath}}upload/profile/220x220/{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index<=2" class="ng-scope">
                                                          </span>
                                                        </a>
                                                    </figure>
                                                    <div class="list-item-body">
                                                        <ul class="pull-right list-icons">
                                                            <li ng-if="!LoginSessionKey">
                                                                <div class="btn-group">
                                                                    <span>
                                                                        <button ng-click="joinPublicGroup(list.GroupGUID, 'list')" aria-expanded="false" class="btn btn-xs btn-default" type="button"> <span class="text">Join</span> </button>
                                                                    </span>
                                                                </div>
                                                            </li>
                                                            <li ng-if="LoginSessionKey">
                                                                <!-- Join Button Start -->
                                                                <div class="btn-group m-l-10">
                                                                    <div class="btn-group" ng-cloak ng-if="list.Permission.IsActiveMember == 1 && list.Permission.DirectGroupMember == 1 ">
                                                                        <span>
                                                                            <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-xs  btn-default dropdown-toggle" type="button"> <span class="text"><?php echo lang('joined'); ?></span> <i class="caret"></i> </button>
                                                                        <ul role="menu" class="dropdown-menu">
                                                                            <li>
                                                                                <a href="javascript:void(0);" ng-click='groupDropOutAction(list.GroupGUID, "list")'>
                                                                                    <?php echo lang('leave_group'); ?>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                        </span>
                                                                    </div>
                                                                    <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 ">
                                                                        <span>
                                                                                                                    <button aria-expanded="false" class="btn btn-xs btn-default" type="button" ng-click="joinPublicGroup(list.GroupGUID, 'OtherUserProfile');"> <span class="text"><?php echo lang('join_group'); ?></span> </button>
                                                                        </span>
                                                                    </div>
                                                                    <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited == false && list.Permission.IsActiveMember == false && list.IsPublic ==0 ">
                                                                        <span ng-if="list.IsInviteSent">
                                                                                                                <button aria-expanded="false" class="btn btn-default" type="button" ng-click="cancelInvite(list.GroupGUID,'OtherUserProfile');"> <span class="text">Cancel Request</span> </button>
                                                                        </span>
                                                                        <span ng-if="!list.IsInviteSent">
                                                                                                                <button aria-expanded="false" class="btn btn-default" type="button" ng-click="requestInvite(list.GroupGUID,'OtherUserProfile');"> <span class="text">Request Invite</span> </button>
                                                                        </span>
                                                                    </div>
                                                                    <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited == 1  ">
                                                                        <span>
                                                                                                                    <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"> <span class="text"><?php echo lang('accept') ?></span> <i class="caret"></i> </button>
                                                                        <ul role="menu" class="dropdown-menu">
                                                                            <li>
                                                                                <a ng-click="groupAcceptDenyRequest(list.GroupGUID, '2', 'OtherUserProfile')">
                                                                                    <?php echo lang('accept') ?>
                                                                                </a>
                                                                            </li>
                                                                            <li>
                                                                                <a ng-click="groupAcceptDenyRequest(list.GroupGUID, '13', 'OtherUserProfile')">
                                                                                    <?php echo lang('deny') ?>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <!-- Join Button Ends -->
                                                            </li>
                                                        </ul>
                                                        <a entitytype="group" ng-if="list.Type=='FORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{site_url}}group/{{list.GroupID}}" ng-bind="list.GroupName"></a>
                                                        <a entitytype="group" ng-if="list.Type=='INFORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{site_url}}group/{{list.GroupID}}">
                                                            <span ng-repeat="Member in list.EntityMembers"><span ng-bind="Member.FirstName" ng-if="$index<=2"></span><span ng-if="$index<2 && list.EntityMembers.length>=3">,</span><span ng-if="$index<(list.EntityMembers.length-1) && list.EntityMembers.length<3">,</span> </span>
                                                            <span ng-if="list.EntityMembers.length>3">and {{list.EntityMembers.length-3}} others</span>
                                                        </a>
                                                        <span class="icon group-type" ng-if="key === 0">
                                                      <i class="ficon-globe"></i>
                                                    </span>
                                                        <span class="icon group-type" ng-if="key ===1">
                                                      <i class="ficon-close f-lg"></i>
                                                    </span>
                                                        <span class="icon group-type" ng-if="key ===2 || key >=2">
                                                      <i class="ficon-secrets f-lg"></i>
                                                    </span>
                                                        <ul class="activity-nav cat-sub-nav">
                                                            <li>
                                                                <span class="cat-name" ng-bind="list.Category.Name"></span>
                                                            </li>
                                                            <li>
                                                                <span class="icon group-activity-lavel heigh" ng-if="key <=2" tooltip data-placement="top" title="Activity Level : High">
                                                                  <svg width="13px" height="13px" class="svg-icons no-hover">
                                                                    <use xlink:href="./assets/img/sprite.svg#iconGrouppactivity"></use>
                                                                  </svg>
                                                                </span>
                                                                <span class="icon group-activity-lavel moderate" ng-if="key >=3" tooltip data-placement="top" title="Activity Level : Moderate">
                                                                  <svg width="13px" height="13px" class="svg-icons no-hover">
                                                                    <use xlink:href="./assets/img/sprite.svg#iconGrouppactivity"></use>
                                                                  </svg>
                                                                </span>
                                                            </li>
                                                        </ul>
                                                        <div>{{list.GroupDescription|limitTo:DescriptionLimit}} <span ng-if="list.GroupDescription.length > DescriptionLimit"> ...</span></div>
                                                        <ul class="list-activites">
                                                            <li>
                                                                <span ng-if="list.Members.length>0" ng-repeat="Member in list.Members|limitTo:2">
                                                                    <a ng-href="{{BaseUrl+Member.ProfileURL}}" ng-bind="Member.FirstName+' '+Member.LastName"></a>
                                                                    <span ng-if="($index+1)<list.Members.length && !$last">, </span>
                                                                </span>
                                                                <span ng-if="(list.Members.length)>2"><span>and other</span> {{list.MemberCount-2}} <span ng-if='(list.MemberCount-2)==1'>Member</span> <span ng-if='(list.MemberCount-2)>1'>Members</span></span>
                                                            </li>
                                                            <li><span ng-bind="list.TotalPosts"></span> <span ng-if="list.TotalPosts<2">Post</span><span ng-if="list.TotalPosts>1">Posts</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="subcategory-list list-group-close" ng-if="list.FeaturedPost.length>0">
                                                <div class="list-items-sm">
                                                    <div class="list-inner">
                                                        <figure ng-click="redirectToBaseLink(list.FeaturedPost[0].ActivityURL)" class="text-base">
                                                            <a class="loadbusinesscard" entitytype="user" entityguid="{{list.FeaturedPost[0].UserGUID}}" ng-href="{{BaseUrl+list.FeaturedPost[0].ProfileURL}}">
                                                                <img err-Name="{{list.FeaturedPost[0].FirstName+' '+list.FeaturedPost[0].LastName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+list.FeaturedPost[0].ProfilePicture}}" class="img-circle" alt="" title="">
                                                            </a>
                                                        </figure>
                                                        <div class="list-item-body">
                                                            <div class="text-base">
                                                                <a ng-click="redirectToBaseLink(list.FeaturedPost[0].ActivityURL)" ng-bind-html="textToLink(list.FeaturedPost[0].PostContent,1)" class="text-black"></a>
                                                            </div>
                                                            <p class="text-sm"><span class="text-off">By </span>
                                                                <a entitytype="user" entityguid="{{list.FeaturedPost[0].UserGUID}}" ng-href="{{BaseUrl+list.FeaturedPost[0].ProfileURL}}" class="loadbusinesscard semi-bold text-black" ng-bind-html="list.FeaturedPost[0].FirstName+' '+list.FeaturedPost[0].LastName"></a>
                                                            </p>
                                                            <div class="feed-post-activity">
                                                                <ul class="feed-like-nav" ng-if="list.FeaturedPost[0].NoOfComments>0 || list.FeaturedPost[0].NoOfLikes>0">
                                                                    <li ng-if="list.FeaturedPost[0].NoOfLikes>0" tooltip data-placement="top" data-original-title="Like" class="iconlike " ng-class="{'active' :list.FeaturedPost[0].IsLike==1}">
                                                                        <svg height="16px" width="16px" class="svg-icons">
                                                                            <use xlink:href="assets/img/sprite.svg#iconLike"></use>
                                                                        </svg>
                                                                    </li>
                                                                    <li class="view-count" ng-if="list.FeaturedPost[0].NoOfLikes>0" ng-bind="list.FeaturedPost[0].NoOfLikes">
                                                                    </li>
                                                                    <li ng-if="list.FeaturedPost[0].NoOfComments>0" tooltip data-placement="top" data-original-title="Comment">
                                                                        <svg height="18px" width="18px" class="svg-icon">
                                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnComment"></use>
                                                                        </svg>
                                                                    </li>
                                                                    <li class="view-count" ng-if="list.FeaturedPost[0].NoOfComments>0" ng-bind="list.FeaturedPost[0].NoOfComments"> </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a ng-click="get_new_featured_post(list.GroupID,list.FeaturedPageNo);" class="list-close">
                                                        <span class="icon" tootip data-toggle="tooltip" data-placement="top" title="Remove">
                                                               <svg height="16px" width="16px" class="svg-icons">
                                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#closeIcon"></use>
                                                               </svg>
                                                           </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <!-- List Ends -->
                            <div class="panel-bottom" ng-hide='MyGrouplist.length>=TotalRecordsMyGroup'>
                                <button type="button" data-ng-click="LoadMoreMyGroups()" class="btn  btn-link">Load More <span><i class="caret"></i></span></button>
                            </div>
                            <div ng-if='TotalRecordsMyGroup==0' ng-cloak>                                       
                                <div class="nodata-panel" ng-cloak ng-if='TotalRecordsMyGroup==0'>
                                    <div ng-if="listing_display_type=='All My Groups'" ng-cloak class="nodata-text p-v-mlg">
                                        <span class="nodata-media">
                                            <img ng-src="<?php site_url() ?>assets/img/empty-img/empty-no-groups-created.png" alt="" />
                                        </span>
                                        <h5>{{lang.no_groups_heading}}</h5>                                                    
                                        <p class="text-off">
                                            {{lang.no_groups_message1}}
                                            <br>
                                            {{lang.no_groups_message2}}
                                        </p>
                                        <a ng-click="CreateEditGroup('createGroup');" data-toggle="modal" data-target="#createGroup">Create Group</a>
                                        <a ng-href="<?php echo site_url('group/discover_categories') ?>">Discover More</a>
                                    </div>

                                    <div ng-if="listing_display_type=='Groups I Created' || listing_display_type=='All Public Groups' || listing_display_type=='Suggested'" ng-cloak class="nodata-text p-v-mlg">
                                        <span class="nodata-media">
                                            <img ng-src="<?php site_url() ?>assets/img/empty-img/empty-no-groups-created.png" alt="" />
                                        </span>
                                        <h5>No Groups Created yet!</h5>                                                    
                                        <p class="text-off">
                                            You get to create groups to stay in touch with the people you want.
                                            <br>
                                            Start conversations, share media make plans and a lot more!
                                        </p>
                                        <a ng-click="CreateEditGroup('createGroup');" data-toggle="modal" data-target="#createGroup">Create Group</a>
                                    </div>
                                    <div ng-if="listing_display_type=='Groups I Joined'" ng-cloak class="nodata-text p-v-mlg">
                                        <span class="nodata-media">
                                            <img ng-src="<?php site_url() ?>assets/img/empty-img/empty-no-groups-discover.png" alt="" />
                                        </span>
                                        <h5>You haven’t joined any Groups yet!</h5>                                                    
                                        <p class="text-off">
                                            Join groups to stay in touch with the people you want.
                                            <br>
                                            Start conversations, share media make plans and a lot more!
                                        </p>
                                        <a ng-href="<?php echo site_url('group/discover_categories') ?>">Discover More</a>
                                    </div>
                                </div>                                   
                            </div>
                        </div>
                        <div ng-if='searchKey!="" && MyGrouplist.length==0 && TotalRecordsMyGroup!=0 && TotalRecordsMyGroup>-1' class="blank-block group-blank" ng-cloak>
                            <div class="row">
                                <div class="col-sm-8 col-xs-10">
                                    <img ng-src="{{AssetBaseUrl}}img/group-no-img.jpg" alt="" title="" class="img-circle">
                                    <p class="m-t-15">
                                        <?php echo lang('no_record'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            <!-- //Left Wall-->
            <!-- Right Wall-->
            <aside class="col-md-3 col-sm-3 col-xs-12 sidebar fadeInDown" data-scroll="sticky">
                <div ng-cloak ng-if="LoginSessionKey" class="panel panel-default">
                    <div class="panel-heading p-heading">
                        <h3><?php echo lang('look_for_more');?></h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <li>
                                <div class="description">
                                    <p class="m-b-10">
                                        <?php echo lang('look_for_more_description');?>
                                    </p>
                                    <!-- Button trigger modal -->
                                    <div class="button-block">
                                        <a class="btn btn-primary color-w" ng-href="{{base_url}}group/discover_categories" type="button">
                                            <?php echo lang('discover');?>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div ng-cloak ng-if="LoginSessionKey" class="panel panel-default">
                    <div class="panel-heading p-heading">
                        <h3><?php echo lang('create_new_group');?></h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <li>
                                <div class="description">
                                    <p class="m-b-10">
                                        <?php echo lang('create_new_group_description');?>
                                    </p>
                                    <!-- Button trigger modal -->
                                    <div class="button-block">
                                        <button class="btn  btn-primary btn-icon" type="button" ng-click="CreateEditGroup('createGroup');" data-toggle="modal" data-target="#createGroup">
                                            <?php echo lang('create_group');?>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="panel transparent" ng-cloak ng-if="LoginSessionKey" ng-init="Invites('Invite','')" ng-show='TotalRecordsInvited>0'>
                    <div class="panel-heading p-heading">
                        <h3><?php echo lang('group_invites');?></h3>
                    </div>
                    <div class="panel-body padding">
                        <ul class="listing-group suggest-list">
                            <li class="list-items-xmd" id="grp{{list.GroupGUID}}" ng-repeat="list in Invitedlist" ng-hide="list.length>0">
                                <div class="list-inner">
                                    <figure>
                                        <a entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="{{base_url}}group/{{list.GroupID}}">
                                            <img ng-if="list.ProfilePicture!=''&& list.Type=='FORMAL'" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle" alt="" title="">
                                            <img ng-if="list.Type=='INFORMAL' && list.ProfilePicture!='' && list.ProfilePicture!='group-no-img.jpg'" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle" alt="" title="">
                                            <div ng-if="list.Type=='INFORMAL' && list.ProfilePicture=='group-no-img.jpg'" ng-class="(list.EntityMembers>2) ? 'group-thumb' : 'group-thumb-two' ;" class="m-user-thmb ng-scope" ng-if="thread.ThreadImageName==''">
                                                <span ng-repeat="recipients in list.EntityMembers" class="ng-scope">
                                                <img err-src="{{SiteUrl}}assets/img/profiles/user_default.jpg" alt="" ng-src="{{ImageServerPath}}upload/profile/220x220/{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index<=2" class="ng-scope" ng-src="{{ImageServerPath}}upload/profile/220x220/{{recipients.ProfilePicture}}">
                                              </span>
                                            </div>
                                        </a>
                                    </figure>
                                    <div class="list-item-body">
                                        <a entitytype="group" ng-if="list.Type=='FORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{base_url}}group/{{list.GroupID}}">{{list.GroupName}} <span class="group-secure"> <i class="icon-n-global" ng-if="list.IsPublic==1"></i> <i class="icon-n-group-secret" ng-if="list.IsPublic==2"></i><i class="icon-n-closed" ng-if="list.IsPublic==0"></i> </span> </a>
                                        <a entitytype="group" ng-if="list.Type=='INFORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{base_url}}group/{{list.GroupID}}">
                                            <span ng-repeat="Member in list.EntityMembers"><span ng-bind="Member.FirstName" ng-if="$index<=2"></span><span ng-if="$index<2 && list.EntityMembers.length>=3">,</span><span ng-if="$index<(list.EntityMembers.length-1) && list.EntityMembers.length<3">,</span> </span>
                                            <span ng-if="list.EntityMembers.length>3"><?php echo lang('and');?> {{list.EntityMembers.length-3}} <?php echo lang('others');?></span>
                                            <span class="group-secure"> <i class="icon-n-global" ng-if="list.IsPublic==1"></i> <i class="icon-n-closed" ng-if="list.IsPublic==0"></i> <i class="icon-n-group-secret" ng-if="list.IsPublic==2"></i> </span>
                                        </a>                                        
                                        <div><span><?php echo lang('by');?></span>
                                            <small><a entitytype="user" entityguid="{{list.CreatorGUID}}" class="loadbusinesscard" href="{{base_url}}{{list.CreatedProfileUrl}}" ng-bind="list.CreatedBy"></a></small>
                                        </div>
                                        <div>
                                            <small>{{list.MemberCount}} <span ng-if='list.MemberCount==1'><?php echo lang('member');?></span>
                                                <span ng-if='list.MemberCount>1'><?php echo lang('members');?></span>
                                            </small>
                                        </div>
                                        <ul class="sublisting">
                                            <li>
                                                <a href="javascript:void(0);" ng-click="groupAcceptDenyRequest(list.GroupGUID,'2')">
                                                    <?php echo lang('accept');?> </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" ng-click="groupAcceptDenyRequest(list.GroupGUID,'13')">
                                                    <?php echo lang('deny');?> </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div ng-hide="listing_display_type=='Suggested'">
                    <?php $this->load->view('widgets/suggested_groups'); ?>
                </div>
            </aside>
            <!-- //Right Wall-->
        </div>
    </div>
    <!--//Container-->
    <?php
    if (!$this->settings_model->isDisabled(1)){
        $this->load->view('groups/create_group_popup');
    } ?>
</div>
<!--Hidden feilds -->
<input type="hidden" id="OffsetMyGroup" value="0">
<input type="hidden" id="OffsetJoin" value="0">
<input type="hidden" id="OffsetInvited" value="0">
<input type="hidden" id="OffsetManage" value="0">
<input type="hidden" id="LimitMyGroup" value="8">
<input type="hidden" id="LimitJoin" value="8">
<input type="hidden" id="LimitInvited" value="8">
<input type="hidden" id="LimitManage" value="8">
<input type="hidden" id="TotalRecordsMyGroup" value="0">
<input type="hidden" id="TotalRecordsJoined" value="0">
<input type="hidden" id="TotalRecordsInvited" value="0">
<input type="hidden" id="TotalRecordsSuggested" value="0">
<input type="hidden" id='OrderBy' value="">
<input type="hidden" id="hdnQuery" value="">
<input type="hidden" id="pageType" value="<?php echo $this->session->userdata('CurrentSection'); ?>">
<input type="hidden" id="searchgrp" value="">
<input type="hidden" id="hdncrdtype" value="">
<input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />
<input type="hidden" id="GroupListPageNo" value="1" />
<input type="hidden" id="unique_id" value="" />
<input type="hidden" id="UserGUID" value="<?php echo $UserGUID; ?>" />
<input type="hidden" id="fromList" value="true">
