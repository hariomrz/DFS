<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getVideoSearchList('',10,1, 1)">
    <section class="news-feed" ng-cloak>
        <div class="feed-title" ng-if="VideoTotalRecords > 0"><span ng-bind="VideoTotalRecords"></span> <span ng-bind="(VideoTotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
        <ul class="search-media-list row" ng-if="VideoTotalRecords>0">
            <li class="col-sm-6" ng-repeat="Video in VideoSearch">
                <div class="search-media-content" ng-click="$emit('showMediaPopupGlobalEmit',Video.MediaGUID,'');">
                    <div class="image-view" ng-style="{'background-image':'url('+ImageServerPath+'upload/'+Video.MediaFolder+'/220x220/'+getThumbImage(Video.ImageName)+')'}"></div>
                    <div class="video-iconblock">
                        <i class="icon">
                            <svg height="40px" width="40px" class="svg-icons">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconVideo'}}"></use>
                            </svg>
                        </i>
                    </div>
                    <div class="video-time" ng-bind="msToTime(Video.VideoLength)"></div>
                    <div class="detail-of-media">
                        <div ng-bind="Video.OriginalName"></div>
                        <div>By <span ng-bind="Video.FirstName+' '+Video.LastName"></span></div>
                        <ul class="sub-navigation">
                            <li ng-bind="getVideoTime(Video.CreatedDate)"></li>
                            <li>
                                <i class="icon">
                                    <svg height="14px" width="14px" class="svg-icons">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnGobal'}}"></use>
                                    </svg>
                                </i>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
        <div class="panel panel-info" ng-cloak ng-if="VideoTotalRecords==0">
          <div class="panel-body nodata-panel">
            <div class="nodata-text">
              <span class="nodata-media">
                  <img src="{{AssetBaseUrl}}img/empty-img/empty-no-search-results-found.png" >
              </span>
              <h5>No Results Found!</h5>
              <p class="text-off">
                Seems like there are no videos matching your search criteria! <br>Change your search terms, or tweak your filters. 
              </p>
              <a ng-href="<?php echo site_url('dashboard') ?>">Here's something for you to explore!</a>
            </div>
          </div>
        </div>
    </section>
</aside>