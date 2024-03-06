<div class="custom-modal" ng-init="getAlbumDetails('<?php echo $AlbumGUID; ?>')">

    <div class="title-row">
        <h4 class="label-title secondary-title">
            <div class="back-arrow-block" ng-click="redirectToSlug('')">
                <i class="icon-md-back-arrow"></i> {{::lang.a_back_caps}}  
            </div>
            <?php if ((isset($IsAdmin) && $IsAdmin == 1) || (isset($IsCreator) && $IsCreator == 1)) { ?>
                <button type="button" ng-click="redirectToSlug('create')" ng-if="Settings.m13 == '1'" class="btn  btn-default btn-sm btn-icon pull-right">
                    <i class="icon-md-plus"></i> {{::lang.a_create_album_caps}} 
                </button>
            <?php } ?>
        </h4>

    </div>
    <div class="row" ng-init="getAlbumDetails('<?php echo $AlbumGUID ?>')" id="act-<?php echo $AlbumGUID ?>">
        <aside class="col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="media-wrapper">
                    <aside class="media-block-header">
                        <ul class="album-nav" ng-if="albumDetails.IsEditable == '1'">
                            <li ng-if="config_detail.IsAdmin" ng-click="redirectToSlug('edit/<?php echo $AlbumGUID ?>')"> <i class="icon-md-edits"></i> </li>
                            <li ng-if="config_detail.IsAdmin" ng-click="deleteAlbum(albumDetails.AlbumGUID, 1)"> <i class="icon-md-delete"></i> </li>
                            <li data-ng-if="albumDetails.ShareAllowed == 1 && albumDetails.Settings.m15 == '1'" ng-click="shareEmit(albumDetails.AlbumGUID);" data-target="#sharemodal" data-toggle="modal"> <i class="icon-md-share"></i> </li>
                        </ul>
                        <div class="heading">
                            <span ng-bind="albumDetails.AlbumName"> </span>

                            <abbr ng-if="albumDetails.IsEditable == '1'"><label>by</label> <a class="loadbusinesscard" entityguid="{{albumDetails.UserGUID}}" entitytype="user" href='{{albumDetails.ProfileUrl}}' ng-bind="albumDetails.UserName"></a></abbr>
                        </div>
                        <ul class="list-sub-nav">
                            <li ng-if="albumDetails.AlbumName != '<?php echo DEFAULT_WALL_ALBUM ?>'">
                                <span>{{albumDetails.MediaCount}}</span> {{::lang.media}}
                            </li>
                            <li ng-if="albumDetails.Location.FormattedAddress" ng-bind="albumDetails.Location.FormattedAddress" ng-cloak></li>

                            <li ng-if="albumDetails.MediaCount > 0">{{::lang.a_updated}} <span ng-bind="timeAgo((albumDetails.ModifiedDate))"></li>
                            <li>
                                <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="Everyone" ng-if="albumDetails.Visibility == 1" class="ficon-globe"></i>
<!--                                <i ng-if="albumDetails.Visibility == 2" class="icon-follwers"></i>-->
                                <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="Friends" ng-if="albumDetails.Visibility == 3" class="ficon-friends"></i>
                                <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="Only Me" ng-if="albumDetails.Visibility == 4" class="ficon-user"></i>
                            </li>
                        </ul>
                        <p ng-if="albumDetails.Description" ng-cloak>{{albumDetails.Description}}</p>
                    </aside>
                    <ul class="albums-listing" id="albummediaul">

                        <!--<li ng-if="albumDetails.IsOwner=='1' && albumDetails.IsEditable=='1'" ng-init="initFineUploader('addAlbumMedia',1);" id="addAlbumMedia" class="create-albums">-->
                        <li ng-if="albumDetails.MediaCount>=1 && albumDetails.IsOwner == '1' && albumDetails.IsEditable == '1'" ngf-select="uploadAlbumMedias($files, $invalidFiles, 1)" multiple ngf-validate-async-fn="validateFileSize($file);" id="addAlbumMedia0" class="create-albums">
                            <figure></figure>
                            <div class="create-album">
                                <i class="icon-md-addphoto"></i>
                                <span ng-bind="lang.a_add_media"></span>
                            </div>
                        </li>
                        <li ng-repeat="( albumMediaLoaderKey, albumMediaLoader ) in albumDetailLoaders track by $index">
                            <!--                                    <div ng-hide="albumMediaLoader.progress" class="active image-holder">
                                                                    <div class="loader loader-attach-file" style="display:block"></div>
                                                                </div>-->
                            <figure></figure>
<!--                            <div class="loader loader-attach-file" style="display:block"></div>-->                            
                            <div ng-if="albumMediaLoader.progressPercentage && albumMediaLoader.progressPercentage < 101" data-percentage="{{albumMediaLoader.progressPercentage}}" upload-progress-bar-cs></div>
                            
                        </li>
                        <!--                                <li ng-repeat="loader in media_loader track by $index" id="file-{{$index}}" rel="file-{{$index}}">
                                                            <figure></figure><div class="spinner48" style="position: absolute;top:50%;left:50%;"></div>
                                                        </li>-->
                        <li ng-repeat="albumMedia in albumMediaList track by $index" id="lg-{{albumMedia.MediaGUID}}">
                            <figure ng-if="albumMedia.MediaType == 'PHOTO'" ng-click="$emit('showMediaPopupGlobalEmit', albumMedia.MediaGUID, '');">
                                <div class="image-view" style="background-image:url('<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.MediaSectionAlias}}/220x220/{{albumMedia.ImageName}}')"></div>
                            </figure>

                            <span class="media-video" ng-if="albumMedia.MediaType == 'VIDEO'"><i class="ficon-video"></i></span>
                            <figure ng-class="{'videoprocess':albumMedia.ConversionStatus != 'Finished'}" ng-if="albumMedia.MediaType == 'VIDEO'" ng-click="$emit('showMediaPopupGlobalEmit', albumMedia.MediaGUID, '');">
                                <div class="image-view" style="background-image:url('<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.MediaSectionAlias}}/220x220/{{albumMedia.FileName}}.jpg')"></div>
                            </figure>
                            <div id="m-{{albumMedia.MediaGUID}}">
                                <video width="100%" height="100%" controls="" style="display:none" ng-if="albumMedia.MediaType == 'VIDEO'" class="object">
                                    <source type="video/mp4" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.MediaSectionAlias}}/{{albumMedia.FileName}}.mp4"></source>
                                    <source type="video/ogg" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.MediaSectionAlias}}/{{albumMedia.FileName}}.ogg"></source>
                                    <source type="video/webm" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.MediaSectionAlias}}/{{albumMedia.FileName}}.webm"></source>
                                    {{::lang.a_browser_not_support_html5}}
                                </video>
                            </div>

                            <div class="media-list-footer">
                                <div class="album-like-comment">
                                    <div class="lke-cmnt-inner">
                                        <i class="icon-md-like"></i>
                                        <span class="count-view" ng-bind="albumMedia.NoOfLikes"></span>
                                    </div>
                                    <div class="lke-cmnt-inner">
                                        <i class="icon-md-comment"></i>
                                        <span class="count-view" ng-bind="albumMedia.NoOfComments"></span>
                                    </div>
                                </div>
                                <div class="action-album">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown">
                                        <i class="icon-vbullets"></i></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li ng-if="albumMedia.MediaType == 'PHOTO'"><a href="javascript:void(0);" ng-click="setProfilePicture('<?php echo IMAGE_SERVER_PATH ?>upload/' + albumMedia.MediaSectionAlias + '/' + albumMedia.ImageName, albumMedia.MediaGUID);" href="javascript:void(0);" data-toggle="modal" data-target="#croperUpdate">{{::lang.a_set_profile_picture}}</a></li>
                                        <li ng-if="albumMedia.MediaType == 'PHOTO'"><a href="javascript:void(0);" ng-click="setProfileCover(albumMedia.MediaGUID);">{{::lang.a_set_cover_photo}}</a></li>
                                        <li ng-if="albumMedia.MediaType == 'PHOTO' && albumDetails.IsEditable == 1"><a href="javascript:void(0);" ng-if="albumMedia.IsCoverMedia != 1" ng-click="setAsAlbumCover(albumMedia.MediaGUID, albumDetails.AlbumGUID, $index);">{{::lang.a_set_album_cover}}</a></li>
                                        <li ng-if="albumDetails.IsOwner == '1'"><a href="javascript:void(0);" ng-click="deleteMedia(albumMedia.MediaGUID, 1);">{{::lang.a_delete}}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="nodata-panel" ng-if="albumDetails.MediaCount <= 0" ng-cloak>
                        <div class="nodata-text">
                          <span class="nodata-media">
                              <img src="{{AssetBaseUrl}}img/empty-img/empty-media.png" >
                          </span>
                          <h5>{{::lang.a_short_on_media}}</h5>
                          <p ng-if="albumDetails.IsOwner == '1' && albumDetails.IsEditable == '1'" ng-cloak class="text-off">
                            {{::lang.a_why_dont_upload_picture}} 
                          </p>
                          <a ng-if="albumDetails.IsOwner == '1' && albumDetails.IsEditable == '1'" ngf-select="uploadAlbumMedias($files, $invalidFiles, 1)" multiple ngf-validate-async-fn="validateFileSize($file);">{{::lang.a_upload_media}}</a>
                        </div>
                    </div>
                    <div class="media-footer-section" ng-if="albumDetails.MediaCount > 0">
                        
                        <div class="post-panel-bottom">              
                            <div class="activity-bar">
                                <ul class="feed-actions">
                                    <li>
                                        <span class="like-btn">
                                            <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="{{(albumDetails.IsLike == '1') ? 'Unlike' : (albumDetails.NoOfLikes=='0') ? 'Be the first to like' : 'Like' ;}}" ng-click="toggleEntityLike('ALBUM', albumDetails.AlbumGUID)" ng-class="albumDetails.IsLike == '1' ? 'ficon-heart active' : 'ficon-heart'" ></i>
                                            <abbr ng-if="albumDetails.NoOfLikes > 0" ng-bind="albumDetails.NoOfLikes" ng-click="$emit('likeDetailsEmit', albumDetails.AlbumGUID, 'ALBUM');"></abbr>                                          
                                        </span>
                                    </li>
                                    <li ng-if="albumDetails.NoOfComments > 0">
                                        <a ng-bind="'Comments (' + albumDetails.NoOfComments + ')'"></a>
                                    </li>
                                    <li data-toggle="tooltip" data-original-title="Share" ng-cloak ng-if="albumDetails.ShareAllowed == 1 && albumDetails.Settings.m15 == '1'" class="cursor-pointer">                                        
                                        <a type="button" ng-click="shareEmit(albumDetails.AlbumGUID);" data-target="#sharemodal" data-toggle="modal">
                                            <span class="icon">
                                                <i class="ficon-share f-mlg"></i>
                                            </span>
                                        </a>

                                    </li>      
                                </ul>
                            </div>
                        </div>
                        
                        <div class="feed-footer">
                            <div class="load-more load-more-comment" ng-if="(albumDetails.NoOfComments > 2 && albumDetails.Comments.length < albumDetails.NoOfComments)">
                                <a  ng-if="albumDetails.Comments.length < albumDetails.NoOfComments"  data-ng-click="viewAllComntEmit(albumDetails.AlbumGUID);" ng-bind="'See all ' + albumDetails.NoOfComments + ' comments'"></a>
                            </div>
                            <div class="row">
                                <div class="col-sm-8">
                                    <ul class="listing-group list-group-close" ng-if="albumDetails.NoOfComments > 0">
                                        <li ng-repeat="comnt in albumDetails.Comments">
                                            <div class="list-items-sm">
                                                <i class="ficon-cross remove-list" ng-if="comnt.CanDelete == '1' || albumDetails.CanRemove" ng-click="$emit('deleteCommentEmit', comnt.CommentGUID, albumDetails.AlbumGUID);">&nbsp;</i>
                                                <div class="list-inner">
                                                    <figure>
                                                        <a ng-href="{{albumDetails.SiteURL + comnt.ProfileLink}}" class="loadbusinesscard" entityguid="{{comnt.UserGUID}}" entitytype="user">
                                                            <img err-Name="{{comnt.Name}}"   class="img-circle" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/220x220/' + comnt.ProfilePicture}}">
                                                        </a>
                                                    </figure>

                                                    <div class="list-item-body">
                                                        <div>
                                                            <span ng-bind-html="getCommentTitle(comnt.Name, comnt.ProfileLink, comnt.ModuleID, comnt.UserGUID)"></span>
                                                            <span ng-bind-html="textToLinkComment(comnt.PostComment)"></span>
                                                        </div>
                                                       
                                                        <div ng-if="comnt.Media && comnt.Media.length > 0" ng-class="getMediaClass(comnt.Media)">
                                                            <div ng-repeat="m in comnt.Media| limitTo:4" ng-class="(comnt.Media.length > 2) ? 'col-sm-3' : '' ;">
                                                                <figure>
                                                                    <img ng-click="$emit('showMediaPopupGlobalEmit', m.MediaGUID, '');" ng-src="{{ImageServerPath + 'upload/comments/' + m.ImageName}}">
                                                                    <span ng-if="$index == 3 && comnt.Media.length > 4" class="more-content" ng-bind="'+' + comnt.Media.length - 4"></span>
                                                                </figure>
                                                            </div>
                                                        </div>

                                                        <div ng-if="(comnt.Files && (comnt.Files !== '') && (comnt.Files.length > 0))" class="post-media">
                                                            <ul class="attached-files">
                                                                <li ng-repeat="file in comnt.Files" ng-click="hitToDownload(file.MediaGUID)">
                                                                    <i class="ficon-file-type" ng-class="file.MediaExtension"><span ng-bind="'.' + file.MediaExtension"></span></i>
                                                                    <span ng-bind="file.OriginalName"></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        
                                                        <div class="activity-bar p-t-sm">
                                                            <ul class="feed-actions comment">
                                                                <li>
                                                                    <span class="like-btn" tooltip data-container="body" data-placement="top" ng-attr-data-original-title="{{(comnt.IsLike == '1') ? 'Unlike' : 'Like' ;}}">
                                                                        <i data-ng-click="toggleEntityLike('COMMENT', comnt.CommentGUID);" class="ficon-heart sm" ng-class="(comnt.IsLike) ? 'active' : '';"></i>
                                                                        <abbr ng-if="comnt.NoOfLikes > 0" ng-bind="comnt.NoOfLikes" ng-click="$emit('likeDetailsEmit', comnt.CommentGUID, 'COMMENT');" class="sm"></abbr>
                                                                    </span>
                                                                </li>  
                                                                <li><span tooltip data-container="body" data-placement="top" ng-attr-data-original-title="{{getTimeFromDate(UTCtoTimeZone(comnt.CreatedDate));}}" ng-bind="date_format(comnt.CreatedDate, 'MMM D, YYYY');"></span></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="post-comments" data-ng-if="albumDetails.CommentsAllowed == '1'">
                                        <div ng-if="albumDetails.ActivityType == 'PagePost'" class="user-thmb" style="display:none">
                                            <img class="img-circle show-pic" alt="User" ng-if="albumDetails.IsEntityOwner == 0 && albumDetails.ModuleEntityOwner == 0 && LoggedInProfilePicture !== ''" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/' + LoggedInProfilePicture}}">
                                            <img class="img-circle show-pic" alt="User" ng-if="albumDetails.IsEntityOwner == 1 && albumDetails.ModuleEntityOwner == 0 && LoggedInProfilePicture !== ''" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/' + LoggedInProfilePicture}}">
                                            <img class="img-circle show-pic" alt="User" ng-if="albumDetails.IsEntityOwner == 1 && albumDetails.ModuleEntityOwner == 1 && albumDetails.UserProfilePicture !== ''" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/' + albumDetails.UserProfilePicture}}">
                                            <img class="img-circle show-pic" alt="User" ng-if="albumDetails.IsEntityOwner == 0 && albumDetails.ModuleEntityOwner == 1 && albumDetails.UserProfilePicture !== ''" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/' + LoggedInProfilePicture}}">
                                            <img ng-if="albumDetails.IsOwner == 0 && LoggedInProfilePicture == ''" ng-src="{{albumDetails.CurrentProfilePic}}" ng-init="getCurrentProfilePic()" class="img-circle current-profile-pic" />
                                        </div>
                                        <div ng-cloak ng-if="LoginSessionKey!=='' && albumDetails.ActivityType != 'PagePost'" class="user-thmb" style="display:none">
                                            <img class="img-circle show-pic" alt="User" err-Name="{{LoggedInName}}" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/' + LoggedInProfilePicture}}">
                                        </div>
                                        <div ng-cloak ng-if="LoginSessionKey=='' && albumDetails.ActivityType != 'PagePost'" class="user-thmb" style="display:none">
                                            <img class="img-circle show-pic" alt="User" ng-src="{{albumDetails.ImageServerPath + 'upload/profile/36x36/user_default.jpg'}}">
                                        </div>
                                        <div class="wall-comments">

                                            <div class="textarea-wrap">
                                                <textarea custom-comment-box ng-init="tagComment('cmt-' + albumDetails.AlbumGUID)" id="cmt-{{albumDetails.AlbumGUID}}" data-ng-keypress="$emit('commentEmit', $event, albumDetails.AlbumGUID);" class="form-control comment-text tagged_text" placeholder="Write Comment"></textarea> 
                                            </div>
                                            <div class="attach-on-comment"> 
                                                <span class="icon" ngf-select="uploadFiles($files, $invalidFiles, albumDetails.AlbumGUID)" multiple ngf-validate-async-fn="validateFileSize($file);">
                                                     <i class="ficon-attachment"></i>
                                                </span>
                                            </div> 
                                        </div>
                                        <span class="post-help-text" style="display:none;">{{::lang.a_shift_return_newline}}</span> 
                                        <div class="attached-list clearfix" id="attachments-cmt-{{albumDetails.AlbumGUID}}" ng-cloak ng-show="albumDetails.commentMediaCount > 0">
                                            <ul class="attache-listing"> 
                                                <li ng-repeat=" ( mediaIndex, media ) in albumDetails.medias">
                                                    <img ng-show="media.progress" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" > 
                                                    <i ng-show="media.progress" class="ficon-cross" ng-click="removeAttachement('media', mediaIndex);"></i>
<!--                                                    <span ng-hide="media.progress" class="loader" style="display: block;"></span>-->
                                                    <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="post-file-list" ng-cloak ng-show="albumDetails.commentFileCount > 0">
                                            <ul class="attache-file-list">
                                                <li ng-repeat="(fileKey, file) in albumDetails.files">
<!--                                                    <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                                    <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                                    <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                                    <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                                    <i class="ficon-cross" ng-show="file.progress" ng-click="removeAttachement('file', fileKey);"></i>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>

                            </div>
                        
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<input type="hidden" id="shareCommentSettings" value="0" />
<input type="hidden" id="ShareModuleEntityGUID" value="" />
<input type="hidden" id="ShareEntityUserGUID" value="" />
<div class="modal fade" id="sharemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel">{{::lang.a_share_this_post}}</h4>
            </div>
            <div class="modal-body share-modal-body scrollbar">
                <div class="share-content-top">
                    <div class="col-sm-6 col-md-6 col-xs-12">
                        <div class="text-field-select">
                            <select id="sharetype" onChange="changePopupShare(this.value)" data-chosen="" data-disable-search="true">
                                <option value="own-wall">On your own wall</option>
                                <option value="friend-wall">On a friend's wall</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="own-wall share-wall">
                    <div class="share-content-bottom">
                        <div class="hide comments about-media about-name">
                            <input type="text" class="form-control" id="friend-src" placeholder="Friend's name" value="" />
                        </div>
                        <div id="FriendSearchResult"></div>
                        <div class="comments about-media">
                            <textarea class="form-control" id="PCnt" placeholder="Say something about this post"></textarea>
                        </div>
                        <div class="media-block mediaPost media-photo" ng-class="layoutClass(albumMediaList)" ng-if="albumMediaList != undefined && albumMediaList !== ''">
                            <figure class="media-thumbwrap" ng-repeat="meida in albumMediaList| limitTo:5" repeat-done="callImgF()"> <a href="javascript:void(0);" ng-class="albumMediaList.length>1 ? 'imgFill' : 'singleImg' ;" class="media-thumb media-thumb-fill"> <img ng-src="{{ImageServerPath + 'upload/' + meida.MediaSectionAlias + '/220x220/' + getThumbImage(meida.ImageName)}}" /> </a> </figure>
                        </div>
                        <div class="share-content">
                            <div class="share-inr-space tagging"> <a href="javascript:void(0);" ng-bind="albumDetails.UserName"></a>
                                <p ng-bind-html="textToLink(albumDetails.PostContent)"></p>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <div class="modal-footer">
                <div class="pull-right wall-btns" ng-init="SharePrivacyClass='ficon-globe'">
                    <!-- Privacy Buttons -->                    
                    <button id="shareComment" class="own-wall-settings btn btn-default btn-icon btn-onoff on" type="button"> <i class="ficon-comment f-lg"></i> <span>On</span> </button>
                    <div class="btn-group custom-icondrop own-wall-settings own-wall-privacy">
                        <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle drop-icon" type="button"> <i ng-class="SharePrivacyClass"></i> <span class="caret"></span> </button>
                        <ul role="menu" class="dropdown-menu pull-left dropdown-withicons">
                            <li><a onClick="$('#shareVisibleFor').val(1);" href="javascript:void(0);" ng-click="SharePrivacyClass='ficon-globe'"><span class="mark-icon"><i class="ficon-globe"></i></span>{{::lang.a_everyone}}</a></li>
<!--                            <li><a onClick="$('#shareVisibleFor').val(2);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-follwers"></i></span>Followers</a></li>-->
                            <li><a onClick="$('#shareVisibleFor').val(3);" href="javascript:void(0);" ng-click="SharePrivacyClass='ficon-friends'"><span class="mark-icon"><i class="ficon-friends"></i></span>{{::lang.a_friends}}</a></li>
                            <li><a onClick="$('#shareVisibleFor').val(4);" href="javascript:void(0);" ng-click="SharePrivacyClass='ficon-user'"><span class="mark-icon"><i class="ficon-user"></i></span>{{::lang.a_only_me}}</a></li>
                        </ul>
                    </div>
                    <!-- Privacy Buttons -->
                    <button class="btn btn-primary" ng-click="shareActivity()" type="button">{{::lang.a_share}}</button>
                </div>
            </div>
        </div>
    </div>

</div>

<div ng-include="like_details_modal_tmplt"></div>