<div class="panel-footer panel-footer-comments" ng-show="data.Comments.length>0 && data.CommentsAllowed==1">
    <div class="load-comments" ng-if="data.NoOfComments > 2 && data.Comments.length < data.NoOfComments" data-ng-click="viewAllComntEmit(FeedIndex, data.ActivityGUID);">
        <a class="view-comments">
            <span ng-bind="data.NoOfComments"></span>
            <span class="text">Comments</span>
            <span class="caret"></span>
        </a>
    </div>
    <div class="comment-on-post">
        <ul class="list-group list-group-thumb sm">
            
            <li class="list-group-item" ng-repeat="comnt in data.Comments|orderBy:'-BestAnswer' track by comnt.CommentGUID">
                <div class="list-group-body">
                    <figure class="list-figure">
                        <a ng-cloak ng-href="{{base_url + comnt.ProfileLink}}" class="ng-thumb-30 loadbusinesscard" entitytype="user" entityguid="{{comnt.UserGUID}}"><img err-Name="{{comnt.Name}}"   ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + comnt.ProfilePicture}}"></a>
                    </figure>
                    <div class="list-group-content">
                        <h5 class="list-group-item-heading">                                               
                            <a ng-href="{{base_url + comnt.ProfileLink}}" ng-bind="comnt.Name">Leon Yates</a>
                        </h5>
                        <ul class="list-activites">
                            <li ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(comnt.CreatedDate));}}" ng-bind="date_format((comnt.CreatedDate));"></li>
                        </ul>
                    </div>
                    <p class="list-group-item-text" ng-bind-html="textToLinkComment(comnt.PostComment)"></p>
                    <ul ng-show="(comnt.Files && (comnt.Files.length > 0))" class="attached-files">
                        <li ng-repeat="file in comnt.Files" ng-click="hitToDownload(file.MediaGUID, 'comments');">
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
                    <div ng-show="(comnt.Media && (comnt.Media.length > 0))" class="feed-content mediaPost" ng-class="addMediaClasses(comnt.Media.length);">
                        <figure class="media-thumbwrap" ng-repeat="( mediaIndex, media ) in comnt.Media" ng-if="(mediaIndex <= 3)">
                            <a class="mediaThumb" image-class="{{addMediaClasses(comnt.Media.length)}}" ng-if="media.ConversionStatus !== 'Pending'" ng-click="$emit('showMediaPopupGlobalEmit', media.MediaGUID, '');">
                                <i class="icon-n-video-big" ng-if="((media.MediaType == 'Video') && (media.ConversionStatus == 'Finished'))"></i>
                                <img ng-if="media.MediaType == 'Image'" ng-src="{{data.ImageServerPath + 'upload/comments/533x300/' + media.ImageName}}" >
                                <img ng-if="media.MediaType == 'Video'" ng-src="{{data.ImageServerPath + 'upload/comments/533x300/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" >
                                <div class="more-content" ng-if="((comnt.Media.length > 4) && (mediaIndex === 3))"><span ng-bind="'+' + (comnt.Media.length - 4)"></span></div>
                                <div class="t"></div>
                                <div class="r"></div>
                                <div class="b"></div>
                                <div class="l"></div>
                            </a>
                            <div class="post-video" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Pending'">
                                <div class="wall-video pending-rating-video">
                                    <i class="icon-video-c"></i>
                                </div>
                            </div>
                        </figure>
                    </div>
                    <div class="list-group-footer">
                        <ul class="list-group-inline pull-left">
                            <li>
                                <a class="bullet" ng-class="(comnt.IsLike == 1) ? 'active' : '';" data-ng-click="likeEmit(comnt.CommentGUID, 'COMMENT', data.ActivityGUID);">
                                    <i class="ficon-heart"></i>
                                </a>
                                <a class="text" ng-bind="comnt.NoOfLikes"></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>

        </ul>
    </div>
</div>


<div class="feed-footer" id="cmt-div-{{data.ActivityGUID}}" ng-if="data.IsDeleted == 0 && data.CommentsAllowed == 1 && data.StatusID !== '10'">
    <span class="place-holder-label" ng-click="postCommentEditor(data.ActivityGUID,FeedIndex)" ng-bind="(data.PostType == '2') ? 'Write an answer...' : 'Write a comment...'"></span>
    <div class="comment-section hide" ng-cloak>
        <div class="loader commentEditorLoader" style="top:45%;">&nbsp;</div>
        <div class="current-selected">
            <span class="icon" ng-if="data.PostContent!==''" ng-click="insert_to_editor(data.ActivityGUID,data.PostContent,FeedIndex)">
                <button class="btn btn-default btn-sm">Quote Post</button>
            <!-- <svg height="16px" width="16px" class="svg-icons">
              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnQuote" ng-href="assets/img/sprite.svg#icnQuote"></use>
            </svg> -->

          </span>
        </div>
        <summernote on-keyup="breakquote(evt); checkEditorData(evt, FeedIndex);" data-posttype="Comment" data-guid="{{data.ActivityGUID}}" on-image-upload="imageUpload(files)" id="cmt-{{data.ActivityGUID}}" config="commentOptions" placeholder="Write a comment" on-focus="focus(evt)" editable="editable" editor="editor" on-blur="CheckBlur(data.ActivityGUID)" on-change="saveRange(data.ActivityGUID);">
        </summernote>
        <!-- Post Action -->
        <div class="post-actions clearfix  border-top">
            <div class="media-upload-view" id="attachments-cmt-{{data.ActivityGUID}}" ng-cloak ng-show="(data.commentMediaCount > 0) || (data.commentFileCount > 0)">
                <ul class="attached-list" id="listingmedia">
                    <li class="photo-itm media-item" ng-repeat=" ( mediaIndex, media ) in data.medias">
                        <div ng-hide="media.progress" class="loader" style="display: block;"></div>
                        <span ng-if="(media.data.MediaType == 'VIDEO')" ng-show="media.progress" class="videoprocess" style="background: #ddd;"></span>
                        <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" >
                        <i ng-show="media.progress" class="icon-n-close-w" ng-click="removeAttachement('media', mediaIndex, FeedIndex);"></i>
                    </li>
                </ul>
                <ul class="attached-files ">
                    <li ng-repeat="( fileIndex, file ) in data.files">
                        <div ng-hide="file.progress" class="loader" style="display: block;"></div>
                        <span ng-show="file.progress" class="file-type {{file.data.MediaExtension}}">
                                <svg class="svg-icon" width="26px" height="28px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#fileIcon"></use>
                                </svg> 
                                <span ng-bind="'.' + file.data.MediaExtension"></span>
                        </span>
                        <span ng-show="file.progress" class="file-name" ng-bind="file.data.OriginalName"></span>
                        <i ng-show="file.progress" class="dwonload icon hover" ng-click="removeAttachement('file', fileIndex, FeedIndex);">
                                <svg class="svg-icons" width="20px" height="20px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIcon"></use>
                                </svg>
                            </i>
                    </li>
                </ul>
            </div>
            <div class="post-footer">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <div class="pull-right wall-btns">
                            <ul class="wall-action-btn">
                                <li class="icon-btn">
                                    <button ngf-select="uploadFiles($files, $invalidFiles, data.ActivityGUID, FeedIndex, 0, 1)" accept=".png, .jpg, .jpeg" ngf-validate-async-fn="validateFileSize($file);" type="button" class="btn btn-default" onclick="$('#fileAttach').trigger('click');">
                                        <span class="icon">
                                                    <svg class="svg-icons" height="20px" width="20px">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnAttachment"></use>
                                                    </svg>
                                                </span>
                                    </button>
                                </li>
                                <li>
                                    <button id="PostBtn-{{data.ActivityGUID}}" data-ng-click="commentEmit($event, data.ActivityGUID, FeedIndex, '.feed-act-' + data.ActivityGUID + ' ')" class="btn btn-primary p-h loader-btn" type="button">Post </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--// Post Action -->
    </div>
    <div class="clearfix"></div>
</div>