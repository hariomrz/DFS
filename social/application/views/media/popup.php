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
                          <img err-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" class="media-image-wrapper" ng-if="showMediaLoader=='0' && mediaDetails.MediaType=='Image'"   ng-src="{{ImageServerPath+'upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.ImageName}}" imageonload="hideLoader()" />
                          <div ng-if="hideMediaLoader=='0' && mediaDetails.MediaType!='Video'" class="loader absolute" ></div>
                          
                          <!-- <video width="100%" height="100%" controls="" ng-if="showMediaLoader=='0' && mediaDetails.MediaType=='Video'" class="object">
                              <source type="video/mp4" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.mp4"></source>
                              <source type="video/ogg" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.ogg"></source>
                              <source type="video/webm" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{mediaDetails.MediaFolder}}/{{mediaDetails.ImageName}}.webm"></source>
                               {{::lang.a_browser_not_support_html5}}
                          </video> -->
                          <div ng-if="showMediaLoader=='0' && mediaDetails.MediaType=='Video'" id="vp-{{mediaDetails.MediaGUID}}" ng-init="initJWPlayerPopup(mediaDetails)"></div>
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

                          <img ng-if="mediaDetails.Album.Owner.ProfilePicture !='' && mediaDetails.Album.Owner.ProfilePicture!='user_default.jpg' " err-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+mediaDetails.Album.Owner.ProfilePicture}}" >

                          <span ng-if="mediaDetails.Album.Owner.ProfilePicture =='' || mediaDetails.Album.Owner.ProfilePicture=='user_default.jpg' " class="default-thumb"><span class="default-thumb-placeholder" ng-bind="getDefaultImgPlaceholder(mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName)"></span></span>

                          </a>

                          <a ng-if="mediaDetails.MediaFolder == 'comments'" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}"  class="loadbusinesscard" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user"  target="_self"><img err-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+mediaDetails.CreatedBy.ProfilePicture}}" ></a>
                          </figure>
                          </div>
                          <div class="media-detail">
                            <a ng-if="mediaDetails.MediaFolder != 'comments'" class="hidden-xs hidden-sm loadbusinesscard"  ng-bind="mediaDetails.Album.Owner.FirstName+' '+mediaDetails.Album.Owner.LastName" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.Album.Owner.ProfileURL}}" target="_self"></a>
                            <a ng-if="mediaDetails.MediaFolder == 'comments'" class="hidden-xs hidden-sm loadbusinesscard"  ng-bind="mediaDetails.CreatedBy.FirstName+' '+mediaDetails.CreatedBy.LastName" entityguid="{{mediaDetails.CreatedBy.UserGUID}}" entitytype="user" ng-href="{{SiteURL+mediaDetails.CreatedBy.ProfileURL}}" target="_self"></a>

                            <span>.</span> <label ng-bind="mediaDetails.Album.AlbumName"></label>  <span ng-bind="mediaDetails.MediaIndex+' '+lang.of+' '+mediaDetails.Album.MediaCount">{{currentPicIndex+1}} {{lang.of}} {{GallarySize}}</span>
                          </div>
                      </div>
                      <div class="col-xs-6 pull-right">
                          <div class="btn-group pull-right">
                              <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown"><i class="icon-vbullets"></i></button>
                              <ul class="dropdown-menu" role="menu">
                                  <li ng-if="mediaDetails.IsOwner=='0' && mediaDetails.FlagAllowed=='1' && mediaDetails.Flaggable=='1' && mediaDetails.IsFlagged=='0'">
                                  <a ng-click="reportMediaAbuseModal(mediaDetails.MediaGUID);" href="javascript:void(0);" ng-bind="lang.a_report_abuse"></a></li>
                                  <li>
                                    <a ng-if="mediaDetails.IsSubscribed=='1'" ng-click="mediaSubscribeToggle(mediaDetails.MediaGUID)" href="javascript:void(0);" ng-bind="lang.unsubscribe"></a>
                                    <a ng-if="mediaDetails.IsSubscribed=='0'" ng-click="mediaSubscribeToggle(mediaDetails.MediaGUID)" href="javascript:void(0);" ng-bind="lang.subscribe"></a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1' && mediaDetails.MediaType=='Image'">
                                    <a data-target="#croperUpdate" data-toggle="modal" ng-click="setProfilePicture('<?php echo IMAGE_SERVER_PATH ?>upload/'+mediaDetails.MediaFolder+'/'+mediaDetails.ImageName);" href="javascript:void(0);" ng-bind="lang.a_set_profile_picture"></a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1' && mediaDetails.MediaType=='Image'">
                                    <a href="javascript:void(0);" ng-click="setProfileCover(mediaDetails.MediaGUID);" ng-bind="lang.a_set_cover_photo"></a>
                                  </li>
                                  <li ng-if="mediaDetails.IsOwner=='1'">
                                    <a ng-click="deleteMedia(mediaDetails.MediaGUID);" href="javascript:void(0);" ng-bind="lang.a_delete"></a>
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
              <div ng-cloak ng-if="mediaDetails.CreatedBy" class="post-view">
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
                                    <li><a ng-class="mediaDetails.Visibility=='1' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'1');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-globe"></i></span>{{::lang.a_everyone}}</a></li>
<!--                                    <li><a ng-class="mediaDetails.Visibility=='2' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'2');" href="javascript:void(0);"><span class="mark-icon"><i class="icon-follwers"></i></span>Followers</a></li>-->
                                    <li><a ng-class="mediaDetails.Visibility=='3' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'3');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-friends"></i></span>{{::lang.a_friends}}</a></li>
                                    <li><a ng-class="mediaDetails.Visibility=='4' ? 'active' : ''" ng-click="setMediaPrivacy(mediaDetails.MediaGUID,'4');" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-user"></i></span>{{::lang.a_only_me}}</a></li>
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
                  <div class="activity-bar p-v-sm">
                    <ul class="feed-actions comment">
                      <li>
                          <a  class="like-btn" data-toggle="tooltip" ng-attr-title="{{(mediaDetails.IsLike=='1') ? 'Unlike' : 'Like' ;}}">
                              <i class="ficon-heart sm" ng-click="toggleLike(mediaDetails.MediaGUID)" ng-class="(mediaDetails.IsLike=='1') ? 'active' : '' ;"></i>
                              <abbr class="sm" 
                                    ng-click123="getMediaLikeDetails(mediaDetails.MediaGUID)" 
                                    
                                    ng-click="likeDetailsEmitMedia(mediaDetails.MediaGUID)"
                                    
                                    ng-if="mediaDetails.NoOfLikes>0" 
                                    
                                    ng-bind="mediaDetails.NoOfLikes"></abbr>
                          </a>
                      </li>
                      <li ng-if="mediaDetails.ShareAllowed=='1' && LoginSessionKey!==''">
                          <a ng-if="mediaDetails.MediaFolder!=='comments'">
                              <a href="javascript:void(0);" ng-click="shareMediaDetails(mediaDetails.MediaGUID)" data-toggle="tooltip" title="Share">
                              <span class="icon">
                                  <i class="ficon-share f-lg"></i>
                              </span>
                          </a>
                      </li>
                    </ul>
                  </div>

              </div>
              <div class="view-comment">
                <div class="view-all-comment" ng-click="getAllMediaComments(mediaDetails.MediaGUID)" ng-if="mediaDetails.NoOfComments > 10 && mediaDetails.Comments.length<mediaDetails.NoOfComments"><a>{{::lang.a_view_all}} {{mediaDetails.NoOfComments}} {{::lang.a_comments}}</a></div>
                <div class="comment-listing">
                    <ul class="listing">
                        <li  ng-repeat="comment in mediaDetails.Comments" class="feed-content">
                            <a class="avtar-48 loadbusinesscard" entityguid="{{comment.UserGUID}}" entitytype="user" ng-href="{{SiteURL+comment.ProfileLink}}" target="_self"><img err-Name={{comment.Name}} ng-src="{{ImageServerPath+'upload/profile/220x220/'+comment.ProfilePicture}}" > </a>
                            <div class="user-name">
                                <a class="semi-bold loadbusinesscard" entityguid="{{comment.UserGUID}}" entitytype="user" ng-href="{{SiteURL+comment.ProfileLink}}" target="_self" ng-bind="comment.Name"></a> <p ng-bind-html="textToLinkComment(comment.PostComment,1)"></p>
                            </div>
                            <!--<div class="media-post" ng-if="comment.Media.length!==0"><img ng-src="{{ImageServerPath+'upload/comments/'+comment.Media.ImageName}}" > </div>-->
                            
                            <ul ng-show="(comment.Files && (comment.Files.length > 0))"  class="attached-files">
                                <li ng-repeat="file in comment.Files" ng-click="hitToDownload(file.MediaGUID)">
                                    <i class="ficon-file-type" ng-class="file.MediaExtension"><span ng-bind="'.' + file.MediaExtension"></span></i>
                                    <span ng-bind="file.OriginalName"></span>
                                </li>
                            </ul>
                            <div ng-show="(comment.Media && (comment.Media.length > 0))"  class="post-media">
                                                                 
                                <div ng-repeat="m in comment.Media| limitTo:4"  ng-class="(comment.Media.length > 2) ? 'col-sm-3' : '' ;">
                                    <figure ng-click="$emit('showMediaPopupGlobalEmit', m.MediaGUID, '');" ng-class="(m.MediaType == 'Video' && m.ConversionStatus == 'Pending' && comment.Media.length > 2) ? 'processing-skyblue' : (m.MediaType == 'Video' && m.ConversionStatus == 'Pending' && (comment.Media.length == 1 || comment.Media.length == 2)) ? 'processing-red' : ''">
                                        <img ng-if="comment.Media.length==1 && m.MediaType !== 'Video' " ng-src="{{ImageServerPath + 'upload/comments/533x300/' + m.ImageName}}">
                                        <img ng-if="comment.Media.length>1 && m.MediaType !== 'Video' " ng-src="{{ImageServerPath + 'upload/comments/533x300/' + m.ImageName}}">
                                        
                                        <img ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Finished'" ng-src="{{ImageServerPath + 'upload/comments/533x300/' + m.ImageName.substr(0, m.ImageName.lastIndexOf('.')) + '.jpg'}}">
                                        <span ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Finished'" class="video-btn">
                                            <i class="ficon-play"></i>
                                        </span>
                                        <span class="video-btn" ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Pending'">
                                            <i class="ficon-video"></i>
                                        </span>
                                        <span ng-if="$index == 3 && comment.Media.length > 4" class="more-content" ng-bind="'+' + (comment.Media.length - 4)"></span>
                                    </figure>
                                </div>
                                
                                
                            </div>
                            
                            <div class="time-and-privacy">
                            </div>
                            <div class="activity-bar p-t-xs">
                              <ul class="feed-actions comment">
                                <li>
                                    <a ng-click="CommentLike('COMMENT',comment.CommentGUID)" class="like-btn" data-toggle="tooltip" title="Like">
                                        <i class="ficon-heart sm" ng-class="(comment.IsLike=='1') ? 'active' : '' ;"></i>
                                        <abbr class="sm" ng-if="comment.NoOfLikes>0" ng-bind="comment.NoOfLikes"></abbr>
                                    </a>
                                </li>
                                <li><span class="time-ago" ng-bind="date_format((comment.CreatedDate));" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(comment.CreatedDate));}}"></span></li>
                              </ul>
                            </div>
                            <a ng-if="comment.CanDelete=='1'" href="javascript:void(0);" class="delete-current-post" ng-click="deleteComment(comment.CommentGUID)"><i class="ficon-cross"></i></a>
                        </li>
                     </ul>
                 </div>
              </div>
          </div>
            
            
            
         <div id="act-{{mediaDetails.MediaGUID}}" ng-if="mediaDetails.IsCommentable=='1'" class="post-view-footer" data-type="write-footer">
              <div class="post-write-block">
                    <div class="wall-comments">
                        <div class="textarea-wrap write-comment">
                            <textarea data-ng-init="tagComment('cmt-'+mediaDetails.MediaGUID)" id="cmt-{{mediaDetails.MediaGUID}}" name="write-comment" data-type="autoSize" class="comment-text" placeholder="Write a comment..." ng-model="popupCommentMessage" ng-keypress="addComment($event,mediaDetails.MediaGUID);"></textarea>
                        </div>

                        <div class="attach-on-comment" ng-if="!mediaDetails.commentMediaCount || mediaDetails.commentMediaCount == 0">                                                        
                            <span class="icon" ngf-select="uploadFiles($files, $invalidFiles, mediaDetails.MediaGUID, 0, 1)" accept=".png, .jpg, .jpeg" ngf-validate-async-fn="validateFileSize($file);">
                              <i class="ficon-attachment"></i>
                            </span>
                        </div>
                    </div>                   
                  
                  <div class="attached-list clearfix" id="attachments-cmt-{{mediaDetails.MediaGUID}}" ng-cloak ng-show="mediaDetails.commentMediaCount > 0">
                        <ul class="attache-listing"> 
                            <li ng-repeat=" ( mediaIndex, media ) in mediaDetails.medias">
                                <img ng-show="media.progress" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" > 
                                <i ng-show="media.progress" class="ficon-cross" ng-click="removeAttachement('media', mediaIndex);"></i>
<!--                                <span ng-hide="media.progress" class="loader" style="display: block;"></span>-->
                                <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                            </li>
                        </ul>
                    </div>
                    <div class="post-file-list" ng-cloak ng-show="mediaDetails.commentFileCount > 0">
                        <ul class="attache-file-list">
                            <li ng-repeat="(fileKey, file) in mediaDetails.files">
<!--                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
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