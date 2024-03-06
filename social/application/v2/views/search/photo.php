<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getPhotoSearchList('',10, 1, 1)">
    <section class="news-feed" ng-cloak>
        <div class="feed-title" ng-if="PhotoTotalRecords > 0"><span ng-bind="PhotoTotalRecords"></span> <span ng-bind="(PhotoTotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
        <ul class="search-media-list row" ng-if="PhotoTotalRecords>0">
            <li ng-repeat="Photo in PhotoSearch" class="col-sm-6">
                <div class="search-media-content" ng-click="$emit('showMediaPopupGlobalEmit',Photo.MediaGUID,'');">
                    <div class="image-view" ng-style="{'background-image':'url('+ImageServerPath+'upload/'+Photo.MediaFolder+'/220x220/'+Photo.ImageName+')'}"></div>
                    <div class="detail-of-media">
                        <ul class="feed-like-nav">
                            <li class="iconlike active">
                                <svg height="16px" width="16px" class="svg-icons">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconLike'}}"></use>
                                </svg>
                            </li>
                            <li class="view-count" ng-bind="Photo.NoOfLikes"></li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>   
        <div class="panel panel-info">     
          <div class="panel-body nodata-panel" ng-cloak ng-if="PhotoTotalRecords==0">
            <div class="nodata-text">
              <span class="nodata-media">
                  <img src="assets/img/empty-img/empty-no-search-results-found.png" >
              </span>
              <h5>No Results Found!</h5>
              <p class="text-off">
                Seems like there are no photos matching your search criteria! <br>Change your search terms, or tweak your filters. 
              </p>
              <a ng-href="<?php echo site_url('dashboard') ?>">Here's something for you to explore!</a>
            </div>
          </div>
        </div>
    </section>
</aside>