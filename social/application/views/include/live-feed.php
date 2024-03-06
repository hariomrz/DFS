<!-- Live Feed -->
<div class="live-feed visible-lg" >
    <div class="feedToggle visible-lg" data-toggle="feedtoggle" ng-click="LiveFeedToggle()" id="live_Feed">
        <i class="icon-n-feed-nav">&nbsp;</i>
    </div>
    <div class="live-feed-content">
        <div class="panel-heading p-heading">
            <h3 class="panel-title visible-lg">Live Feed</h3>
            <a class="panel-title hidden-lg" href="#liveFeeds" role="button" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">Live Feed <i class="icon-arrow-ac"></i></a>

        </div>
        <div class="live-feed-body" ng-cloak ng-if="LiveFeeds.length==0 && live_feed_call">
            <div class="nodata-panel">
                <div class="nodata-text">
                    <span class="nodata-media">
                        <img ng-src="{{AssetBaseUrl}}img/empty-img/empty-live-activity-feeds.png" >
                    </span>
                    <h5>No Conversations yet!</h5>
                    <p class="text-off">
                    It’s looking a little lonely in here.
                    <br>
                    Reach out and talk someone.
                    </p>
                    <a ng-href="<?php echo site_url('network/grow_your_network') ?>">Start Conversation</a>
                </div>
            </div> 
        </div>
        <div class="live-feed-body" id="liveFeeds" ng-show="LiveFeeds.length>0">
            <ul class="live-feed-listing">
                <li ng-repeat="feed in LiveFeeds">
                    <div class="feed-list-content">
                        <i ng-if="feed.Type=='PL' || feed.Type=='CL' || feed.Type=='ML'" class="ficon-heart"></i>
                        <i ng-if="feed.Type=='FA'" class="ficon-user"></i>
                        <i ng-if="feed.Type=='FU'" class="ficon-friends"></i>
                        <i ng-if="feed.Type=='PC' || feed.Type=='MC'" class="ficon-comment"></i>
                        <i ng-if="feed.Type=='EJ'" class="ficon-calc"></i>
                        <i ng-if="feed.Type=='GJ' || feed.Type=='GA'" class="ficon-friends"></i>
                        <i ng-if="feed.Type=='FP'" class="ficon-document"></i>
                        <span ng-if="feed.Users.length==1">
                            <a ng-repeat="user in feed.Users" ng-init="callToolTip()" class="loadbusinesscard" entitytype="user" entityguid="{{user.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{user.ProfileURL}}" ng-bind="user.FirstName+' '+user.LastName" ></a> 
                        </span>
                        
                        <span ng-if="feed.Users.length==2">
                            <a ng-repeat-start="user in feed.Users" ng-init="callToolTip()" class="loadbusinesscard" entitytype="user" entityguid="{{user.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{user.ProfileURL}}" ng-bind="user.FirstName+' '+user.LastName"></a>
                            <span ng-if="!$last">and</span>
                            <span ng-repeat-end></span> 
                        </span>
            
                        <span ng-if="feed.Users.length>2">
                            <a class="loadbusinesscard" entitytype="user" entityguid="{{feed.Users[0].ModuleEntityGUID}}" ng-init="callToolTip()" ng-href="<?php echo site_url() ?>{{feed.Users[0].ProfileURL}}" ng-bind="feed.Users[0].FirstName+' '+feed.Users[0].LastName"></a>
                            <span>and</span>
                            <a ng-init="callToolTip()" ng-bind="(feed.Users.length-1)+' others'" data-html="true" data-original-title="{{feed.user_tooltip}}" data-toggle="tooltip" ng-init="callToolTip()"></a> 
                        </span>
                        
                        <span ng-bind="feed.Message"></span>
            
                        <span ng-if="feed.Type=='PC' || feed.Type=='MC' || feed.Type=='PL' || feed.Type=='ML' || feed.Type=='CL'">
                        <a ng-if="feed.EntityLink!=='' && feed.EntityModuleID==1" class="loadbusinesscard" entitytype="group" entityguid="{{feed.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{feed.EntityLink}}" ng-bind="feed.EntityName"></a>
                        <a ng-if="feed.EntityLink!=='' && feed.EntityModuleID==3" class="loadbusinesscard" entitytype="user" entityguid="{{feed.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{feed.EntityLink}}" ng-bind="feed.EntityName"></a>
                        <a ng-if="feed.EntityLink!=='' && feed.EntityModuleID==14" class="loadbusinesscard" entitytype="event" entityguid="{{feed.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{feed.EntityLink}}" ng-bind="feed.EntityName"></a>
                        <a ng-if="feed.EntityLink!=='' && feed.EntityModuleID==18" class="loadbusinesscard" entitytype="page" entityguid="{{feed.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{feed.EntityLink}}" ng-bind="feed.EntityName"></a>
                        <span ng-if="feed.EntityLink==''" ng-bind="feed.EntityName"></span>
                        </span>
            
                        <a ng-href="{{feed.ActivityLink}}" ng-if="(feed.Type=='PC' || feed.Type=='PL') && (feed.ActivityTypeID=='5' || feed.ActivityTypeID=='6')" ng-bind="'Album'"></a>
                        <a ng-href="{{feed.ActivityLink}}" ng-if="(feed.Type=='PC' || feed.Type=='PL') && (feed.ActivityTypeID!=='5' && feed.ActivityTypeID!=='6')" ng-bind="'Post'"></a>
                        <a ng-click="$emit('showMediaPopupGlobalEmit',feed.EntityGUID,'');" ng-if="feed.Type=='MC' || feed.Type=='ML'" ng-bind="getMediaType(feed.Album[0].Media[0].ImageName)"></a>
                        <a ng-href="{{feed.ActivityLink}}" ng-if="feed.Type=='CL'" ng-bind="'Comment'"></a>
                        
                        <span ng-if="feed.Entities.length==1">
                            <a ng-repeat="entity in feed.Entities" ng-if="entity.ModuleID==1" class="loadbusinesscard" entitytype="group" entityguid="{{entity.ModuleEntityGUID}}"  ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a>
                            <a ng-repeat="entity in feed.Entities" ng-if="entity.ModuleID==3" class="loadbusinesscard" entitytype="user" entityguid="{{entity.ModuleEntityGUID}}"  ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a> 
                            <a ng-repeat="entity in feed.Entities" ng-if="entity.ModuleID==14" class="loadbusinesscard" entitytype="event" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a> 
                            <a ng-repeat="entity in feed.Entities" ng-if="entity.ModuleID==18" class="loadbusinesscard" entitytype="page" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a> 
                        </span>
                        
                        <span ng-if="feed.Entities.length==2" >
                            <span ng-repeat-start="entity in feed.Entities">
                            <a ng-if="entity.ModuleID==1" class="loadbusinesscard" entitytype="group" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a>
                             <a ng-if="entity.ModuleID==3" class="loadbusinesscard" entitytype="user" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a>
                            <a ng-if="entity.ModuleID==14" class="loadbusinesscard" entitytype="event" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a>
                            <a ng-if="entity.ModuleID==18" class="loadbusinesscard" entitytype="page" entityguid="{{entity.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{entity.ProfileURL}}" ng-bind="entity.FirstName+' '+entity.LastName" ng-init="callToolTip()"></a>
                            <span ng-if="!$last">and</span>
                            </span>
                            <span ng-repeat-end></span> 
                        </span>
            
                        <span ng-if="feed.Entities.length>2">
                            <a ng-href="<?php echo site_url() ?>{{feed.Entities[0].ProfileURL}}"  ng-if="feed.Entities[0].ModuleID==1" class="loadbusinesscard" entitytype="group" entityguid="{{feed.Entities[0].ModuleEntityGUID}}" ng-bind="feed.Entities[0].FirstName+' '+feed.Entities[0].LastName" ng-init="callToolTip()"></a>
                            <a ng-href="<?php echo site_url() ?>{{feed.Entities[0].ProfileURL}}"  ng-if="feed.Entities[0].ModuleID==3" class="loadbusinesscard" entitytype="user" entityguid="{{feed.Entities[0].ModuleEntityGUID}}" ng-bind="feed.Entities[0].FirstName+' '+feed.Entities[0].LastName" ng-init="callToolTip()"></a>
                            <a ng-href="<?php echo site_url() ?>{{feed.Entities[0].ProfileURL}}"  ng-if="feed.Entities[0].ModuleID==14" class="loadbusinesscard" entitytype="event" entityguid="{{feed.Entities[0].ModuleEntityGUID}}" ng-bind="feed.Entities[0].FirstName+' '+feed.Entities[0].LastName" ng-init="callToolTip()"></a>
                            <a ng-href="<?php echo site_url() ?>{{feed.Entities[0].ProfileURL}}"  ng-if="feed.Entities[0].ModuleID==18" class="loadbusinesscard" entitytype="page" entityguid="{{feed.Entities[0].ModuleEntityGUID}}" ng-bind="feed.Entities[0].FirstName+' '+feed.Entities[0].LastName" ng-init="callToolTip()"></a>
                            <span>and</span>
                            <a ng-bind="(feed.Entities.length-1)+' other'" data-html="true" data-original-title="{{feed.entity_tooltip}}" data-toggle="tooltip" ng-init="callToolTip()"></a>
                        </span>
            
                        <span ng-if="feed.ShowExtMsg" ng-bind="feed.ExtMsg"></span>
            
                        <span ng-cloak ng-if="feed.EName && feed.ELink && feed.Type!=='GA'">
                            on <a ng-bind="feed.EName" ng-href="{{feed.ELink}}" ng-if="feed.EModuleID==1" class="loadbusinesscard" entitytype="group" entityguid="{{feed.MEntityGUID}}"></a>
                            <a ng-bind="feed.EName" ng-href="{{feed.ELink}}" ng-if="feed.EModuleID==3" class="loadbusinesscard" entitytype="group" entityguid="{{feed.MEntityGUID}}"></a>
                            <a ng-bind="feed.EName" ng-href="{{feed.ELink}}" ng-if="feed.EModuleID==14" class="loadbusinesscard" entitytype="event" entityguid="{{feed.MEntityGUID}}"></a>
                            <a ng-bind="feed.EName" ng-href="{{feed.ELink}}" ng-if="feed.EModuleID==18" class="loadbusinesscard" entitytype="page" entityguid="{{feed.MEntityGUID}}"></a>
                        </span>
            
                        <span ng-cloak ng-if="feed.EName && feed.ELink && feed.Type=='GA'">
                            by <a ng-bind="feed.EName" ng-href="{{feed.ELink}}" ng-if="feed.EModuleID==3" class="loadbusinesscard" entitytype="user" entityguid="{{feed.MEntityGUID}}"></a>
                        </span>
            
                        <span class="post-msz" ng-if="feed.PostContent" ng-bind-html="get_summary(feed.PostContent)"></span>
            
                        <ul ng-if="feed.Type=='FA' || feed.Type=='FU'" class="liked-listing userlisting">
                            <li ng-repeat="entity in feed.Entities | limitTo:4" ng-class="($index==3) ? 'others-user' : '' ;">
                                <a>
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{entity.ProfilePicture}}" data-original-title="{{entity.FirstName+' '+entity.LastName}}" data-toggle="tooltip" ng-init="callToolTip()" class="mCS_img_loaded">
                                    <i ng-if="$index==3" class="icon-n-more-user" data-html="true" data-original-title="{{feed.entity_tooltip_img}}" data-toggle="tooltip" ng-init="callToolTip()" ng-bind="''"></i>
                                </a>
                            </li>
                        </ul>
            
            
                        <ul ng-if="feed.Album.length>0" class="liked-listing">
                            <li ng-repeat="media in feed.Album[0].Media">
                                <a ng-click="$emit('showMediaPopupGlobalEmit',media.MediaGUID,'');">
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="mCS_img_loaded" ng-if="feed.Album[0].AlbumName!=='Cover Photos' && feed.Album[0].AlbumName!=='Profile Photos' && feed.Album[0].AlbumName!=='Wall Media' && feed.Album[0].AlbumName!=='Comments Media'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/album/220x220/'+getThumbImage(media.ImageName)}}" />
                                    
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="mCS_img_loaded" ng-if="feed.Album[0].AlbumName=='Comments Media'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/comments/220x220/'+getThumbImage(media.ImageName)}}" />
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="mCS_img_loaded" ng-if="feed.Album[0].AlbumName=='Wall Media'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/wall/220x220/'+getThumbImage(media.ImageName)}}" />
                                    
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="mCS_img_loaded" ng-if="feed.Album[0].AlbumName=='Profile Photos'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/profile/220x220/'+getThumbImage(media.ImageName)}}" />
                                    <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="mCS_img_loaded" ng-if="feed.Album[0].AlbumName=='Cover Photos'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/profilebanner/220x220/'+getThumbImage(media.ImageName)}}" />
                                </a>
                            </li>
                        </ul>
            
                    </div>
                </li>
            </ul>      
        </div>
    </div>
    <div class="loader loader-live-feed absolute" style="top:auto;bottom:20%;display:none;width:30px;height:30px;transform:translate(-50%,0)"></div>
</div>

<input type="hidden" id="LiveFeedPageNo" value="1" />