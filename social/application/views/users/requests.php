
<div class="panel-body no-padding"  ng-if="connectionPanel == 'requests'">
    <div class="nav-tabs-default">
        <ul class="nav nav-tabs nav-tabs-liner nav-tabs-scroll" role="tablist">
            <li role="presentation" class="active" ng-click="changeRequestTab($event, 'received')">
                <a data-target="#received" >
                    Received 
                    <span ng-if="IncomingRequestCount > 0" ng-cloak ng-bind="'' + IncomingRequestCount + ''"></span>
                </a>
            </li>
            <li role="presentation" ng-click="changeRequestTab($event, 'sent')">
                <a data-target="#sent" >
                    Sent 
                    <span ng-if="OutgoingRequestCount > 0" ng-cloak ng-bind="'' + OutgoingRequestCount + ''"></span>
                </a>
            </li>
        </ul>
    </div>

    <!-- tab contents begins here-->
    <div class="tab-default-content">
        <div class="tab-content">


            <div role="tabpanel" class="tab-pane active" id="received">
                <ul class="row list-items-hover" ng-if="IncomingRequestCount > 0">
                    <li 
                        class="items col-sm-6 col-md-4 col-lg-3 xlist-items-hover" 
                        ng-repeat="Request in filteredRequests = IncomingRequest" 
                        repeat-done="colHeighIncoming();repeatDoneBCard();">
                        <div class="list-items-sm">
                            <div class="list-inner">
                                <figure>

                                    <a ng-href="{{'<?php echo site_url() ?>' + Request.ProfileLink}}" class="" entityguid="{{Request.UserGUID}}" entitytype="user">
                                        <img ng-if="Request.ProfilePicture !== '' && Request.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + Request.ProfilePicture}}">
                                        <span ng-if="Request.ProfilePicture == '' || Request.ProfilePicture == 'user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(Request.FirstName + ' ' + Request.LastName)"></span></span>
                                    </a>

                                </figure>
                                <div class="list-item-body">                            
                                    <h4 class="list-heading-xs">
                                        <a 
                                            ng-href="{{'<?php echo site_url() ?>' + Request.ProfileLink}}" 
                                            class="loadbusinesscard ellipsis"
                                            ng-bind="Request.FirstName + ' ' + Request.LastName" 
                                            entityguid="{{Request.UserGUID}}" entitytype="user"></a>

                                    </h4>
                                    <div>
                                        <small>
                                            <span 
                                                class="cursor-pointer"
                                                ng-if="Request.MutualFriendCount > 0" 
                                                ng-click="getMutualFriends(Request.UserGUID)">
                                                <span ng-bind="Request.MutualFriendCount"></span> 

                                                <?php echo lang('mutual_friend'); ?><span ng-if="Request.MutualFriendCount > 1"><?php echo lang('mutual_friends') ?></span>
                                            </span>

                                        </small>
                                    </div>
                                    <div class="btn-toolbar btn-toolbar-xs left">


                                        <a class="btn btn-default btn-xs selected"
                                           ng-if="Request.FriendStatus == '3' && Request.ShowFriendsBtn == '1' && Request.MySelf != '1'"
                                           ng-click="acceptIncomingRequest(Request.UserGUID)"
                                           >
                                            <span class="icon" data-toggle="tooltip" data-original-title="Accept">
                                                <i class="ficon-doubletick"></i>  
                                            </span>
                                        </a>
                                        <a class="btn btn-default btn-xs"
                                           ng-if="Request.FriendStatus == '3' && Request.ShowFriendsBtn == '1' && Request.MySelf != '1'"
                                           ng-click="denyIncomingRequest(Request.UserGUID)"
                                           >
                                            <span class="icon" data-toggle="tooltip" data-original-title="Deny">  
                                                <i class="ficon-cross"></i>  
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                
                               
                <div class="nodata-panel " ng-cloak ng-if="IncomingRequestCount == 0">
                    <div class="nodata-text nodata-text p-v-lg">
                        <span class="nodat-circle lg shadow">
                            <img src="assets/img/no-connection.png"  class="nodata-img">
                        </span>                        
                        <p class="no-margin">There are no data to show</p>
                    </div>
                </div>
                
            </div>


            <div role="tabpanel" class="tab-pane" id="sent">
                <ul class="row list-items-hover"  ng-if="OutgoingRequestCount > 0">
                    <li class="items col-sm-6 col-md-4 col-lg-3 xlist-items-hover" ng-repeat="Request in filteredRequests = OutgoingRequest " repeat-done="colHeightOutgoing();repeatDoneBCard();">
                        <div class="list-items-sm">
                            <div class="list-inner">
                                <figure>

                                    <a ng-href="{{'<?php echo site_url() ?>' + Request.ProfileLink}}" class="loadbusinesscard" entityguid="{{Request.UserGUID}}" entitytype="user">
                                        <img ng-if="Request.ProfilePicture !== '' && Request.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + Request.ProfilePicture}}">
                                        <span ng-if="Request.ProfilePicture == '' || Request.ProfilePicture == 'user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(Request.FirstName + ' ' + Request.LastName)"></span></span>
                                    </a>

                                </figure>
                                <div class="list-item-body">                            
                                    <h4 class="list-heading-xs">

                                        <a 
                                            ng-href="{{'<?php echo site_url() ?>' + Request.ProfileLink}}" 
                                            class="loadbusinesscard ellipsis"
                                            ng-bind="Request.FirstName + ' ' + Request.LastName" entityguid="{{Request.UserGUID}}" entitytype="user"></a>
                                    </h4>
                                    <div>
                                        <small>
                                            <span 
                                                class="cursor-pointer"
                                                ng-if="Request.MutualFriendCount > 0" 
                                                ng-click="getMutualFriends(Request.UserGUID)">
                                                <span ng-bind="Request.MutualFriendCount"></span> 
                                                <?php echo lang('mutual_friend'); ?><span ng-if="Request.MutualFriendCount > 1"><?php echo lang('mutual_friends') ?></span>
                                            </span>

                                        </small>
                                    </div>
                                    <a class="small text-brand bold"

                                       ng-if="Request.FriendStatus == '2' && Request.ShowFriendsBtn == '1' && Request.MySelf != '1'"
                                       ng-click="cancelRequest(Request.UserGUID)"

                                       >
                                           <?php echo lang('cancel_request') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                
                               
                <div class="nodata-panel " ng-cloak ng-if="OutgoingRequestCount == 0">
                    <div class="nodata-text nodata-text p-v-lg">
                        <span class="nodat-circle lg shadow">
                            <img src="assets/img/no-connection.png"  class="nodata-img">
                        </span>                        
                        <p class="no-margin">There are no data to show</p>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
</div>


<div class="panel-footer" ng-if="connectionPanel == 'requests' && requestCurrentTab == 'received' && (IncomingRequest.length < IncomingRequestCount)">
    <a class="loadmore" ng-click="RequestsPageSizeF()">
        <span class="text" ng-if="!connectionLoader"><?php echo lang('load_more') ?></span>
        <span class="loader" ng-if="connectionLoader">&nbsp;</span>
    </a>
</div>


<div class="panel-footer" ng-if="connectionPanel == 'requests' && requestCurrentTab == 'sent' && (OutgoingRequest.length < OutgoingRequestCount)">
    <a class="loadmore" ng-click="OutgoingPageSizeF()">
        <span class="text" ng-if="!connectionLoader"><?php echo lang('load_more') ?></span>
        <span class="loader" ng-if="connectionLoader">&nbsp;</span>
    </a>
</div>





<div class="panel-footer" ng-if="connectionPanel == 'connections' && connectionCurrentTab == 'friends' && (Friends.length < FriendsCount)">
    <a class="loadmore" ng-click="FriendsPageSizeF()">
        <span class="text" ng-if="!connectionLoader"><?php echo lang('load_more') ?></span>
        <span class="loader" ng-if="connectionLoader">&nbsp;</span>
    </a>
</div>

<div class="panel-footer" ng-if=" connectionPanel == 'connections' && connectionCurrentTab == 'following' && (Following.length < FollowingCount)">
    <a class="loadmore" ng-click="FollowingPageSizeF()">
        <span class="text" ng-if="!connectionLoader"><?php echo lang('load_more') ?></span>
        <span class="loader" ng-if="connectionLoader">&nbsp;</span>
    </a>
</div>

<div class="panel-footer" ng-if="connectionPanel == 'connections' && connectionCurrentTab == 'followers' && (Followers.length < FollowersCount)">
    <a class="loadmore" ng-click="FollowersPageSizeF()">
        <span class="text" ng-if="!connectionLoader"><?php echo lang('load_more') ?></span>
        <span class="loader" ng-if="connectionLoader">&nbsp;</span>
    </a>
</div>


<span class="loader absolute" ng-if="searchConnectionLoader">&nbsp;</span>
