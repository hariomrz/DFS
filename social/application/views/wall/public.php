<div class="container wrapper" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="GetwallPost()">
	<section class="news-feed" ng-cloak>
		<!-- <activity-item fa-suspendable repeat-done="wallRepeatDone();" public="true" loggedinname="{{LoggedInName}}" loggedinprofilepicture="{{LoggedInProfilePicture}}" class="wall-post" ng-repeat="postItem in activityData track by $index" data="postItem" 
		index="{{$index}}" ng-if="(postItem.ActivityType!='AlbumAdded' && postItem.ActivityType!='AlbumUpdated') || postItem.Album.length>0" ng-cloak> </activity-item> -->
		<div ng-cloak ng-repeat="data in activityData track by $index" repeat-done="wallRepeatDone();" ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index;" viewport-watch>
            <div class="inner-wall-post" ng-include="getTemplateUrl(data)" ></div>
        </div>
                <?php $this->load->view('include/feed-loader'); ?>
	</section>
	<div ng-if="activityData.length == 0" ng-cloak class="inner-wall-post">
	    <div class="wall-posts ng-scope">
	        <div  class="no-post panel panel-default">
	            <div class="blank-block">
	                <div class="row">
	                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10"> <img ng-src="<?php echo ASSET_BASE_URL ?>img/blank-wall-img.png"  >
	                        <h4>The content you requested cannot be displayed right now. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this content.</h4>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
</div>


<input type="hidden" id="FeedSortBy" value="2" />
<input type="hidden" id="IsMediaExists" value="2" />
<input type="hidden" id="PostOwner" value="" />
<input type="hidden" id="ActivityFilterType" value="0" />
<input type="hidden" id="AsOwner" value="0" />
<input type="hidden" id="ActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : '' ; ?>" />