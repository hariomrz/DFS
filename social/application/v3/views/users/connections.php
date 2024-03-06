<?php $this->load->view('profile/profile_banner') ?>
<!--Container-->
<div class="container wrapper">
    <div class="row">
        <!-- Left Wall-->
        <aside class="col-md-9"  ng-controller="UserListCtrl" id="UserProfileConnections" ng-init="getConnections();getProfileUser();" ng-cloak>
            <div class="panel panel-info" >
                <div class="panel-heading no-border">
                    <div class="row">
                        <div class="col-sm-4 col-md-5">
                            <h3 ng-if="connectionPanel == 'connections'" class="panel-title">
                                <span class="text">
                                    {{::lang.connections}} 
                                    <span ng-if="TotalCount > 0" ng-bind="'(' + TotalCount + ')'"></span>
                                </span>
                            </h3>

                            <h3 ng-if="connectionPanel == 'requests'" class="panel-title">
                                <span class="text">
                                    Respond to your friend requests 
                                    <span ng-if="IncomingRequestCount > 0" ng-bind="'(' + IncomingRequestCount + ')'"></span>
                                </span>
                            </h3>
                        </div>
                        <div class="col-sm-8 col-md-7">
                            <div class="panel-right-action">
                                <ul class="action-list">
                                    <li ng-if="Settings.m10 == '1' && connectionPanel == 'connections'">
                                        <a ng-if="SelfProfile == 1" ng-click="changeConnectionPanel('requests')" class="btn btn-default btn-block btn-count">
                                            <span class="text">Friends Requests</span>
                                            <span class="badge-count" ng-if="IncomingRequestCount > 0" ng-bind="IncomingRequestCount"></span>
                                        </a>
                                    </li>
                                    <li ng-if="Settings.m10 == '1' && connectionPanel == 'requests'">
                                        <a ng-click="changeConnectionPanel('connections')" class="btn btn-default btn-block btn-count">
                                            <span class="text"><?php echo lang('connection') ?> </span>
                                            <span class="badge-count" ng-if="totalConnections > 0" ng-bind="totalConnections"></span></a>
                                    </li>
                                    <li>
                                        <div class="input-search form-control right">
                                            <input type="text" 
                                                   ng-model="searchConnection" 
                                                   ng-keyup="getConnectionCount(searchConnection, 1)" 
                                                   ng-init="searchConnection = ''" 
                                                   id="srch-filters" 
                                                   name="srch-filters" 
                                                   placeholder="Quick Search" class="form-control">

                                            <div class="input-group-btn">
                                                <button 
                                                    type="button" 
                                                    class="btn" 
                                                    ng-click="clearConnectionSearch();">
                                                    <i ng-if="!searchConnection" class="ficon-search"></i>
                                                    <i ng-if="searchConnection" class="ficon-cross"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body no-padding"  ng-if="connectionPanel == 'connections'">
                    <div class="nav-tabs-default">
                        <div class="row">
                            <div class="col-sm-9 col-xs-8">
                                <ul class="nav nav-tabs nav-tabs-liner nav-tabs-scroll" role="tablist">
                                    <li ng-cloak ng-if="Settings.m10 == '1'" role="presentation" ng-class="(Settings.m10 == '1') ? 'active' : '';"  ng-click="changeConnectionsTab($event, 'friends')">
                                        <a data-target="#friends"  ng-if="ViewFriendsPermission == '1'">
                                            All Friends <span ng-bind="FriendsCount"></span>
                                        </a>
                                        <a data-target="#friends" ng-if="ViewFriendsPermission == '0'">
                                            Mutual Friends <span ng-bind="FriendsCount"></span>
                                        </a>
                                    </li>
                                    <li ng-cloak ng-if="Settings.m11 == '1'" ng-class="(Settings.m10 == '0') ? 'active' : '';" role="presentation"  ng-click="changeConnectionsTab($event, 'following')" ng-cloak>
                                        <a data-target="#following" >
                                            <!-- <?php echo lang('following'); ?>  -->
                                            Following <span ng-cloak ng-bind="'' + FollowingCount + ''"></span>
                                        </a>
                                    </li>
                                    <li ng-cloak ng-if="Settings.m11 == '1'" role="presentation"  ng-click="changeConnectionsTab($event, 'followers')" ng-cloak>
                                        <a data-target="#followers" >
                                            <?php echo lang('followers'); ?> 
                                            <span ng-bind="'' + FollowersCount + ''"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-sm-3 col-xs-4">
                                <ul class="sort-action" ng-if="0">
                                    <li>
                                        <div class="dropdown-sort">                  
                                            <div class="dropdown">
                                                <a data-toggle="dropdown">
                                                    <span class="text">All Friends</span><span class="icon hidden-xs"><i class="ficon-arrow-down"></i></span>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li><a>Option</a></li>
                                                    <li><a>Option</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- tab contents begins here-->
                    <div class="tab-default-content">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane" ng-class="(Settings.m10 == '1') ? 'active' : '';" id="friends" >
                                <ul class="row list-items-hover" ng-if="FriendsCount > 0">
                                    <li class="items col-sm-6 col-md-4 col-lg-3 xlist-items-hover" ng-repeat="(friendsKey, friends) in filteredFriends = Friends" repeat-done="repeatDoneBCard();">
                                        <div class="list-items-sm">
                                            <div class="list-inner">
                                                <figure>                                                    
                                                    <a ng-href="{{'<?php echo site_url() ?>' + friends.ProfileLink}}" class="loadbusinesscard" entityguid="{{friends.UserGUID}}" entitytype="user">
                                                        <img ng-if="friends.ProfilePicture !== '' && friends.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + friends.ProfilePicture}}">
                                                        <img ng-if="friends.ProfilePicture == '' || friends.ProfilePicture == 'user_default.jpg'" ng-cloak err-name="{{friends.FirstName + ' ' + friends.LastName}}" src="" />
                                                    </a>                                                       
                                                </figure>
                                                <div class="list-item-body">
                                                    <div class="pull-right dropdown list-items-options dropdown-Onhover" ng-if="friends.MySelf != '1'">
                                                        <a data-toggle="dropdown" ><i class="ficon-arrow-down"></i></a>
                                                        <ul class="dropdown-menu">
                                                            <li ng-if="friends.ShowFriendsBtn == 1 && friends.MySelf != '1'">
                                                                <a href="javascript:void(0);" ng-if="friends.FriendStatus == 1" ng-click="removeFriend(friends.UserGUID)">
                                                                    Unfriend
                                                                </a>

                                                                <a href="javascript:void(0);" 
                                                                   ng-if="friends.FriendStatus == 0 || friends.FriendStatus == 4 || friends.FriendStatus == 3" 
                                                                   ng-click="sendRequest(friends.UserGUID, '', friends)">
                                                                    Add Friend
                                                                </a>

                                                                <a href="javascript:void(0);" 
                                                                   ng-if="friends.FriendStatus == 2" 
                                                                   ng-click="cancelRequest(friends.UserID, '', friends)">
                                                                    Cancel Request
                                                                </a>
                                                            </li>
                                                            <li ng-if="friends.ShowFollowBtn == 1 && friends.MySelf != '1'">
                                                                <a href="javascript:void(0);" 

                                                                   id="followmem1{{friends.UserGUID}}" 
                                                                   ng-click="follow(friends.UserGUID, friends, friendsKey, 'Friends')"
                                                                   ng-bind="friends.FollowStatus"
                                                                   >

                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <h4 class="list-heading-xs">
                                                        <a 
                                                            ng-href="{{'<?php echo site_url() ?>' + friends.ProfileLink}}" 
                                                            class="loadbusinesscard ellipsis"
                                                            ng-bind="friends.FirstName + ' ' + friends.LastName"  
                                                            entityguid="{{friends.UserGUID}}" 
                                                            entitytype="user"></a>
                                                    </h4>
                                                    <div>
                                                        <small>
                                                            <span 
                                                                class="cursor-pointer"
                                                                ng-if="friends.MutualFriendCount > 0" 
                                                                ng-click="getMutualFriends(friends.UserGUID)">
                                                                <span ng-bind="friends.MutualFriendCount"></span> 
                                                                <?php echo lang('mutual_friend'); ?><span ng-if="friends.MutualFriendCount > 1"><?php echo lang('mutual_friends') ?></span>
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div class="nodata-panel" ng-cloak ng-if="FriendsCount == 0">
                                    <div class="nodata-text">
                                        <span class="nodata-media">
                                            <img src="assets/img/empty-img/empty-no-friends.png" >
                                        </span>
                                        <h5 ng-if="config_detail.IsAdmin">{{lang.no_friends_heading}}</h5>
                                        <p class="text-off">
                                            <span ng-if="config_detail.IsAdmin">
                                                {{lang.no_friends_message}}
                                            </span>
                                            <span ng-if="!config_detail.IsAdmin">
                                                <span ng-bind="FirstName"></span> {{lang.no_friends_other_profile_message}}
                                        </p>                    
                                        <a ng-if="!config_detail.IsAdmin && profileUser.FriendStatus == '4'" onclick="$('#friendrequest').trigger('click')" ng-click="profileUser.FriendStatus = '2'">{{lang.send_request}}</a>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" ng-class="(Settings.m10 == '0') ? 'active' : '';" id="following" >
                                <ul class="row list-items-hover" ng-if="FollowingCount > 0">
                                    <li class="items col-sm-6 col-md-4 col-lg-3 xlist-items-hover" ng-repeat="(followingKey, following) in Following" repeat-done="repeatDoneBCard();">
                                        <div class="list-items-sm">
                                            <div class="list-inner">
                                                <figure>                                                                                                        
                                                    <a ng-href="{{'<?php echo site_url() ?>' + following.ProfileLink}}" class="loadbusinesscard" entityguid="{{following.UserGUID}}" entitytype="user">
                                                        <img ng-if="following.ProfilePicture !== '' && following.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + following.ProfilePicture}}">
                                                        <img ng-if="following.ProfilePicture == '' || following.ProfilePicture == 'user_default.jpg'" ng-cloak err-name="{{following.FirstName + ' ' + following.LastName}}" src="" />
                                                    </a>                                                    
                                                </figure>
                                                <div class="list-item-body">
                                                    <div class="pull-right dropdown list-items-options dropdown-Onhover" ng-if="following.MySelf != '1'">
                                                        <a data-toggle="dropdown" ><i class="ficon-arrow-down"></i></a>
                                                        <ul class="dropdown-menu">
                                                            <li ng-if="following.ShowFriendsBtn == 1 && following.MySelf != '1'">
                                                                <a href="javascript:void(0);" 
                                                                   ng-if="following.FriendStatus == 1" 
                                                                   ng-click="removeFriend(following.UserGUID, '', following)">
                                                                    Unfriend
                                                                </a>
                                                                <a href="javascript:void(0);" 
                                                                   ng-if="following.FriendStatus == 0 || following.FriendStatus == 4 || following.FriendStatus == 3" 
                                                                   ng-click="sendRequest(following.UserGUID, '', following)">
                                                                    Add Friend
                                                                </a>

                                                                <a href="javascript:void(0);" 
                                                                   ng-if="following.FriendStatus == 2" 
                                                                   ng-click="cancelRequest(following.UserID, '', following)">
                                                                    Cancel Request
                                                                </a>

                                                            </li>
                                                            <li  ng-if="following.ShowFollowBtn == 1 && following.MySelf != '1'">
                                                                <a href="javascript:void(0);"

                                                                   id="followmem1{{following.UserGUID}}" 
                                                                   ng-click="follow(following.UserGUID, following, followingKey, 'Following')"
                                                                   ng-bind="following.FollowStatus"
                                                                   ></a>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                    <h4 class="list-heading-xs">
                                                        <a 
                                                            ng-href="{{'<?php echo site_url() ?>' + following.ProfileLink}}" 
                                                            class="loadbusinesscard ellipsis"
                                                            ng-bind="following.FirstName + ' ' + following.LastName"  
                                                            entityguid="{{following.UserGUID}}" 
                                                            entitytype="user"></a>

                                                    </h4>
                                                    <div>
                                                        <small>
                                                            <span 
                                                                class="cursor-pointer"
                                                                ng-if="following.MutualFriendCount > 0" 
                                                                ng-click="getMutualFriends(following.UserGUID)">
                                                                <span ng-bind="following.MutualFriendCount"></span> 
                                                                <?php echo lang('mutual_friend'); ?><span ng-if="following.MutualFriendCount > 1"><?php echo lang('mutual_friends'); ?></span>
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>                               
                                <div class="nodata-panel" ng-cloak ng-if="FollowingCount == 0">
                                    <div class="nodata-text">
                                        <span class="nodata-media">
                                            <img src="assets/img/empty-img/empty-no-following.png" >
                                        </span>
                                        <h5 ng-if="config_detail.IsAdmin">{{lang.no_following_heading}}</h5>
                                        <p class="text-off">
                                            <span ng-if="!config_detail.IsAdmin">
                                                <span ng-bind="FirstName"></span> {{lang.no_following_other_profile_message}}
                                                <!-- <span ng-hide="config_detail.IsAdmin">See what <span ng-bind="(Gender=='1') ? 'he' : (Gender=='2') ? 'she' : '' ;"></span>'s upto!</span> -->
                                            </span>
                                            <span ng-if="config_detail.IsAdmin">{{lang.no_following_message}}
                                                <!-- <br>
                                                The more you follow the better stories you get. -->
                                            </span>
                                        </p>
                                        <!-- <a ng-if="!config_detail.IsAdmin" ng-href="< ?php echo current_url().'/../about'; ?>">Know more about <span ng-bind="(Gender=='1') ? 'him' : (Gender=='2') ? 'her' : '' ;"></span></a> -->
                                        <a ng-if="config_detail.IsAdmin" ng-href="<?php echo site_url('network/grow_your_network') ?>">Find People to Follow</a>
                                    </div>
                                </div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="followers"  ng-cloak>
                                <ul class="row list-items-hover" ng-if="FollowersCount > 0">
                                    <li class="items col-sm-6 col-md-4 col-lg-3 xlist-items-hover" ng-repeat="(followersKey, followers) in Followers" repeat-done="repeatDoneBCard();">
                                        <div class="list-items-sm">
                                            <div class="list-inner">
                                                <figure>
                                                    <a ng-href="{{'<?php echo site_url() ?>' + followers.ProfileLink}}" class="loadbusinesscard" entityguid="{{followers.UserGUID}}" entitytype="user">
                                                        <img ng-if="followers.ProfilePicture !== '' && followers.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + followers.ProfilePicture}}">
                                                        <img ng-if="followers.ProfilePicture == '' || followers.ProfilePicture == 'user_default.jpg'" ng-cloak err-name="{{followers.FirstName + ' ' + followers.LastName}}" src="" />
                                                    </a>
                                                </figure>
                                                <div class="list-item-body"> 


                                                    <div class="pull-right dropdown list-items-options dropdown-Onhover" ng-if="followers.MySelf != '1'">
                                                        <a data-toggle="dropdown" ><i class="ficon-arrow-down"></i></a>
                                                        <ul class="dropdown-menu">

                                                            <li ng-if="followers.ShowFriendsBtn == 1 && followers.MySelf != '1'">
                                                                <a href="javascript:void(0);" 
                                                                   ng-if="followers.FriendStatus == 1" 
                                                                   ng-click="removeFriend(followers.UserGUID, '', followers)">
                                                                    Unfriend
                                                                </a>
                                                                <a href="javascript:void(0);" 
                                                                   ng-if="followers.FriendStatus == 0 || followers.FriendStatus == 4 || followers.FriendStatus == 3" 
                                                                   ng-click="sendRequest(followers.UserGUID, '', followers)">
                                                                    Add Friend
                                                                </a>

                                                                <a href="javascript:void(0);" 
                                                                   ng-if="following.FriendStatus == 2" 
                                                                   ng-click="cancelRequest(followers.UserID, '', followers)">
                                                                    Cancel Request
                                                                </a>

                                                            </li>

                                                            <li  ng-if="followers.ShowFollowBtn == 1 && followers.MySelf != '1'">
                                                                <a href="javascript:void(0);"

                                                                   id="followmem1{{followers.UserGUID}}" 
                                                                   ng-click="follow(followers.UserGUID, followers, followersKey, 'Followers')"
                                                                   ng-bind="followers.FollowStatus"
                                                                   ></a>
                                                            </li>

                                                            <li ng-if="followers.ShowFollowBtn == 1 && followers.MySelf != '1' && SelfProfile == 1">
                                                                <a href="javascript:void(0);"                                                                                                                                      
                                                                   ng-click="removeFollow(followers.UserGUID, followersKey)"                                                                   
                                                                   >
                                                                    Remove
                                                                </a>
                                                            </li>

                                                        </ul>
                                                    </div>


                                                    <h4 class="list-heading-xs">
                                                        <a 
                                                            ng-href="{{'<?php echo site_url() ?>' + followers.ProfileLink}}" 
                                                            class="loadbusinesscard ellipsis"
                                                            ng-bind="followers.FirstName + ' ' + followers.LastName"  
                                                            entityguid="{{followers.UserGUID}}" 
                                                            entitytype="user"></a>

                                                    </h4>
                                                    <div>
                                                        <small>
                                                            <span 
                                                                class="cursor-pointer"
                                                                ng-if="followers.MutualFriendCount > 0" 
                                                                ng-click="getMutualFriends(followers.UserGUID)">
                                                                <span ng-bind="followers.MutualFriendCount"></span>                                                                 
                                                                <?php echo lang('mutual_friend'); ?><span ng-if="followers.MutualFriendCount > 1"><?php echo lang('mutual_friends') ?></span>
                                                            </span>
                                                        </small>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>                                
                                <div class="nodata-panel" ng-cloak ng-if="FollowersCount == 0">
                                    <div class="nodata-text">
                                        <span class="nodata-media">
                                            <img src="assets/img/empty-img/empty-no-followers.png" >
                                        </span>
                                        <h5 ng-if="config_detail.IsAdmin">{{lang.no_followers_heading}}</h5>
                                        <!-- <h5 ng-if="!config_detail.IsAdmin"><span ng-bind="FirstName"></span>!</h5> -->
                                        <p class="text-off">
                                            <span ng-if="!config_detail.IsAdmin">
                                                <span ng-bind="FirstName"></span> {{lang.no_followers_other_profile_message}}
                                            </span>
                                            <span ng-if="config_detail.IsAdmin">{{lang.no_followers_message}}
                                                <!-- <br>
                                                Reach out and talk someone. -->
                                            </span>
                                        </p>
                                        <a ng-if="!config_detail.IsAdmin" onclick="$('.followuser').trigger('click')">Start Following!<!-- <span ng-bind="(Gender=='1') ? 'him' : (Gender=='2') ? 'her' : '' ;"></span>! --></a>
                                        <!-- <a ng-if="config_detail.IsAdmin" ng-href="<?php echo site_url('network/grow_your_network') ?>">Start Conversation</a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $this->load->view('users/requests') ?>
            </div>
        </aside>
        <!-- //Left Wall-->
        <!-- Right Wall-->
        <aside class="col-md-3" data-scroll="fixed">
            <?php $this->load->view('sidebars/right'); ?>
        </aside>
        <!-- //Right Wall-->
    </div>
</div>
<!--//Container-->
<input type="hidden" id="UserID" value="<?php
if (isset($UserID)) {
    echo $UserID;
}
?>" />
<input type="hidden" value="2" id="UserWall">
