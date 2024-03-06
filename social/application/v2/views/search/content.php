<aside class="col-sm-7 col-md-7 col-xs-12 pull-left"  ng-controller="NewsFeedCtrl" id="NewsFeedCtrl">
    <div class="stiky-overlay" ng-class="{'active': isOverlayActive}" ng-click="toggleStickyPopup('close', 'tutorial');"></div>
    <section class="news-feed" ng-show=" ((activityData.length > 0) && !isWallPostRequested)" ng-cloak  
             infinite-scroll="GetwallPost()" 
             infinite-scroll-distance="2" 
             infinite-scroll-use-document-bottom="true" 
             infinite-scroll-disabled="is_busy"
             > <!--Newsfeed Tab Starts-->
        <div class="news-feed-listing sticky-tutor" id="stickyTutorialBox" ng-cloak ng-if="stickynote" ng-class="{'overlay-content': stickynote}">
            <div class="feed-body">
                <img ng-src="{{AssetBaseUrl}}img/sticky-pos-options.jpg" >
            </div>
        </div>
        <div 
            id="activityFeedId-{{ FeedIndex}}" 
            ng-repeat="data in activityData track by $index" 
            repeat-done="wallRepeatDone();" 
            ng-init="SettingsFn(data.ActivityGUID);
                                FeedIndex = $index;
                                initTagsItem($index);
            " viewport-watch 
            class="news-feed-listing" 
            ng-class="{'overlay-content': data.stickynote}"
            >
            <div class="inner-wall-post" ng-include="getTemplateUrl(data)" ></div>
        </div>

        <?php $this->load->view('include/feed-loader'); ?>

    </section>    
    <div class="panel panel-info" ng-cloak ng-if="isNewsFeedResponseDone && activityData.length == 0">
        <div class="panel-body nodata-panel">
            <div class="nodata-text">
                <span class="nodata-media">
                    <img src="assets/img/empty-img/empty-no-search-results-found.png" >
                </span>
                <h5>No Results Found!</h5>
                <p class="text-off">
                Seems like there are no content matching your search criteria! <br>Change your search terms, or tweak your filters. 
                </p>
                <a ng-href="<?php echo site_url('dashboard') ?>">Here's something for you to explore!</a>
            </div>
        </div>
    </div>
</aside>

<?php
$this->load->view('include/wall-modal');
if(!$this->settings_model->isDisabled(30)){
    $this->load->view('poll/invite_popup');
}
$this->load->view('include/invite-modal-popup');
?>