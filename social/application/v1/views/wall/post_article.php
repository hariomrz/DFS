<div ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="checkWallPost()">
    <div id="NewsFeedCtrl" ng-controller="NewsFeedCtrl">
        <span ng-cloak ng-if="activityData.length>0" ng-init="checkEditPost()"></span>
        <form id="wallpostform" method="post" ng-cloak>
            <div class="info-strip">
                <div class="container-fluid"> 
                    <?php /*<a class="btn btn-default btn-sm pull-right"><span class="icon visible-xs"><i class="ficon-image-media"></i></span><span class="hidden-xs">Add cover image</span></a>*/ ?>
                    <div class="container">        
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 col-xs-10">       

                                <div ng-cloak ng-if="(article_post.ModuleID == 3 || article_post.ModuleID == 34) && !article_post_custom.ActivityGUID " class="info-content">
                                    I am posting this article for 
                                    <div class="dropdown inline"> 
                                        <a data-toggle="dropdown" class="text-primary">
                                            <span class="text" ng-if="!selectedForum">Select a forum</span>
                                            <span class="text" ng-if="selectedForum" ng-bind="selectedForum.Name"></span>
                                            <span class="icon"><i class="ficon-arrow-down"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left dropdown-scroll scroll-240 xmCustomScrollbar">
                                            <li ng-repeat="forum in forumList">
                                                <a ng-bind="forum.Name" ng-click="getForumCates(forum)"></a>
                                            </li>                                                                                  
                                        </ul>
                                    </div>
                                    in the
                                    <div class="dropdown inline"> 
                                        <a data-toggle="dropdown" class="text-primary">
                                            <span class="text" ng-if="!selectedCategory">Category</span>
                                            <span class="text" ng-if="selectedCategory" ng-bind="selectedCategory.Name"></span>
                                            <span class="icon"><i class="ficon-arrow-down"></i></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left dropdown-scroll scroll-240 xmCustomScrollbar">
                                            <li ng-repeat="formCat in forumCatList">
                                                <a ng-bind="formCat.Name" ng-click="selectForumCategory(formCat)"></a>
                                            </li>        
                                        </ul>
                                    </div>
                                    <a data-toggle="tooltip" data-container="body" data-title="Select the section in which you would like to post this article."><i class="ficon-info-outline f-lg"></i></a>
                                </div> 

<!--                                <div ng-cloak ng-if="article_post.ModuleID == 3" class="info-content">
                                    I am posting this article for all
                                </div>-->
                                
                                <div ng-cloak ng-if="article_post.ModuleID == 1 || article_post.ModuleID == 14 " class="info-content">
                                    I am posting this article in
                                    <span class="text-brand" ng-bind="article_post.ModuleEntityName"></span>                                    
                                </div>
                                
                                <div ng-cloak ng-if="((article_post.ModuleID == 34 &&  article_post_custom.ActivityGUID) && article_post.ModuleEntityGUID != '' && article_post.ModuleEntityGUID != '0')" class="info-content">
                                    I am posting this article for
                                    <span class="text-primary" >  <?php echo $ForumName; ?> </span> <span class="text-brand" > in </span>
                                    
                                    <span class="text-primary" ng-bind="article_post.ModuleEntityName"></span>
                                </div>

                                
                            </div>
                        </div>
                    </div>

                </div>      
            </div>

            <!--Container-->    
            <div class="wrapper post-onpage">
                <div class="container">
                    <div class="row"> 
                        <div class="col-lg-8 col-lg-offset-2">
                            <div class="post-section">
                                <div class="post-title">
                                    <input id="PostTitleInput" maxlength="140" ng-class="get_title_class()" onkeyup="if (140 - this.value.length == 1) {
                                        $('#PostTitleLimit').html(140 - this.value.length + ' character remaining');
                                        } else {
                                        $('#PostTitleLimit').html(140 - this.value.length + ' characters');
                                        }" name="PostTitle" ng-keyup="update_title_keyup(1)" ng-init="titleKeyup = '0'" type="text" class="form-control post-placeholder" placeholder="Post Title" required ng-model="postTitle">
                                </div>
                                <div class="post-content">
                                    <div id="postEditor" class="textarea">
                                        <summernote class="small-editor-font" ng-model="PostContent" on-init="summernoteDropdown();" data-posttype="Post" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); parseTaggedInfo(contents);" id="PostContent" config="article_options" on-image-upload="imageUpload(files)"></summernote>
                                        <span class="absolute loader postEditorLoader" style="top: 30%; display: none;">&nbsp;</span>
                                    </div>
                                </div>    



                                <div ng-if="parseLinks.length > 0" class="link-parsing">
                                    <div ng-repeat="parseLink in parseLinks" class="link-post">
                                        <i ng-click="removeParseLink(parseLink.URL)" class="ficon-cross"></i>
                                        <div class="link-pars-left">
                                            <ul class="link-img-slider" ng-if="parseLink.Thumbs.length" id="parseImg">
                                                <li ng-repeat="image in parseLink.Thumbs" repeat-done="slickSlider('#parseImg',1)">
                                                    <figure class="link-thumb">
                                                        <img ng-src="{{BaseUrl + image}}" alt="">
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
                                            <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" alt="">

                                            <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                                <a target="_self" class="active"><span></span></a>
                                            </div>

                                            <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>

                                            <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                        </li>
                                        <li ng-repeat="(mediaKey, media) in medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" ng-class="{ selected : (mediaInputIndex === media.data.MediaGUID) }">
                                            <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" alt="">

                                            <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                                <a target="_self" class="active"><span></span></a>
                                            </div>

                                            <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>

                                            <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
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
                                            <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                            <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)"></i>
                                        </li>
                                    </ul>
                                </div>

                                <div ng-show="IsNewsFeed == '1' && !edit_post" class="tags-section">
                                    <div class="dropable sortable" droppable='items.list1' ng-move='moveObject(from, to, fromList, toList)' ng-create='createObject(object, to, list)'></div>
                                    <div class="groups-tag groups-tag-list" id="list1">
                                        <div class="form-control">
                                            <tags-input ng-model="tagsto" add-from-autocomplete-only="true" display-property="name" placeholder="Select which group or member see this post" replace-spaces-with-dashes="false" on-tag-added="tagAddedGU($tag)" on-tag-removed="tagRemovedGU($tag)" limit-tags="1" template="tagTemplateUser">
                                                <auto-complete debounce-delay="500" source="loadGroupFriendslist($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                            </tags-input>
                                            <script type="text/ng-template" id="tagTemplateUser">
                                                <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.name">
                                                <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.name"></span>
                                                <a target="_self" class="ficon-cross tag-remove ng-scope" ng-click="$removeTag()"></a>
                                                </div>
                                            </script>
                                            <script type="text/ng-template" id="userlistTemplate">
                                                <div class="list-items-xs">
                                                <div class="list-inner">
                                                <figure>
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{image_server_path+'upload/profile/220x220/'+data.ProfilePicture}}" alt="">
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="{{image_server_path+'assets/img/profiles/user_default.jpg'}}" alt="">
                                                </figure>
                                                <div class="list-item-body">
                                                <h4 class="list-heading-xs">
                                                <a target="_self" class="ellipsis conv-name">
                                                <span ng-bind-html="$highlight($getDisplayText())"></span>
                                                <i class="ficon-close"  ng-if="data.ModuleID==1" ng-class="{'ficon-close':data.Privacy==0,'ficon-secrets':data.Privacy==2,'ficon-globe':data.Privacy==1}"></i>
                                                </a>
                                                </h4>
                                                </div>
                                                </div>
                                                </div>
                                            </script>
                                            <span ng-if=" (tagsto.length > 0)" class="place-holder" ng-bind="selectContactsHelpTxt"></span>
                                            <span ng-if=" (tagsto.length === 0)" class="place-holder" ng-bind="selectPrivacyHelpTxt"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="tags-section" ng-show="addTagList">
                                    <i class="ficon-user-tag" title="Select contacts or groups to start a new conversation." data-toggle="tooltip" data-placement="top"></i>
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
                        </div>
                    </div>




                </div>  
                <div class="post-footer">
                    <div class="post-footer-inner">

                        <div class="container">
                            <div class="row"> 
                                <div class="col-lg-8 col-lg-offset-2">
                                    <div class="post-footer-inner">
                                        <div class="row gutter-5">
                                            <div class="col-xs-5 col-sm-5 post-footer-left">
                                                <div ng-cloak ng-show="activePostType != '8' && activePostType != '9'" class="checkbox-list">
                                                    <div ng-cloak ng-if="!edit_post" class="checkbox checkbox-inline">
                                                        <input type="checkbox" value="" id="dCommenting">
                                                        <label for="dCommenting">Disable Commenting</label>
                                                    </div>
                                                    <div ng-cloak ng-if="edit_post" class="checkbox checkbox-inline">
                                                        <input type="checkbox" ng-checked="!edit_post_details.CommentsAllowed" id="dCommenting">
                                                        <label for="dCommenting">Disable Commenting</label>
                                                    </div>

                                                    <div ng-if="!edit_post && activePostType != '9'" class="checkbox checkbox-inline" ng-class="{'hide' : !memTagCount}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                        <input id="notifyAll" type="checkbox" value="1" ng-model="NotifyAll">
                                                        <label for="notifyAll">Notify all group members</label>
                                                    </div>
                                                    <div ng-if="edit_post && activePostType != '9'" class="checkbox checkbox-inline" ng-class="{'hide' : data.IsEntityOwner == '0' || data.ModuleID !== '1'}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                        <input id="notifyAll" type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-model="edit_post_details.NotifyAll">
                                                        <label for="notifyAll">Notify all group members</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-7 col-sm-7 post-footer-right">
                                                <ul class="post-buttons">

                                                    <li ng-cloak ng-show="activePostType != '8' && activePostType != '9'" class="xattachment" tooltip data-placement="top" data-original-title="Add Tags" data-container="body">
                                                        <button type="button" class="btn btn-default" ng-click="updateTagList(!addTagList);"><i class="ficon-user-tag"></i></button>
                                                    </li>
                                                    <li ng-cloak ng-show="activePostType != '8' && activePostType != '9'" class="xattachment" tooltip data-placement="top" data-original-title="Attach Media" data-container="body">

                                                        <button ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);" class="btn btn-default"><i class="ficon-attachment"></i></button>
                                                    </li>

                                                    <li>


                                                        <div class="btn-group dropup">                            
                                                            <button ng-click="SubmitWallpostPagePre()" ng-disabled="SubmitWallpostPagePostBtn()" class="btn btn-primary" id="ShareButton" type="button">
                                                                Post                                  
                                                            </button>
                                                            <button ng-disabled="SubmitWallpostPagePostBtn()" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="ficon-arrow-down"></i></button>
                                                            <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>                                
                                                            <ul class="dropdown-menu">
                                                                <li><a ng-click="SubmitWallpostPagePre();">Post</a></li>
                                                                <!--                                                            <li><a ng-click="showPreview()">Preview</a></li>-->
                                                                <li ng-hide="edit_post && singleActivity.StatusID == '2'">
                                                                    <a ng-click="SubmitWallpostPagePre(1);">Save as draft</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>

                                                <input type="hidden" name="Status" id="status" value="2" />
                                                <input type="hidden" name="PostType" id="post_type" ng-value="4" />
                                                <input ng-if="!edit_post" type="hidden" name="Visibility" id="visible_for" value="{{DefaultPrivacy}}" />
                                                <input ng-if="edit_post" type="hidden" name="Visibility" id="visible_for2" ng-value="edit_post_details.Visibility" />
                                                <input type="hidden" ng-if="!edit_post" name="Commentable" id="comments_settings" value="1" />
                                                <input type="hidden" ng-if="edit_post" name="Commentable" id="comments_settings2" ng-value="edit_post_details.CommentsAllowed" />
                                                <input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
                                                <input type="hidden" name="ModuleEntityOwner" id="module_entity_owner" value="0" />
                                                <input type="hidden" name="ModuleEntityName" id="ModuleEntityName" value="<?php echo isset($ModuleEntityName) ? $ModuleEntityName : ''; ?>" />
                                                <input type="hidden" name="ActivityGUID" id="ActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : ''; ?>" />
                                                <input type="hidden" name="ActivityGUID" id="EditActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : ''; ?>" />
                                                <input type="hidden" name="ForumID" id="ForumID" value="<?php echo isset($ForumID) ? $ForumID : 0; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>     
            </div>
            <div class="modal fade" id="addsummary">
                <div class="modal-dialog">
                    <div class="modal-content modal-lg">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                        <h4 class="modal-title">Add summary to your article </h4>
                      </div>
                      <div class="modal-body">
                        <form >
                          <div class="form-group">
                            <textarea name="Summary" id="Summary" rows="8" maxcount="400" placeholder="Summary will be displayed on article listing page. This will help others to understand objective of the article." class="form-control noborder"></textarea>
                          </div>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <a class="pull-left text-link m-v-xs" ng-click="SubmitWallpostPage(0);">Publish without adding summary</a>
                        <div class="pull-right">     
                          <button type="submit" class="btn btn-primary" data-dismiss="modal" ng-click="SubmitWallpostPage(1);">Publish</button>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>