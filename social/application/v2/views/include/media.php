<div class="news-feed-listing" ng-cloak ng-show="user_media.length>0" ng-init="get_entity_media();">
    <div class="feed-heading">
        <h3 class="panel-title border-bottom">
            <span class="svg-icon">
                <svg width="20px" height="20px" class="svg-icons">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnImageArea"></use>
                </svg>
            </span>
            MEDIA
        </h3>
    </div>
    <div class="feed-body">
        <ul class="media-listing row">
            <li class="media-thumbwrap col-sm-3 col-xs-6" ng-repeat="um in user_media" ng-click="$emit('showMediaPopupGlobalEmit',um.MediaGUID,'','all');">
                <div class="recent-media-listing">
                    <img ng-if="um.MediaType!=='Video'" ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/'+um.MediaFolder+'/220x220/'+um.ImageName}}"  /> 
                    <img ng-if="um.MediaType=='Video' && um.ConversionStatus=='Finished'"   ng-src="<?php echo IMAGE_SERVER_PATH ?>{{'upload/'+um.MediaFolder+'/220x220/'+um.ImageName.substr(0, um.ImageName.lastIndexOf('.')) + '.jpg'}}" /> 
                    <i class="icon-n-video-big" ng-if="um.MediaType=='Video' && um.ConversionStatus=='Finished'"></i> 
                    <div class="post-video" ng-if="um.MediaType=='Video' && um.ConversionStatus=='Pending'">
                      <div class="wall-video pending-rating-video">
                          <i class="icon-video-c"></i>
                      </div>  
                    </div>
               </div>  
            </li>
        </ul>
    </div>
</div>