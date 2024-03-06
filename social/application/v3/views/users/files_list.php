 
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
} else {
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
<div class="<?php echo $container_class ?>" ng-init="isFileTab = <?php echo $isFileTab; ?>; isActivityPrevented = true;">
    <!-- Left Wall -->
    <aside ng-cloak class="<?php echo $class ?>" ng-controller="FileTabController as FileTabCtrl" ng-init="FileTabCtrl.NewsFeedFileTab = <?php echo ( isset($IsNewsFeed) && ( $IsNewsFeed == '1' ) ) ? 1 : 0; ?>; FileTabCtrl.getFilesList();">

        <div class="stiky-overlay" ng-class="{'active': isOverlayActive}" ng-click="toggleStickyPopup('close', 'tutorial');"></div>

        <div class="pages-block" ng-if="!ShowWallPostOnFilesTab">
            <div class="pages-head">
                <h4>Files</h4>
                <div class="search-cmn page-cmn-search">
                    <button class="search-contentinput visible-xs btn btn-default btn-sm" type="button" style="right:0;"><span class="icon"><i class="ficon-search"></i></span></button>
                    <div class="filters hidden-xs" style="right:0;">
                        <div class="filters-search">
                            <div class="input-group global-search">
                                <input ng-model="FileTabCtrl.SearchText" ng-change="FileTabCtrl.onSearchTextChange();" call-on-press-enter="FileTabCtrl.doFilesTabAction(true, FileTabCtrl.SearchAction);" type="text" class="form-control" placeholder="Search by file name." name="srch-filters" id="srch-filters">
                                <div class="input-group-btn">
                                    <button ng-click="FileTabCtrl.doFilesTabAction(true, FileTabCtrl.SearchAction);" class="btn-search" type="button"><i class="ficon-search" ng-class="FileTabCtrl.SearchAction" ng-cloak></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="clear"></div>
        </div>

        <section class="news-feed" ng-if="!ShowWallPostOnFilesTab" ng-cloak> <!--Files Tab Starts-->
            <div class="news-feed-listing">
                
                    <div class="panel-body nodata-panel" ng-hide="((FileTabCtrl.isFileListRequested) || (FileTabCtrl.fileTabList.length > 0))" ng-cloak>
                        <div class="nodata-text p-v-mlg">
                          <span class="nodata-media">
                              <img src="{{AssetBaseUrl}}img/empty-img/empty-no-files-attached.png" >
                          </span>
                          <h5>{{lang.no_file_heading}}</h5>
                          <p ng-if="config_detail.IsAdmin" ng-cloak class="text-off no-margin">
                            {{lang.no_file_message}}
                          </p>
                          <p ng-if="!config_detail.IsAdmin" ng-cloak class="text-off no-margin">
                            {{lang.no_file_message}}
                          </p>
                        </div>
                    </div>
                
                <ul class="file-listing" ng-cloak ng-if="FileTabCtrl.fileTabList.length>0">
                    <li ng-repeat="file in FileTabCtrl.fileTabList" ng-cloak>
                        <i class="ficon-file-type" ng-class="FileTabCtrl.addClassesToIcon(file)" ng-click="FileTabCtrl.hitToDownload(file.MediaGUID, file.ConversionStatus, file.MediaFolder);"><span ng-bind="'.' + file.MediaExtension"></span></i>
                        <div class="description">
                            <a class="list-file-name a-link" ng-bind="file.OriginalName" ng-click="FileTabCtrl.hitToDownload(file.MediaGUID, file.ConversionStatus, file.MediaFolder);"></a> 
                            <div>Posted by <a ng-bind="file.Name" ng-href="{{FileTabCtrl.baseURL}}{{file.ProfileURL}}"></a></div>
                            <span class="location" ng-cloak ng-init="createdDate = FileTabCtrl.createDateObj(UTCtoTimeZone(file.CreatedDate));">{{ createdDate | date : "d MMM 'at' h:mm a" }}</span>
                            <!--<span class="location">11 Dec at 9:03 AM</span>-->
                        </div>
                        <a class="download-it btn-link btn" ng-click="FileTabCtrl.hitToDownload(file.MediaGUID, file.ConversionStatus, file.MediaFolder);">
                            <svg class="svg-icon" width="20px" height="20px">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#dwonloadIcon'}}"></use>
                            </svg>
                        </a>
                    </li> 
                    <li class="loading-class wallloading center-block" ng-show="FileTabCtrl.isFileListRequested">
                        <div class="loader" style="display: block;"></div>
                    </li>

                    <li class="center-block" ng-show=" (!FileTabCtrl.isFileListRequested && FileTabCtrl.ShouldLoadMore)" ng-cloak>
                        <div class="load-more"><a class="arrow-box" data-ng-click="FileTabCtrl.getFilesList();"> Load More </a></div>
                    </li>
                </ul>
            </div> 
        </section> <!--Files Tab Ends-->

        <section class="news-feed" ng-cloak  ng-controller="NewsFeedCtrl" id="NewsFeedCtrl"> <!--Newsfeed Tab Starts-->
            <div class="news-feed-listing sticky-tutor" id="stickyTutorialBox" ng-cloak ng-if="stickynote" ng-class="{'overlay-content': stickynote}">
                <div class="feed-body">
                    <img ng-src="{{AssetBaseUrl}}img/sticky-pos-options.jpg" >
                </div>
            </div>
            <div 
                
                ng-if="ShowWallPostOnFilesTab" 
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