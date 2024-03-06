<div ng-cloak class="news-feed-listing" id="StickyPostController" ng-controller="StickyPostController as StickyPostCtrl" ng-init="StickyPostCtrl.getStickyPostList();">
    <div class="panel-heading p-heading">
        <h3>
                                    {{lang.w_sticky_posts}}  (<span ng-bind="StickyPostCtrl.stickyPostList.length"></span>)
                                    <div class="pull-right">                              
                                        <a target="_self" data-toggle="collapse" href="#stickyListing" class="accordion-icon">
                                            <i class="ficon-arrow-left-sml f-lg"></i>
                                        </a>
                                    </div>
                                </h3>
    </div>
    <div class="sticky-content">
        <div id="stickyListing" class="collapse in">
            <div class="repate-sticky" ng-repeat="( stickyIndex, sticky ) in StickyPostCtrl.stickyPostList">
               <span class="sticky"><i class="ficon-pin rotate-45"></i></span>
               <div class="list-items-xs"> 
                      <div class="list-inner">
                       <figure class="thumb-48">
                            <img ng-if="( sticky.ProfilePicture != '' )" ng-src="{{ StickyPostCtrl.ImageServerPath + 'upload/profile/220x220/' + sticky.ProfilePicture }}" class="img-circle"  >
                            <span ng-if="sticky.ProfilePicture=='' || sticky.ProfilePicture=='user_default.jpg' " class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(sticky.Name)"></span></span>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs">
                                <a target="_self" class="ellipsis" ng-bind="sticky.Name" ng-href="{{ StickyPostCtrl.baseURL + sticky.ProfileURL }}"></a>
                            </h4>
                            <small>11 Dec at 9:03 AM</small>
                          </div>
                      </div>
                  </div>
                <div class="feed-body"> 
                    <div class="feed-content" ng-if="( sticky.Album.length && sticky.Album[0].Media.length )">
                        {{stickyMedia = sticky.Album[0].Media[0];""}}
                        <figure class="media-thumbwrap" ng-if="stickyMedia.ConversionStatus != 'Pending'" ng-click="$emit('showMediaPopupGlobalEmit', stickyMedia.MediaGUID, '');">
                            <a target="_self" class="mediaThumb">
                                <!--<img src="assets/img/wall-images/002.jpg" >-->
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName!=='Wall Media' && stickyMedia.MediaType=='Image'"   ng-src="{{StickyPostCtrl.ImageServerPath+'upload/album/750x500/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName=='Wall Media' && stickyMedia.MediaType=='Image'"   ng-src="{{StickyPostCtrl.ImageServerPath+'upload/wall/750x500/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName!=='Wall Media' && stickyMedia.MediaType=='Video' && stickyMedia.ConversionStatus=='Finished'"   ng-src="{{StickyPostCtrl.ImageServerPath+'upload/album/750x500/'+  stickyMedia.ImageName.substr(0, stickyMedia.ImageName.lastIndexOf('.')) + '.jpg' }}" />
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName=='Wall Media' && stickyMedia.MediaType=='Video' && stickyMedia.ConversionStatus=='Finished'"   ng-src="{{StickyPostCtrl.ImageServerPath+'upload/wall/750x500/'+ stickyMedia.ImageName.substr(0, stickyMedia.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                <img ng-if="sticky.ActivityType=='ProfilePicUpdated'" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/profile/220x220/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType=='ProfileCoverUpdated'" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/profilebanner/1200x300/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName!=='Wall Media' && stickyMedia.MediaType=='Image'" style="width:1px;" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/album/750x500/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType!='ProfilePicUpdated' && sticky.ActivityType!='ProfileCoverUpdated' && sticky.Album[0].AlbumName=='Wall Media' && stickyMedia.MediaType=='Image'" style="width:1px;" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/wall/750x500/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType=='ProfilePicUpdated'" style="width:1px;" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/profile/220x220/'+stickyMedia.ImageName}}" />
                                <img ng-if="sticky.ActivityType=='ProfileCoverUpdated'" style="width:1px;" ng-src="{{StickyPostCtrl.ImageServerPath+'upload/profilebanner/1200x300/'+stickyMedia.ImageName}}" />
                                <i class="icon-n-video-big" ng-if="stickyMedia.MediaType=='Video' && stickyMedia.ConversionStatus=='Finished'"></i>
                            </a>
                        </figure>
                        <!-- Video Process Thumb -->
                        <div class="post-video" ng-if="stickyMedia.MediaType=='Video' && stickyMedia.ConversionStatus == 'Pending'">
                            <div class="wall-video pending-rating-video">
                                <i class="icon-video-c"></i>
                            </div>
                        </div>
                    </div>
                    <ul class="attached-files" ng-if="( sticky.Files.length )">
                        {{stickyFile = sticky.Files[0];""}}
                        <li ng-click="hitToDownload(stickyFile.MediaGUID);">
                            <i ng-class="'ficon-file-type '+stickyFile.MediaExtension"><span ng-bind="'.'+stickyFile.MediaExtension"></span></i>
                            <span ng-bind="stickyFile.OriginalName"></span>
                        </li>
                    </ul>
                    {{sticky.PostContent = StickyPostCtrl.createSharedPostContent(sticky);""}}
                    <p ng-if="sticky.PostContent" ng-bind-html="textToLink(sticky.PostContent, true)"></p>
                    <div class="feed-post-activity">
                        <ul class="feed-like-nav">
                            <li ng-class="(sticky.IsLike == '1') ? 'active' : '' ;" class="iconlike" tooltip data-placement="top" ng-attr-data-original-title="{{(sticky.IsLike == '1') ? 'Dislike' : 'Like' ;}}">
                                <span ng-disabled="sticky.IsDeleted == 1" ng-click="likeEmit(sticky.ActivityGUID, 'ACTIVITY', sticky.ActivityGUID); changeLikeStatus(sticky)">
                                <svg height="16px" width="16px" class="svg-icons">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconLike'}}"></use>
                                </svg>
                            </span>
                            </li>
                            <li class="view-count" ng-if="sticky.NoOfLikes>0" ng-bind="sticky.NoOfLikes"></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="feed-footer">
                <div class="post-comments">
                    <div class="user-thmb" style="display:none;"><img class="img-circle" src="../assets/img/dummythm1.jpg"  /></div>
                    <div class="wall-comments">
                        <div class="textarea-wrap">
                            <textarea class="comment-text tagged_text" placeholder="Write Comment"></textarea>
                        </div>
                        <div class="attach-on-comment">
                            <span class="icon">
                                <i class="ficon-attachment"></i>
                           </span>
                            <input type="file" name="">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
