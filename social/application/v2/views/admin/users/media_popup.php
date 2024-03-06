<!--Theater Popup -->
<section class="media-popup modal" tabindex="-1" style="display:none;">
  <div class="media-popup-content">
      <aside class="media-left">
          <div class="media-left-content">
              <div class="table-row-img-view">
                  <div class="media-img-view">
                      <i ng-click="toggleHideFullScreen()" class="icon-th-close"></i>
                        <i ng-click="toggleFullScreen()" class="icon-th-fullscreen"></i>
                     <i ng-if="mediaDetails.Album.MediaCount>1 && mediaDetails.MediaIndex<mediaDetails.Album.MediaCount && mediaServiceName=='media/details_all'" class="icon-th-next" ng-click="$emit('showMediaPopupEmit',mediaDetails.NextMediaGUID,'','all');"></i>
                     <i ng-if="mediaDetails.Album.MediaCount>1 && mediaDetails.MediaIndex<mediaDetails.Album.MediaCount && mediaServiceName!=='media/details_all'" class="icon-th-next" ng-click="$emit('showMediaPopupEmit',mediaDetails.NextMediaGUID,'');"><small class="ficon-arrow-right"></small></i>
                        <i ng-if="mediaDetails.Album.MediaCount>1 && mediaDetails.MediaIndex>1 && mediaServiceName=='media/details_all'" class="icon-th-prev" ng-click="$emit('showMediaPopupEmit',mediaDetails.PrevMediaGUID,'','all');"></i>
                        <i ng-if="mediaDetails.Album.MediaCount>1 && mediaDetails.MediaIndex>1 && mediaServiceName!=='media/details_all' && mediaServiceName!=='media/details_all'" class="icon-th-prev" ng-click="$emit('showMediaPopupEmit',mediaDetails.PrevMediaGUID,'');"><small class="ficon-arrow-left2"></small></i>
                    <div class="image-content">
                        <div class="medea-image">
                          <img err-src="{{image_server_path+'assets/img/profiles/user_default.jpg'}}" class="media-image-wrapper" ng-if="showMediaLoader=='0' && mediaDetails.MediaType=='Image'"   ng-src="{{ImageServerPath+'upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.ImageName}}" imageonload="hideLoader()" />
                          <div ng-show="hideMediaLoader=='0' && mediaDetails.MediaType!='Video'" class="loader" ></div>

                          <video width="100%" height="100%" controls="" ng-if="showMediaLoader=='0' && mediaDetails.MediaType=='Video'" class="object">
                              <source type="video/mp4" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.mp4"></source>
                              <source type="video/ogg" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.ogg"></source>
                              <source type="video/webm" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.webm"></source>
                               Your browser does not support HTML5 video.
                          </video>

                        </div>
                      </div>
                  </div>
              </div>

              <div ng-if="mediaDetails.MediaFolder != 'ratings'" class="table-row-info-view">
                  <div  class="media-info" ng-cloak>
                      <div class="col-xs-6">
                          <div class="user-thmb-th pull-left hidden-xs">
                          <figure class="thumb50">
                          <a ng-if="mediaDetails.MediaFolder != 'comments'" ng-href="{{SiteURL+mediaDetails.Album.Owner.ProfileURL}}"  class="loadbusinesscard" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user"  target="_self">

                          <img ng-if="mediaDetails.Album.Owner.ProfilePicture !='' && mediaDetails.Album.Owner.ProfilePicture!='user_default.jpg' " err-src="{{image_server_path+'assets/img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+mediaDetails.Album.Owner.ProfilePicture}}" >

                          <span ng-if="mediaDetails.Album.Owner.ProfilePicture =='' || mediaDetails.Album.Owner.ProfilePicture=='user_default.jpg' " class="default-thumb"><span class="default-thumb-placeholder" ng-bind="getDefaultImgPlaceholder(mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName)"></span></span>

                          </a>

                          <a ng-if="mediaDetails.MediaFolder == 'comments'" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}"  class="loadbusinesscard" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user"  target="_self"><img err-src="{{image_server_path+'assets/img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+mediaDetails.CreatedBy.ProfilePicture}}" ></a>
                          </figure>
                          </div>
                          <div class="media-detail">
                            <a ng-if="mediaDetails.MediaFolder != 'comments'" class="hidden-xs hidden-sm loadbusinesscard"  ng-bind="mediaDetails.Album.Owner.FirstName+' '+mediaDetails.Album.Owner.LastName" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.Album.Owner.ProfileURL}}" target="_self"></a>
                            <a ng-if="mediaDetails.MediaFolder == 'comments'" class="hidden-xs hidden-sm loadbusinesscard"  ng-bind="mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}" target="_self"></a>

                            <span>.</span> <label ng-bind="mediaDetails.Album.AlbumName"></label>  <span ng-bind="mediaDetails.MediaIndex+' of '+mediaDetails.Album.MediaCount">{{currentPicIndex+1}} of {{GallarySize}}</span>
                          </div>
                      </div>
                      <div class="col-xs-6 pull-right">
                          <div class="btn-group pull-right">
                              <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown"><i class="icon-vbullets"></i></button>
                              <ul class="dropdown-menu" role="menu">
                                  <li ng-if="mediaDetails.IsOwner=='0' && mediaDetails.FlagAllowed=='1' && mediaDetails.Flaggable=='1' && mediaDetails.IsFlagged=='0'">
                                  <a ng-click="reportMediaAbuseModal(mediaDetails.MediaGUID);" href="javascript:void(0);">Report Abuse</a></li>
                                  <li>
                                    <a ng-if="mediaDetails.IsSubscribed=='1'" ng-click="mediaSubscribeToggle(mediaDetails.MediaGUID)" href="javascript:void(0);">Unsubscribe</a>
                                    <a ng-if="mediaDetails.IsSubscribed=='0'" ng-click="mediaSubscribeToggle(mediaDetails.MediaGUID)" href="javascript:void(0);">Subscribe</a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1' && mediaDetails.MediaType=='Image'">
                                    <a data-target="#croperUpdate" data-toggle="modal" ng-click="setProfilePicture('<?php echo IMAGE_SERVER_PATH ?>upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.ImageName);" href="javascript:void(0);"> Set as profile picture</a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1' && mediaDetails.MediaType=='Image'">
                                    <a href="javascript:void(0);" ng-click="setProfileCover(mediaDetails.MediaGUID);">Set as cover photo</a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1'">
                                    <a ng-click="deleteMedia(mediaDetails.MediaGUID);" href="javascript:void(0);">Delete</a>
                                  </li>
                              </ul>
                          </div>
                          <ul ng-click="toggleMediaRightSec();" data-type="media-buttons" style="display:none;" class="media-detail-nav pull-right">
                              <li>
                                  <i class="icon-th-like">&nbsp;</i>
                                  <span ng-bind="mediaDetails.NoOfLikes"></span>
                              </li>
                              <li>
                                  <i class="icon-th-comment">&nbsp;</i>
                                  <span ng-bind="mediaDetails.NoOfComments"></span>
                              </li>
                          </ul>
                      </div>
                  </div>
              </div>

          </div>
      </aside>


      <aside class="media-right">
        <div class="media-right-content" data-type="write-comment">
           <div class="post-region" data-type="postRegion">
              <div class="post-view">
                 <ul class="listing">
                    <li>
                      <a class="avtar-48 loadbusinesscard" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}" target="_self" >
                      <img ng-if="mediaDetails.CreatedBy.ProfilePicture!='' && mediaDetails.CreatedBy.ProfilePicture!='user_default.jpg'" ng-src="{{ImageServerPath+'upload/profile/220x220/'+mediaDetails.CreatedBy.ProfilePicture}}" >
                      <span ng-if="mediaDetails.CreatedBy.ProfilePicture=='' || mediaDetails.CreatedBy.ProfilePicture=='user_default.jpg'" class="default-thumb"><span class="default-thumb-placeholder" ng-bind="getDefaultImgPlaceholder(mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName)"></span></span>
                      </a>
                      <div class="user-name">
                        <a class="semi-bold loadbusinesscard" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}" ng-bind="mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName" target="_self"></a>
                      </div>
                      <div class="time-and-privacy">
                          <ul class="list-sub-nav">
                            <li>
                            <span class="time-ago" ng-bind="date_format((mediaDetails.CreatedDate));" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(mediaDetails.CreatedDate))}}"></span>

                            </li>
                            <li class="mar-left-20" ng-cloak ng-if="mediaDetails.ShowPrivacy=='1'">
                              <div class="btn-group custom-icondrop">
                                <div data-ng-if="mediaDetails.IsOwner=='1' && mediaDetails.ModuleID=='3'" class="btn-group custom-icondrop">
                                  <a aria-expanded="false" data-toggle="dropdown" class="drop-icon dropdown-toggle">
                                    <i ng-if="mediaDetails.Visibility=='1'" class="ficon-globe"></i>
<!--                                    <i ng-if="mediaDetails.Visibility=='2'" class="icon-follwers"></i>-->
                                    <i ng-if="mediaDetails.Visibility=='3'" class="ficon-friends"></i>
                                    <i ng-if="mediaDetails.Visibility=='4'" class="ficon-user"></i>
                                    <span class="caret"></span>
                                  </a>
                                  <ul role="menu" class="dropdown-menu dropdown-withicons">
                                    <li><a ng-class="mediaDetails.Visibility=='1' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'1');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-globe"></i></span>Everyone</a></li>
<!--                                    <li><a ng-class="mediaDetails.Visibility=='2' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'2');" href="javascript:void(0);"><span class="mark-icon"><i class="icon-follwers"></i></span>Followers</a></li>-->
                                    <li><a ng-class="mediaDetails.Visibility=='3' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'3');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-friends"></i></span>Friends</a></li>
                                    <li><a ng-class="mediaDetails.Visibility=='4' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'4');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-user"></i></span>Only Me</a></li>
                                  </ul>
                                </div>
                                <a ng-if="mediaDetails.IsOwner=='0' && mediaDetails.ShowPrivacy=='1'" class="normal-cursor dropdown-toggle drop-icon" data-toggle="dropdown">
                                  <i ng-if="mediaDetails.Visibility=='1'" class="ficon-globe"></i>
<!--                                  <i ng-if="mediaDetails.Visibility=='2'" class="icon-follwers"></i>-->
                                  <i ng-if="mediaDetails.Visibility=='3'" class="ficon-friends"></i>
                                  <i ng-if="mediaDetails.Visibility=='4'" class="ficon-user"></i>
                                </a>
                              </div>
                            </li>
                          </ul>
                      </div>
                    </li>
                 </ul>
                 <div class="post-content-view">
                   <p ng-bind-html="mediaDetails.PostContent"></p>
                 </div>
                 <ul class="list-sub-nav">
                   <li ng-if="mediaDetails.IsLike=='0'" ng-click="toggleLike(mediaDetails.MediaGUID)"><a>Like</a></li>
                   <li ng-if="mediaDetails.IsLike=='1'" ng-click="toggleLike(mediaDetails.MediaGUID)"><a>Unlike</a></li>
                   <li ng-if="mediaDetails.MediaFolder!=='comments'"><a href="javascript:void(0);" ng-click="shareMediaDetails(mediaDetails.MediaGUID)" data-toggle="modal" data-target="#sharemediamodal">Share</a></li>
                 </ul>
              </div>
              <div ng-if="mediaDetails.NoOfLikes==1" class="people-like-this">
                  <i class="icon-like-p liked">&nbsp;</i> <a ng-bind="mediaDetails.LikeName.Name"></a>
              </div>
              <div ng-if="mediaDetails.NoOfLikes>1" class="people-like-this">
                  <i class="icon-like-p liked">&nbsp;</i> <a ng-bind="mediaDetails.LikeName.Name"></a> and

                  <a ng-if="mediaDetails.NoOfLikes == 2" 
                     
                     
                     ng-click123="getMediaLikeDetails(mediaDetails.MediaGUID)" 
                     
                     ng-click="likeDetailsEmitMedia(mediaDetails.MediaGUID)" 
                     
                     ng-bind="getRemainingLikes(mediaDetails.NoOfLikes)+' other'"></a>

                  <a ng-if="mediaDetails.NoOfLikes > 2" 
                     
                     ng-click123="getMediaLikeDetails(mediaDetails.MediaGUID)" 
                     
                     ng-click="likeDetailsEmitMedia(mediaDetails.MediaGUID)" 
                     
                     ng-bind="getRemainingLikes(mediaDetails.NoOfLikes)+' others'"></a>


              </div>
              <div class="view-comment">
                <div class="view-all-comment" ng-click="getAllMediaComments(mediaDetails.MediaGUID)" ng-if="mediaDetails.NoOfComments > 10 && mediaDetails.Comments.length<mediaDetails.NoOfComments"><a>View all {{mediaDetails.NoOfComments}} comments</a></div>
                <div class="comment-listing">
                    <ul class="listing">
                        <li  ng-repeat="comment in mediaDetails.Comments">
                            <a class="avtar-48 loadbusinesscard" entityguid="{{comment.UserGUID}}" entitytype="user" ng-href="{{SiteURL+comment.ProfileLink}}" target="_self"><img err-Name={{comment.Name}} ng-src="{{ImageServerPath+'upload/profile/220x220/'+comment.ProfilePicture}}" > </a>
                            <div class="user-name">
                                <a class="semi-bold loadbusinesscard" entityguid="{{comment.UserGUID}}" entitytype="user" ng-href="{{SiteURL+comment.ProfileLink}}" target="_self" ng-bind="comment.Name"></a> <p ng-bind-html="textToLinkComment(comment.PostComment,1)"></p>
                            </div>
                            <!--<div class="media-post" ng-if="comment.Media.length!==0"><img ng-src="{{ImageServerPath+'upload/comments/'+comment.Media.ImageName}}" > </div>-->
                            <div ng-show="(comment.Media && (comment.Media.length > 0))" class="feed-content mediaPost" ng-class="addMediaClasses(comment.Media.length);">
                                <figure class="media-thumbwrap" ng-repeat="( mediaIndex, media ) in comment.Media" ng-if="(mediaIndex <= 3)">
                                    <a class="mediaThumb" image-class="{{addMediaClasses(comment.Media.length)}}"  ng-if="media.ConversionStatus !== 'Pending'" ng-click="$emit('showMediaPopupGlobalEmit', media.MediaGUID, '');">
                                        <i class="icon-n-video-big" ng-if="( media.MediaType == 'Video' )"></i>
                                        <img ng-if="media.MediaType == 'Image'" ng-src="{{ImageServerPath + 'upload/comments/533x300/' + media.ImageName}}" >
                                        <img ng-if="media.MediaType == 'Video'" ng-src="{{ImageServerPath+'upload/comments/533x300/'+ media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" >
                                        <div class="more-content" ng-if="((comment.Media.length > 4) && (mediaIndex === 3))"><span ng-bind="'+' + (comment.Media.length - 4)"></span></div>
                                        <div class="t"></div>
                                        <div class="r"></div>
                                        <div class="b"></div>
                                        <div class="l"></div>
                                    </a>
                                    <!-- Video Process Thumb -->
                                    <div class="post-video" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Pending'">
                                      <div class="wall-video pending-rating-video">
                                          <i class="icon-video-c"></i>
                                      </div>
                                    </div>
                                    <!-- Video Process Thumb -->
                                </figure>
                            </div>
                            <ul ng-show="(comment.Files && (comment.Files.length > 0))" class="attached-files">
                                <li ng-repeat="file in comment.Files">
                                    <span class="file-type {{file.MediaExtension}}">
                                        <svg class="svg-icon" width="26px" height="28px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#fileIcon"></use>
                                        </svg>
                                        <span ng-bind=" '.' + file.MediaExtension"></span>
                                    </span>
                                    <span class="file-name" ng-bind="file.OriginalName"></span>
                                    <i class="dwonload icon hover">
                                        <svg class="svg-icons" width="20px" height="20px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#dwonloadIcon"></use>
                                        </svg>
                                    </i>
                                </li>
                            </ul>
                            <div class="time-and-privacy">
                                <ul class="list-sub-nav">
                                  <li><span class="time-ago" ng-bind="date_format((comment.CreatedDate));" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(comment.CreatedDate));}}"></span> </li>
                                  <li ng-if="comment.IsLike=='0'" ng-click="CommentLike('COMMENT',comment.CommentGUID)"><a>Like</a></li>
                                  <li ng-if="comment.IsLike=='1'" ng-click="CommentLike('COMMENT',comment.CommentGUID)"><a>Unlike</a></li>
                                  <li ng-if="comment.NoOfLikes>0"><i ng-click="getCommentLikeDetails('COMMENT',comment.CommentGUID)" ng-class="comment.IsLike=='1' ? 'icon-statuslike' : 'icon-like-p'">&nbsp;</i> <span class="color-light" ng-if="comment.NoOfLikes>0" ng-bind="comment.NoOfLikes"></span></li>
                                </ul>
                            </div>
                            <a ng-if="comment.CanDelete=='1'" href="javascript:void(0);" class="delete-current-post" ng-click="deleteComment(comment.CommentGUID)"><i class="ficon-cross"></i></a>
                        </li>
                     </ul>
                 </div>
              </div>
          </div>
         <div ng-if="mediaDetails.IsCommentable=='1'" class="post-view-footer" data-type="write-footer">
              <div class="post-write-block">
                    <div class="wall-comments">
                        <div class="textarea-wrap write-comment">
                            <textarea data-ng-init="tagComment(mediaDetails.MediaGUID)" id="cmt-{{mediaDetails.MediaGUID}}" name="write-comment" data-type="autoSize" class="comment-text" placeholder="Write a comment..." ng-model="popupCommentMessage" ng-keypress="addComment($event,mediaDetails.MediaGUID);"></textarea>
                        </div>
                        <div class="attach-on-comment">
                            <span class="icon" ngf-select="uploadFiles($files, $invalidFiles, mediaDetails.MediaGUID, 0, 1)"  ngf-validate-async-fn="validateFileSize($file);" accept=".png, .jpg, .jpeg">
                              <svg class="svg-icons" height="18px" width="18px">
                                  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnAttachment"></use>
                              </svg>
                            </span>
                        </div>
                    </div>

                    
                    
                    <div class="attached-list clearfix" id="attachments-cmt-{{mediaDetails.MediaGUID}}" ng-cloak ng-show="mediaDetails.commentMediaCount > 0">
                        <ul class="attache-listing"> 
                            <li ng-repeat=" ( mediaIndex, media ) in mediaDetails.medias">
                                <img ng-show="media.progress" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" > 
                                <i ng-show="media.progress" class="ficon-cross" ng-click="removeAttachement('media', mediaIndex);"></i>
                                <span ng-hide="media.progress" class="loader" style="display: block;"></span>
                            </li>
                        </ul>
                    </div>
                    <div class="post-file-list" ng-cloak ng-show="mediaDetails.commentFileCount > 0">
                        <ul class="attache-file-list">
                            <li ng-repeat="(fileKey, file) in mediaDetails.files">
                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>
                                <i ng-show="file.progress" class="ficon-file-type" ng-class="file.data.MediaExtension"><span ng-bind="'.' + file.data.MediaExtension"></span></i>
                                <span ng-show="file.progress" class='file-name' ng-bind="file.data.OriginalName"></span>
                                <i class="ficon-cross" ng-show="file.progress" ng-click="removeAttachement('file', fileKey);"></i>
                            </li>
                        </ul>
                    </div>
                  
              </div>
          </div>

        </div>

      </aside>



  </div>
  <img class="no-height" ng-if="mediaDetails.NextImgName" ng-src="{{ImageServerPath+'upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.NextImgName}}" />
  <img class="no-height" ng-if="mediaDetails.PrevImgName" ng-src="{{ImageServerPath+'upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.PrevImgName}}" />
</section>
<!-- Theater Popup -->