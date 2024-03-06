<div ng-if="LoginSessionKey" class="modal fade" tabindex="-1" role="dialog" id="welcomeUserPopup" data-backdrop="static">
    <div ng-show="popupSettings.steps==1" class="modal-dialog modal-mlg newsfeedPersonalis-modal" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h2>Personalising your newsfeed…</h2>
                <p>We are preparing your newsfeed based on your preferences.</p>
                <div class="row">
                    <div class="col-sm-4" ng-repeat="selection in followedCategoriesOrInt | limitTo:3">
                        <div class="categories-box corner-left no-effect">
                            <div class="category-thumb" style="background-image:url('<?php echo IMAGE_SERVER_PATH;?>upload/{{selectionType}}/220x220/{{selection.ProfilePicture}}');">
                                <div class="category-thumb-txt" ng-bind="selection.Name"></div>
                                <div class="category-thumb-overlay"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="more-categories"><a href="" class="text-off"><i class="ficon-plus"></i> {{selectionCount-3}} MORE</a></div>
                <div class="loader-personalising">
                    <span style="width:{{popupSettings.popupProcessbarWidth}}%;"></span>
                </div>
            </div>
        </div>
    </div>
    <div ng-show="popupSettings.steps==2" class="modal-dialog welcomeCommunity-modal" ng-class="(SettingsData.m40=='1') ? 'modal-mlg' : 'modal-lg post-without-editor' ;" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="text-center">
                    <h2 ng-cloak>Welcome {{LoggedInFirstName+ ' '+LoggedInLastName}}, to your own community!</h2>
                    <p>First things first, make a positive first impression. Start by introducing yourself to the community.</p>
                </div>
                <form id="welcomewallpostform" method="post" ng-hide="config_detail.ModuleID == '18' && pageDetails.IsFollowed == '0'" ng-show="!IsSingleActivity || edit_post" >
                    <div class="post-section">
                        <div class="post-title">
                            <input id="WelcomePostTitleInput" maxlength="140" ng-class="get_title_class()" onkeyup="if (140 - this.value.length == 1){
                      $('#PostTitleLimitPop').html(140 - this.value.length + ' character remaining');
                  } else {
                      $('#PostTitleLimitPop').html(140 - this.value.length + ' characters');
                  }" name="PostTitle" ng-keyup="titleKeyup = '1';" ng-init="titleKeyup = '0'" type="text"
                                   class="form-control post-placeholder" placeholder="Post Title" required ng-model="postTitle">
                            <span id="PostTitleLimitPop" class="place-holder">140 characters</span>
                        </div>
                        <div class="post-content">
                            <div id="WelcomePostEditor" class="textarea">
                                <summernote ng-model="PostContent" on-init="summernoteDropdown();" data-posttype="Post" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); parseTaggedInfo(contents); onSummerNoteChange(evt);" id="PostContent2" config="welcomeOptions" on-image-upload="imageUpload(files)"></summernote>
                                <span class="absolute loader postEditorLoader" style="top: 30%; display: none;">&nbsp;</span>
                            </div>

                            <div ng-if="parseLinks.length > 0" class="link-parsing">
                                <div ng-repeat="parseLink in parseLinks" class="link-post">
                                    <i ng-click="removeParseLink(parseLink.URL)" class="ficon-cross"></i>
                                    <div class="link-pars-left">
                                        <ul class="link-img-slider" ng-if="parseLink.Thumbs.length" id="parseImg">
                                            <li ng-repeat="image in parseLink.Thumbs" repeat-done="slickSlider('#parseImg',1)">
                                                <figure class="link-thumb">
                                                    <img ng-src="{{BaseUrl + image}}" >
                                                </figure>
                                            </li>
                                        </ul>
                                        <span ng-if="parseLink.Thumbs.length" class="img-count" ng-bind="(parseLink.Thumbs.length == 1) ? '1 Image' : parseLink.Thumbs.length + ' Images'"></span>
                                    </div>
                                    <div class="link-pars-right">
                                        <div class="link-content">
                                            <h5 ng-bind="parseLink.Title"></h5>
                                            <p ng-bind="parseLink.description"></p>
                                            <a target="_self" ng-bind="parseLink.URL"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="attached-list clearfix" ng-cloak ng-if="mediaCount > 0">
                                <ul class="attache-listing" ng-cloak ng-if="mediaCount > 0">
                                    <li ng-click="setSaySomethingAboutMedia('ALL')" ng-class="{ selected : ((mediaInputIndex === 'ALL') || (mediaInputIndex === '')) }" ng-show="(mediaCount > 1)"><span class="all-media">All <span ng-bind="(mediaCount | number:0)"></span> Media</span></li>
                                    <li ng-repeat="(mediaKey, media) in edit_medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" ng-class="{ selected : (mediaInputIndex === media.data.MediaGUID) }">
                                        <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" >

                                        <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                            <a target="_self" class="active"><span></span></a>
                                        </div>

                                        <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
                                        <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                    </li>
                                    <li ng-repeat="(mediaKey, media) in medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" ng-class="{ selected : (mediaInputIndex === media.data.MediaGUID) }">
                                        <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" >

                                        <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                            <a target="_self" class="active"><span></span></a>
                                        </div>

                                        <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
                                        <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                    </li>
                                </ul>
                                <div ng-show="(mediaCount > 0)" ng-cloak style="display:block;" class="comments same-caption about-media image-caption">
                                    <input ng-if="edit_post" ng-model="saySomthingAboutMedia[mediaInputIndex]" id="mc-default" class="form-control mc" placeholder="Say something about {{ ((mediaInputIndex == 'ALL') && (mediaCount > 1)) ? 'these media.' : 'this media.'}}" />
                                    <input ng-if="!edit_post" ng-model="saySomthingAboutMedia[mediaInputIndex]" id="mc-default" class="form-control mc" placeholder="Say something about {{ ((mediaInputIndex == 'ALL') && (mediaCount > 1)) ? 'these media.' : 'this media.'}}" />
                                </div>
                            </div>
                            <div class="post-file-list" ng-cloak ng-if="objLen(edit_files) > 0 || objLen(files) > 0">
                                <ul class="attache-file-list">
                                    <li ng-repeat="(fileKey, file) in edit_files">
                                        <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                        <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                        <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                        <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('edit_file', fileKey, file.data.MediaGUID)"></i>
                                    </li>
                                    <li ng-repeat="(fileKey, file) in files">
                                        <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                        <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                        <span class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                        <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)"></i>
                                    </li>
                                </ul>
                            </div>
                            <div class="tags-section" ng-show="addTagList">
                                <i class="ficon-user-tag" title="Add Tags" data-toggle="tooltip" data-placement="top"></i>
                                <div class="groups-tag groups-tag-list" id="list1">
                                    <div class="form-control">
                                        <tags-input ng-model="postTagList" display-property="Name" placeholder="Add tags" min-length="1" replace-spaces-with-dashes="false" template="tagsTemplate">
                                            <auto-complete source="getActivityTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="addTagDropdownTemplate"></auto-complete>
                                        </tags-input>
                                        <script class="tag-list" type="text/ng-template" id="tagsTemplate">
                                            <div class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip>
                                                <span class="tag-item-text" ng-init="tagname = $getDisplayText();"  ng-cloak ng-bind="$getDisplayText()"></span>
                                                <a target="_self" class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                                            </div>
                                        </script>
                                        <script type="text/ng-template" id="addTagDropdownTemplate">
                                            <a target="_self" ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="post-footer">
                            <div class="post-footer-inner">
                                <div class="row">
                                    <div class="col-md-6 col-sm-5">

                                    </div>
                                    <div class="col-md-6 col-sm-7">
                                        <ul class="post-buttons">
                                            <li class="attachment" tooltip data-placement="top" data-original-title="Add Tags" data-container="body">
                                                <button type="button" class="btn btn-default" ng-click="addTagList = !addTagList;"><i class="ficon-user-tag"></i></button>
                                            </li>
                                            <li class="attachment" tooltip data-placement="top" data-original-title="Attach Media" data-container="body">
                                                <button ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);" class="btn btn-default"><i class="ficon-attachment"></i></button>
                                            </li>
                                            <li>
                                                <div class="btn-group">
                                                    <button ng-disabled=" (isWallAttachementUploading || noContentToPost || summernoteBtnDisabler)" class="btn btn-primary" id="ShareButton" ng-click="SubmitWelcomePost();" type="button">
                                                        Post
                                                    </button>
                                                    <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                                                </div>
                                            </li>
                                        </ul>

                                        <input type="hidden" name="Status" id="status" value="2" />
                                        <input type="hidden" name="PostType" id="welcome_post_type" ng-value="activePostType" />
                                        <input type="hidden" name="Visibility" id="welcome_visible_for" value="<?php echo isset($DefaultPrivacy) ? $DefaultPrivacy : 1; ?>" />
                                        <input type="hidden" name="Commentable" id="comments_settings" value="1" />
                                        <input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
                                        <input type="hidden" name="ModuleEntityOwner" id="module_entity_owner" value="0" />
                                        <input type="hidden" name="IntroductionPopup" id="IntroductionPopup" value="1" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $this->load->view('include/post/preview') ?>
                    </div>
                </form>
                <h3>People like you!</h3>
                <p>Here are the members new to the community…</p>
                <div class="sliderdots"></div>
                <div class="similar-feed-copy " id="similar-feed-copy">
                    <div ng-repeat="data in welcome_popup_article">
                        <div class="feed-list">
                            <div class="feed-header">
                                <div class="feed-header-left">
                                    <figure class="thumb-sm">
                                        <a ng-if="data.PostAsModuleID == '3'" class="pointer-none">
                                            <img   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}" err-name="{{data.UserName}}">
                                        </a>
                                    </figure>
                                    <div class="info-text">
                                        <a class="pointer-none" ng-bind="data.UserName"></a>
                                        <small> posted in </small>
                                        <a class="pointer-none" ng-bind="data.EntityName"></a>
                                    </div>
                                </div>
                                <ul class="feed-nav pull-right">
                                    <li class="cursor-pointer" ng-click="get_history(data.ActivityGUID)">
                                        <a class="text-sm-off pointer-none">
                                            <span ng-cloak ng-if="data.ActivityType !== 'AlbumUpdated'" data-toggle="tooltip" ng-attr-data-original-title="{{getTimeFromDate(UTCtoTimeZone(data.CreatedDate));}}" ng-bind="date_format((data.CreatedDate))"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="feed-body">
                                <h4 ng-if="!data.IsAnnnouncementWidget" class="feed-media-placeholder"  ng-class="(data.PostTitle) ? 'exit-post-title' : 'no-post-title'">
                                    <span class="icon" ng-if="data.collapsedAttachmentExists"><i class="ficon-attachment"></i></span>
                                    <a  ng-bind-html="getPostTitle(data)" class="pointer-none" ></a>
                                </h4>
                                <p class="news-feed-post-body-container" ng-if="data.PostContent">
                                    <span ng-mouseup="get_selected_text($event, data.ActivityGUID);" ng-if="data.PostContent" ng-bind-html="textToLink(data.PostContent, false, 200)"></span>
                                </p>
                                <p ng-if="data.PostContent.length > 200 && data.ShowFull" ng-bind="parseLink(data.PostContent, false)" ></p>
                                <div class="activity-bar" ng-if="data.IsDeleted == 0 && data.StatusID != '10'">
                                    <ul class="feed-actions">
                                        <li class="btn-group " ng-if="data.PostType == 2">
                                            <button data-container="body" tooltip data-placement="top" title="Request Answer"
                                                    ng-hide="data.Visibility == '4'"
                                                    class="btn btn-default btn-xs"
                                                    ng-disabled="requestAns"
                                                    ng-click="get_activity_friend_list('init', data.ActivityGUID, data)">
                                                Request
                                            </button>
                                        </li>

                                        <li>
                            <span class="like-btn">
                                <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="{{(data.IsLike == '1') ? 'Unlike' : (data.NoOfLikes=='0') ? 'Be the first to like' : 'Like' ;}}" ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);" ng-class="data.IsLike == '1' ? 'ficon-heart active' : 'ficon-heart'" ></i>
                                <abbr ng-if="data.NoOfLikes > 0" ng-bind="data.NoOfLikes" ng-click="likeDetailsEmit(data.ActivityGUID, 'ACTIVITY');"></abbr>
                            </span>
                                        </li>
                                        <li ng-if="data.CommentsAllowed == 0 && data.NoOfComments > 0">
                                            <a ng-if="data.PostType !== '2'" ng-bind="'Comments (' + data.NoOfComments + ')'" ng-click="(data.ShowComments == 1) ? data.ShowComments = 0 : data.ShowComments = 1;"></a>
                                        </li>
                                        <li ng-if="data.CommentsAllowed == 1">
                                            <a ng-click="postCommentEditor(data.ActivityGUID, FeedIndex);  data.showeditor = true;" ng-if="LoginSessionKey!='' && data.NoOfComments == 0">
                                                Be the first to comment
                                            </a>
                                            <a ng-click="loginRequired()" ng-if="LoginSessionKey=='' && data.NoOfComments == 0">
                                                Be the first to comment
                                            </a>
                                            <a target="_self" ng-href="{{data.ActivityURL}}" ng-if="data.PostType !== '2' && data.NoOfComments > 0" ng-bind="'Comments (' + data.NoOfComments + ')'"  ng-click="(data.ShowComments == 1) ? data.ShowComments = 0 : data.ShowComments = 1;"></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="feed-footer" ng-hide="data.NoOfComments == 0 && !data.showeditor" ng-class="(data.NoOfComments > 0) ? 'is-comments' : '';">
                                <span ng-include src="partialURL+'activity/Comments.html'+app_version" ></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>