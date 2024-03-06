 
<?php
if (!(isset($IsNewsFeed) && $IsNewsFeed == '1')) {
    if($ModuleID == '1')
    {
        $this->load->view('profile/banner/group');
    }
    if($ModuleID == '3')
    {
        $this->load->view('profile/banner/user');   
    }
    if($ModuleID == '14')
    {
        $this->load->view('profile/banner/event');
    }
    if($ModuleID == '18')
    {
        $this->load->view('profile/banner/page');
    }
}
?>
<?php
if ($this->page_name == 'userprofile') {
    $class = "col-md-6 col-sm-8 col-xs-12";
} elseif($this->page_name == 'forum')
{
    $class = "";
} elseif($this->page_name == 'group' || $this->page_name == 'pages')
{
    $class = "col-md-9 col-sm-9 col-xs-12";
}
else {
    $class = "col-md-8 col-sm-8 col-xs-12";
}
if(isset($IsNewsFeed) && $IsNewsFeed == '1')
{
    $class = "";
}
else
{
    $class = "col-md-9 col-sm-8";
}
?> 
<?php if($this->page_name == 'forum'){
$container_class = "";
} else {
    if(isset($IsNewsFeed) && $IsNewsFeed == '1')
    {
        $container_class = "";
    }
    else
    {
        $container_class = "container wrapper";
    }
} ?>
<div class="<?php echo $container_class ?>" ng-init="isLinkTab = <?php echo $isLinkTab; ?>; isActivityPrevented = true;">    
    <!-- Left Wall-->
    <aside ng-cloak class="<?php echo $class ?>" ng-controller="LinkTabController as LinkTabCtrl" ng-init="LinkTabCtrl.NewsFeedLinkTab = <?php echo ( isset($IsNewsFeed) && ( $IsNewsFeed == '1' ) ) ? 1 : 0; ?>; LinkTabCtrl.getLinksList();">

        <div class="stiky-overlay" ng-class="{'active': isOverlayActive}" ng-click="toggleStickyPopup('close', 'tutorial');"></div>        

        <div class="pages-block" ng-if="!ShowWallPostOnFilesTab">
            <div class="pages-head">
                <h4>Links</h4>
                <div class="search-cmn page-cmn-search">
                    <button class="search-contentinput visible-xs btn btn-default btn-sm" type="button" style="right:0;"><span class="icon"><i class="ficon-search"></i></span></button>
                    <div class="filters hidden-xs" style="right:0;">
                        <div class="filters-search">
                            <div class="input-group global-search">
                                <input ng-model="LinkTabCtrl.SearchText" ng-change="LinkTabCtrl.onSearchTextChange();" call-on-press-enter="LinkTabCtrl.doLinksTabAction(true, LinkTabCtrl.SearchAction);" type="text" class="form-control" placeholder="Search" name="srch-filters" id="srch-filters">
                                <div class="input-group-btn">
                                    <button ng-click="LinkTabCtrl.doLinksTabAction(true, LinkTabCtrl.SearchAction);" class="btn-search" type="button"><i class="ficon-search" ng-class="LinkTabCtrl.SearchAction" ng-cloak></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="clear"></div>
        </div>

        <section class="news-feed" ng-if="!ShowWallPostOnFilesTab" ng-cloak>
            <div class="news-feed-listing" ng-repeat="(linkIndex, linkData) in LinkTabCtrl.linkTabList">
                <div class="feed-body">
                    <ul class="list-group list-no-padding thumb-60 link-listing">
                        <li>
                            <figure class="img-rounded">
                                <a>
                                    <img ng-if="( linkData.ImageURL != '' )" ng-src="{{LinkTabCtrl.ImageServerPath + linkData.ImageURL}}" err-src="assets/img/link-default.jpg"   />
                                    <img ng-if="( linkData.ImageURL == '' )" ng-src="assets/img/link-default.jpg"   />
                                </a>
                            </figure>
                            <div class="description">
                                <a class="a-link" target="_blank" ng-href="{{linkData.URL}}" ng-bind="linkData.Title"></a>
                                <a class="name block link-wrap" target="_blank" ng-href="{{linkData.URL}}" ng-bind="linkData.URL"></a>
                                <span ng-if=" ( linkData.ModuleID == '3' ) " class="time" ng-cloak ng-init="createdDate = LinkTabCtrl.createDateObj(UTCtoTimeZone(linkData.CreatedDate));">Posted by <a ng-href="{{ LinkTabCtrl.baseURL + linkData.UserProfileURL }}" ng-bind="linkData.Name">Ryan Doe</a> in a <a ng-href="{{ LinkTabCtrl.baseURL  + linkData.ActivityURL }}">post</a> on <a ng-href="{{ LinkTabCtrl.baseURL + linkData.EntityProfileURL }}" ng-bind="linkData.EntityName + '\'s wall'"></a> on {{ createdDate | date : "d MMM 'at' h:mm a" }}.</span>
                                <span ng-if=" ( linkData.ModuleID == '1' ) " class="time" ng-cloak ng-init="createdDate = LinkTabCtrl.createDateObj(UTCtoTimeZone(linkData.CreatedDate));">Posted by <a ng-href="{{ LinkTabCtrl.baseURL + linkData.UserProfileURL }}" ng-bind="linkData.Name">Ryan Doe</a> in a <a ng-href="{{ LinkTabCtrl.baseURL + linkData.ActivityURL }}">post</a> in group <a ng-href="{{ LinkTabCtrl.baseURL + linkData.EntityProfileURL }}" ng-bind="linkData.EntityName">Group</a> on {{ createdDate | date : "d MMM 'at' h:mm a" }}.</span>
                                <span ng-if=" ( linkData.ModuleID == '14' ) " class="time" ng-cloak ng-init="createdDate = LinkTabCtrl.createDateObj(UTCtoTimeZone(linkData.CreatedDate));">Posted by <a ng-href="{{ LinkTabCtrl.baseURL + linkData.UserProfileURL }}" ng-bind="linkData.Name">Ryan Doe</a> in a <a ng-href="{{ LinkTabCtrl.baseURL + linkData.ActivityURL }}">post</a> in event <a ng-href="{{ LinkTabCtrl.baseURL + linkData.EntityProfileURL }}" ng-bind="linkData.EntityName">Event</a> on {{ createdDate | date : "d MMM 'at' h:mm a" }}.</span>
                                <span ng-if=" ( ( linkData.ModuleID == '18' ) && ( linkData.PostAsModuleID == '18' ) ) " class="time" ng-cloak ng-init="createdDate = LinkTabCtrl.createDateObj(UTCtoTimeZone(linkData.CreatedDate));">Posted by <a ng-href="{{ LinkTabCtrl.baseURL + 'page/' + linkData.EntityProfileURL }}" ng-bind="linkData.UserName">Ryan Doe</a> in a <a ng-href="{{ LinkTabCtrl.baseURL + linkData.ActivityURL }}">post</a> in page <a ng-href="{{ LinkTabCtrl.baseURL + 'page/' + linkData.EntityProfileURL }}" ng-bind="linkData.EntityName">Page</a> on {{ createdDate | date : "d MMM 'at' h:mm a" }}.</span>
                                <span ng-if=" ( ( linkData.ModuleID == '18' ) && ( linkData.PostAsModuleID == '3' ) ) " class="time" ng-cloak ng-init="createdDate = LinkTabCtrl.createDateObj(UTCtoTimeZone(linkData.CreatedDate));">Posted by <a ng-href="{{ LinkTabCtrl.baseURL + linkData.UserProfileURL }}" ng-bind="linkData.Name">Ryan Doe</a> in a <a ng-href="{{ LinkTabCtrl.baseURL + linkData.ActivityURL }}">post</a> in page <a ng-href="{{ LinkTabCtrl.baseURL + 'page/' + linkData.EntityProfileURL }}" ng-bind="linkData.EntityName">Page</a> on {{ createdDate | date : "d MMM 'at' h:mm a" }}.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="panel panel-info" ng-if="( !LinkTabCtrl.isLinkListRequested && !LinkTabCtrl.linkTabList.length )" ng-cloak>
                <div class="panel-body nodata-panel">
                    <div class="nodata-text p-v-mlg">
                      <span class="nodata-media">
                          <img src="assets/img/empty-img/empty-no-links-shared.png" >
                      </span>
                      <h5>{{lang.no_link_heading}}</h5>
                      <p class="text-off no-margin">
                        {{lang.no_link_message}}
                      </p>
                    </div>
                </div>
            </div>
            <div class="center-block" ng-show=" (!LinkTabCtrl.isLinkListRequested && LinkTabCtrl.ShouldLoadMore)" ng-cloak>
                <div class="load-more"><a class="arrow-box" data-ng-click="LinkTabCtrl.getLinksList();"> Load More </a></div>
            </div>
            <div class="loading-class wallloading center-block" ng-show="LinkTabCtrl.isLinkListRequested">
                <div class="loader" style="display: block;"></div>
            </div>
        </section> <!--Links Tab Ends-->

        <section class="news-feed" ng-cloak  ng-controller="NewsFeedCtrl" id="NewsFeedCtrl"> <!--Newsfeed Tab Starts-->
            <div class="news-feed-listing sticky-tutor" id="stickyTutorialBox" ng-cloak ng-if="stickynote" ng-class="{'overlay-content': stickynote}">
                <div class="feed-body">
                    <img ng-src="{{AssetBaseUrl}}img/sticky-pos-options.jpg" >
                </div>
            </div>
            <div 
                
                ng-show="ShowWallPostOnFilesTab" 
                id="activityFeedId-{{ FeedIndex}}" 
                ng-repeat="data in activityData track by $index" 
                repeat-done="wallRepeatDone();" 
                ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index; initTagsItem($index); " 
                viewport-watch 
                class="news-feed-listing" 
                ng-class="{'overlay-content': data.stickynote}"
            >
                <div class="inner-wall-post" ng-include="getTemplateUrl(data)" ></div>
            </div>
        </section>
        <div class="wallloader">
            <div class="spinner32"></div>
        </div>
    </aside> <!--Newsfeed Tab Ends-->
    <!-- //Left Wall-->
    <?php
    if (isset($IsNewsFeed) && $IsNewsFeed == '1') {
        //Do Some Action
    } else if(isset($page_name) && $page_name == 'forum')
    {
        //Do Some Action
    } else {
        if($this->page_name == 'group' || $this->page_name == 'pages')
            echo "<aside class='col-md-3 col-sm-4'>";
        $this->load->view('sidebars/right');
        if($this->page_name == 'group')
            echo "</aside>";
    }
    $this->load->view('include/wall-modal');
    ?>
</div>