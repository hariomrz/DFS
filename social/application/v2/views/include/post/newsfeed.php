<form id="wallpostform" ng-cloak method="post" ng-hide="!showPostBox || (config_detail.ModuleID == '3' && !CanPost) || (config_detail.ModuleID == '18' && pageDetails.IsFollowed == '0') || (config_detail.ModuleID == '14' && EventDetail.CanPostOnWall=='0')" ng-show="!IsSingleActivity || edit_post" ng-cloak>
    <div ng-class="(!IsSingleActivity) ? 'post-type-view' : '';" ng-hide="IsSingleActivity && !postEditormode">
        <div class="write-message">
            <?php if (isset($IsLoggedIn) && $IsLoggedIn == '1') { ?>

                <div class="write-m-block" ng-click=" slickSlider(); updateActivePostTypeDefault(ContentTypes); showNewsFeedPopup();">
                    <figure class="thumb-md">
                        <img ng-if="LoggedInProfilePicture!='' && LoggedInProfilePicture!='user_default.jpg'" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + LoggedInProfilePicture}}" class="img-circle"  >
                        <span class="thumb-alpha" ng-if="LoggedInProfilePicture=='' || LoggedInProfilePicture=='user_default.jpg'">
                            <span ng-style="RandomBG" class="default-thumb">
                                <span class="default-thumb-placeholder" ng-bind="getInitials(LoggedInFirstName,LoggedInLastName)"></span>
                            </span>
                        </span>
                    </figure>

                    <span ng-click="setFocusToSummernote('#PostContent')" ng-bind="lang.write_new_msg"></span>
                </div>
            <?php } else { ?>
                <div ng-click="loginRequired()" class="write-m-block" >
                    <span ng-bind="lang.write_new_msg"></span>
                </div>
            <?php } ?>



            <div class="modal fade" aria-hidden="false" tabindex="-1" id="postNewsFeedTypeModal" data-backdrop="static" ng-click="confirmCloseEditor($event)">
                <div class="modal-dialog modal-mlg no-modal-close-container">
                    <div class="modal-content">
                        <div class="post-type" ng-show="postEditormode && ShowPreview == '0'" ng-cloak>
                            <div class="post-type-info">
                                <span ng-bind="lang.exit_fullscreen_view"></span>
                            </div>
                            <div ng-if="!edit_post && config_detail.ModuleID != '14' && config_detail.ModuleID != '18'" class="post-header">
                                <ul ng-if="IsContest==0 && override_post_permission.length == 0" class="post-type-tab nav nav-tabs nav-tabs-modal-scroll">
                                    <li ng-repeat="type in ContentTypes" repeat-done="updateActivePostTypeDefault(ContentTypes)"><a ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                                </ul>
                                <ul ng-if="IsContest==0 && override_post_permission.length > 0" class="post-type-tab nav nav-tabs nav-tabs-modal-scroll">
                                    <li ng-repeat="type in override_post_permission" repeat-done="updateActivePostTypeDefault(override_post_permission)"><a ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                                </ul>
                                <ul ng-cloak ng-if="IsContest==1" class="post-type-tab">
                                    <li>
                                        <a class="selected" ng-init="updateActivePostType(9);">Contest</a>
                                    </li>
                                </ul>
                            </div>

                            <div ng-if="edit_post && config_detail.ModuleID != '14' && config_detail.ModuleID != '18'" class="post-header">
                                <ul class="post-type-tab nav nav-tabs nav-tabs-modal-scroll"><li><a class="selected" ng-bind="allow_post_types[activePostType]"></a></li></ul>
                            </div>

                            <div ng-if="config_detail.ModuleID == '14' || config_detail.ModuleID == '18'" class="post-header">
                                <ul ng-if="override_post_permission.length == 0" class="post-type-tab nav nav-tabs nav-tabs-modal-scroll">
                                    <li ng-repeat="type in ContentTypes" ng-if="type.Value == '1'" repeat-done="updateActivePostTypeDefault(ContentTypes)"><a ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                                </ul>
                                <ul ng-if="override_post_permission.length > 0" class="post-type-tab nav nav-tabs nav-tabs-modal-scroll">
                                    <li ng-repeat="type in override_post_permission" ng-if="type.Value == '1'" repeat-done="updateActivePostTypeDefault(override_post_permission)"><a ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                                </ul>
                            </div>

                            <div class="post-section">
                                <div ng-cloak ng-if="activePostType!='8' && activePostType!='9'" class="post-title">
                                    <input id="PostTitleInput" maxlength="140" ng-class="get_title_class()" onkeyup="if (140 - this.value.length == 1){ $('#PostTitleLimit').html(140 - this.value.length + ' character remaining'); } else { $('#PostTitleLimit').html(140 - this.value.length + ' characters'); }" name="PostTitle" ng-keyup="titleKeyup = '1';" ng-init="titleKeyup = '0'" type="text" class="form-control post-placeholder" placeholder="Post Title" required ng-model="postTitle"> 
                                    <span id="PostTitleLimit" class="place-holder">140 characters</span>
                                </div>
                                <div ng-cloak ng-show="activePostType!='8' && activePostType!='9'" class="post-content">
                                    <div id="postEditor" class="textarea">
                                        <summernote ng-cloak ng-if="SettingsData.m40=='1'" ng-model="PostContent" on-init="summernoteDropdown();" data-posttype="Post" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); parseTaggedInfo(contents);onSummerNoteChange(evt);" id="PostContent" config="options" on-image-upload="imageUpload(files)"></summernote>
                                        <span ng-cloak ng-if="SettingsData.m40=='1'" class="absolute loader postEditorLoader" style="top: 30%; display: none;">&nbsp;</span>
                                        <textarea ng-cloak id="PostContent" ng-model="PostContent" ng-if="SettingsData.m40=='0'"></textarea>
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
                                                    <a ng-bind="parseLink.URL"></a>
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
                                                    <a class="active"><span></span></a>
                                                </div>

                                                <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
<!--                                                <span ng-hide="media.progress" class="loader"></span>-->
                                                <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                            </li>
                                            <li ng-repeat="(mediaKey, media) in medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" ng-class="{ selected : (mediaInputIndex === media.data.MediaGUID) }">
                                                <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" >

                                                <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                                    <a class="active"><span></span></a>
                                                </div>

                                                <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
<!--                                                <span ng-hide="media.progress" class="loader"></span>-->
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
<!--                                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                                <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                                <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                                <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                                <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('edit_file', fileKey, file.data.MediaGUID)"></i>
                                            </li>
                                            <li ng-repeat="(fileKey, file) in files">
<!--                                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                                <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                                <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                                <span class='file-name' ng-bind="file.data.OriginalName|| file.name"></span>
                                                <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)"></i>
                                            </li>
                                        </ul>
                                    </div>
                                    <div ng-show="IsNewsFeed == '1' && !edit_post" class="tags-section" ng-if="SettingsData.m1=='1'">
                                        <div class="dropable sortable" droppable='items.list1' ng-move='moveObject(from, to, fromList, toList)' ng-create='createObject(object, to, list)'></div>
                                        <div class="groups-tag groups-tag-list" id="list1"> 
                                            <div class="form-control">
                                                <tags-input ng-model="tagsto" add-from-autocomplete-only="true" display-property="name" placeholder="Select which group or member see this post" replace-spaces-with-dashes="false" on-tag-added="tagAddedGU($tag)" on-tag-removed="tagRemovedGU($tag)" limit-tags="1" template="tagTemplateUser">
                                                    <auto-complete debounce-delay="500" source="loadGroupFriendslist($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="tagTemplateUser">
                                                    <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.name">
                                                    <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.name"></span>
                                                    <a class="ficon-cross tag-remove ng-scope" ng-click="$removeTag()"></a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="userlistTemplate">
                                                    <div class="list-items-xs">
                                                    <div class="list-inner">
                                                    <figure>
                                                    <img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{ImageServerPath + 'upload/profile/220x220/'+data.ProfilePicture}}" >
                                                    <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" >
                                                    </figure>
                                                    <div class="list-item-body">
                                                    <h4 class="list-heading-xs">
                                                    <a class="ellipsis conv-name">
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
                                        <i class="ficon-user-tag" title="Add Tags" data-toggle="tooltip" data-placement="top"></i> 
                                        <div class="groups-tag groups-tag-list" id="list1"> 
                                            <div class="form-control">
                                                <tags-input ng-model="postTagList" display-property="Name" placeholder="Add tags" min-length="1" replace-spaces-with-dashes="false" template="tagsTemplate">
                                                    <auto-complete source="getActivityTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="addTagDropdownTemplate"></auto-complete>
                                                </tags-input>
                                                <script class="tag-list" type="text/ng-template" id="tagsTemplate">
                                                    <!-- <ul class="tag-list">
                                                    <li ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak> -->
                                                    <div class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip>  
                                                    <span class="tag-item-text" ng-init="tagname = $getDisplayText();"  ng-cloak ng-bind="$getDisplayText()"></span>
                                                    <a class="ficon-cross tag-remove" ng-click="$removeTag()"></a>
                                                    </div>
                                                    <!-- </li>

                                                    </ul> --> 
                                                </script>
                                                <script type="text/ng-template" id="addTagDropdownTemplate">
                                                    <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contest Start -->
                                <div ng-cloak ng-if="activePostType=='9'" class="post-content" ng-class="(isWallAttachementUploading) ? 'loading-overlay' : '' ;" ng-init="FocusOn=''">
                                    <div ng-cloak ng-if="edit_post" ng-init="setContestEdit()"></div>
                                    <div class="panel post-panel contest-post"  ng-class="backgroundClass" ng-style="visualPostStyle">
                                      <div class="panel-heading">
                                        <div class="row">
                                          <div class="col-sm-8">
                                            <div class="form-group title-group">              
                                              <input type="text" ng-focus="FocusOn='ContestTitle'" ng-blur="FocusOn=''" name="PostTitle" maxlength="50" ng-model="Contest.PostTitle" placeholder="Title" class="form-control input-md input-dashed" on-focus>
                                            </div>
                                          </div>
                                          <div class="col-sm-4 text-right">
                                                <span ng-cloak ng-show="FocusOn=='ContestTitle'" class="help-character" ng-bind="(50-Contest.PostTitle.length)+' Characters'"></span>

                                                <span ng-cloak ng-show="FocusOn=='ContestPostContent'" class="help-character" ng-bind="(100-Contest.PostContent.length)+' Characters'"></span>

                                                <span ng-cloak ng-show="FocusOn=='ContestButtonText'" class="help-character" ng-bind="(20-Contest.ButtonText.length)+' Characters'"></span>
                                          </div>
                                        </div>
                                      </div>
                                      
                                      <div class="panel-body">
                                        <div class="row">
                                          <div class="col-sm-8">
                                            <div class="form-group write-group">                                    
                                              <textarea ng-focus="FocusOn='ContestPostContent'" ng-blur="FocusOn=''" name="PostContent" maxlength="100" ng-model="Contest.PostContent" placeholder="Win your own personalised t-Shirt and Brand New Car" rows="2" class="form-control input-dashed" on-focus></textarea>
                                            </div>
                                          </div>
                                        </div>
                                          
                                        <div class="row">
                                            <div class="col-sm-4">
                                              <div class="form-group">              
                                                <input type="text" placeholder="Select Submission Date" ng-init="initContestDatepicker()" id="ContestDate" ng-model="Contest.ContestDate" readonly="readonly" class="form-control input-md input-dashed" on-focus>
                                              </div>
                                            </div>
                                            <div class="col-sm-4">
                                              <div class="form-group">              
                                                <input type="text" placeholder="Select Submission Time" ng-init="initContestTimepicker()" id="ContestTime" ng-model="Contest.ContestTime" readonly="readonly" class="form-control input-md input-dashed" on-focus>
                                              </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="form-group">              
                                                  <input ng-focus="FocusOn='ContestButtonText'" ng-blur="FocusOn=''" maxlength="20" ng-model="Contest.ButtonText" type="text" placeholder="Add Contest button text" class="form-control input-md input-dashed" on-focus>
                                                </div>
                                              </div>
                                            </div>
                                      </div>

                                      <div class="panel-footer">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <div class="switches">
                                              <ul ng-cloak ng-hide="visualPostImage=='1'" class="list-switch">
                                                <li class="items" ng-class="(backgroundClass=='switch-one' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-one" ng-click="UpdateBackgroundClass('switch-one')"></a>
                                                </li>
                                                <li class="items" ng-class="(backgroundClass=='switch-two' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-two" ng-click="UpdateBackgroundClass('switch-two')"></a>
                                                </li>
                                                <li class="items" ng-class="(backgroundClass=='switch-three' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-three" ng-click="UpdateBackgroundClass('switch-three')"></a>
                                                </li>
                                                <li class="items" ng-class="(backgroundClass=='switch-four' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-four" ng-click="UpdateBackgroundClass('switch-four')"></a>
                                                </li>
                                                <li class="items" ng-class="(backgroundClass=='switch-five' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-five" ng-click="UpdateBackgroundClass('switch-five')"></a>
                                                </li>
                                              </ul>
                                            </div>
                                          </div>
                                          <div class="col-sm-6">
                                            <div class="btn-toolbar btn-toolbar-xs btn-toolbar-right">
                                              <a ng-cloak ng-if="visualPostImage=='0' && !isWallAttachementUploading" class="btn btn-default trasparent" ngf-min-height="472" ngf-min-width="750" ngf-select="uploadWallFiles($files, $invalidFiles)" ngf-validate-async-fn="validateFileSize($file);">Add Background Image</a>
                                              <a ng-cloak ng-if="visualPostImage=='1'" ng-click="removeWallAttachement('media', 'media-0', medias['media-0'].data.MediaGUID)" class="btn btn-default trasparent">Remove Image</a>
                                              <div class="hiddendiv">
                                                <input type="file" name="" id="changeBg">
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <!-- Contest Ends -->


                                <div ng-cloak ng-if="activePostType=='8'" class="post-content" ng-class="(isWallAttachementUploading) ? 'loading-overlay' : '' ;">
                                    <span ng-if="edit_post" ng-init="setVisualEditPost()"></span>
                                    <div class="panel post-panel visual-post" ng-class="backgroundClass" ng-style="visualPostStyle">
                                      <div class="loader" ng-if="isWallAttachementUploading" ng-cloak></div>
                                      <div class="panel-heading">
                                        <div class="row">
                                          <div class="col-sm-8">
                                            <div class="form-group title-group">              
                                              <input ng-focus="FocusOn='title'" ng-blur="FocusOn=''" maxlength="50" ng-model="VisualPost.PostTitle" name="PostTitle" type="text" placeholder="Title" class="form-control input-md input-dashed" on-focus>
                                            </div>
                                          </div>
                                          <div class="col-sm-4 text-right">
                                            
                                              <span ng-show="FocusOn=='title' && 50-VisualPost.PostTitle.length<2" class="help-character" ng-bind="(50-VisualPost.PostTitle.length)+' Character'"></span>
                                              <span ng-show="FocusOn=='title' && 50-VisualPost.PostTitle.length>=2" class="help-character" ng-bind="(50-VisualPost.PostTitle.length)+' Characters'"></span>

                                              <span ng-show="FocusOn=='facts' && 80-VisualPost.Facts.length<2" class="help-character" ng-bind="(80-VisualPost.Facts.length)+' Character'"></span>
                                              <span ng-show="FocusOn=='facts' && 80-VisualPost.Facts.length>=2" class="help-character" ng-bind="(80-VisualPost.Facts.length)+' Characters'"></span>

                                              <span ng-show="FocusOn=='content' && 140-VisualPost.PostContent.length<2" class="help-character" ng-bind="(140-VisualPost.PostContent.length)+' Character'"></span>
                                              <span ng-show="FocusOn=='content' && 140-VisualPost.PostContent.length>=2" class="help-character" ng-bind="(140-VisualPost.PostContent.length)+' Characters'"></span>
                                          </div>
                                        </div>
                                      </div> 
                                      
                                      <div class="panel-body">
                                        <div class="row">
                                          <div class="col-sm-8 col-sm-offset-2">
                                            <div class="form-group write-group">              
                                              <input ng-focus="FocusOn='facts'" ng-blur="FocusOn=''" ng-model="VisualPost.Facts" name="Facts" maxlength="80" type="text" placeholder="Write your Fact" class="form-control input-md input-dashed text-center" on-focus>
                                            </div>
                                          </div>
                                          <div class="col-sm-8 col-sm-offset-2">
                                            <div class="form-group">              
                                              <textarea ng-init="textareaAutosize()" ng-focus="FocusOn='content'" ng-blur="FocusOn=''" ng-model="VisualPost.PostContent" name="PostContent" maxlength="140" type="text" placeholder="Write your Content" class="form-control input-md input-dashed text-center autoresize" data-autoresize="" on-focus></textarea>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="panel-footer">
                                        <div class="row">
                                          <div class="col-sm-6">
                                            <div class="switches">
                                              <ul ng-cloak ng-hide="visualPostImage=='1'" class="list-switch">
                                                <li class="items" ng-class="(backgroundClass=='switch-one' && visualPostImage=='0') ? 'selected' : '' ;">
                                                  <a class="switch-one" ng-click="UpdateBackgroundClass('switch-one')"></a>
                                                </li>

                                                <li class="items" ng-class="(backgroundClass=='switch-two' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-two" ng-click="UpdateBackgroundClass('switch-two')"></a>
                                                </li>

                                                <li class="items" ng-class="(backgroundClass=='switch-three' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-three" ng-click="UpdateBackgroundClass('switch-three')"></a>
                                                </li>

                                                <li class="items" ng-class="(backgroundClass=='switch-four' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-four" ng-click="UpdateBackgroundClass('switch-four')"></a>
                                                </li>

                                                <li class="items" ng-class="(backgroundClass=='switch-five' && visualPostImage=='0') ? 'selected' : '' ;">
                                                    <a class="switch-five" ng-click="UpdateBackgroundClass('switch-five')"></a>
                                                </li>
                                              </ul>
                                            </div>
                                          </div>
                                          <div class="col-sm-6">
                                            <div class="btn-toolbar btn-toolbar-xs btn-toolbar-right">
                                              <a ng-cloak ng-if="visualPostImage=='0' && !isWallAttachementUploading" class="btn btn-default trasparent" ngf-min-height="472" ngf-min-width="750" ngf-select="uploadWallFiles($files, $invalidFiles)" ngf-validate-async-fn="validateFileSize($file);">Add Background Image</a>
                                              <a ng-cloak ng-if="visualPostImage=='1'" ng-click="removeWallAttachement('media', 'media-0', medias['media-0'].data.MediaGUID)" class="btn btn-default trasparent">Remove Image</a>
                                              <div class="hiddendiv">
                                                <input type="file" name="" id="changeBg">
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="post-footer">
                                    <div class="post-footer-inner">
                                        <div class="row">
                                            <div class="col-sm-5" ng-class="(activePostType=='9') ? 'col-md-4' : 'col-md-6' ;">
                                                <div ng-cloak ng-show="activePostType!='8' && activePostType!='9'" class="checkbox-list">
                                                    <div ng-cloak ng-if="!edit_post" class="checkbox checkbox-inline">
                                                        <input type="checkbox" value="" id="dCommenting">
                                                        <label for="dCommenting">Disable Commenting</label>
                                                    </div>
                                                    <div ng-cloak ng-if="edit_post" class="checkbox checkbox-inline">
                                                        <input type="checkbox" ng-checked="!edit_post_details.CommentsAllowed" id="dCommenting">
                                                        <label for="dCommenting">Disable Commenting</label>
                                                    </div>

                                                    <div ng-if="!edit_post && activePostType!='9'" class="checkbox checkbox-inline" ng-class="{'hide' : !memTagCount}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                        <input id="notifyAll" type="checkbox" value="1" ng-model="NotifyAll">
                                                        <label for="notifyAll">Notify all group members</label>
                                                    </div>
                                                    <div ng-if="edit_post && activePostType!='9'" class="checkbox checkbox-inline" ng-class="{'hide' : data.IsEntityOwner == '0' || data.ModuleID !== '1'}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                        <input id="notifyAll" type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-model="edit_post_details.NotifyAll">
                                                        <label for="notifyAll">Notify all group members</label>
                                                    </div>
                                                </div>


                                            </div>
                                            <div class="col-md-6 col-sm-7" ng-class="(activePostType=='9') ? 'col-md-8' : 'col-md-6' ;">
                                                <ul class="post-buttons">

                                                    <li ng-cloak ng-show="activePostType=='9'" class="hidden-sm hidden-xs"><label class="label-control m-t-7">How many winners announced</label></li>
                                                    <li ng-cloak ng-show="activePostType=='9'">
                                                         <div class="input-small chosen-dropup">
                                                            <select id="NoOfWinners" class="form-control" chosen="" data-disable-search="true" ng-model="Contest.NoOfWinners" ng-options="o for o in opt">
                                                            </select>
                                                        </div>
                                                    </li>
                                                    
                                                    <li ng-cloak ng-show="activePostType!='8' && activePostType!='9'" class="attachment" tooltip data-placement="top" data-original-title="Add Tags" data-container="body">
                                                        <button type="button" class="btn btn-default" ng-click="addTagList = !addTagList;"><i class="ficon-user-tag"></i></button>
                                                    </li>
                                                    <li ng-cloak ng-show="activePostType!='8' && activePostType!='9'" class="attachment" tooltip data-placement="top" data-original-title="Attach Media" data-container="body">

                                                        <button ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);" class="btn btn-default"><i class="ficon-attachment"></i></button>
                                                    </li>
                                                    <?php if (isset($ModuleID) && $ModuleID == 3 && $IsAdmin == 1) : ?>
                                                        <li ng-if="(edit_post && !postInGroup && (tagsto.length === 0) && edit_post_details.ModuleID!='34') && activePostType!='8' && activePostType!='9'" ng-init="setActiveIconToPrivacy(selectedPrivacy);">
                                                            <div class="btn-group custom-icondrop">
                                                                <button tooltip data-placement="top" data-container="body" data-original-title="{{ selectedPrivacyTooltip}}" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                                    <i ng-if="edit_post_details.Visibility == '1'" id="IconSelect" ng-cloak class="ficon-globe" ></i>
                                                                    <i ng-if="edit_post_details.Visibility == '2'" id="IconSelect" ng-cloak class="icon-follwers" ></i>
                                                                    <i ng-if="edit_post_details.Visibility == '3' && SettingsData.m10=='1'" id="IconSelect" ng-cloak class="ficon-friends" ></i>
                                                                    <i ng-if="edit_post_details.Visibility == '4'" id="IconSelect" ng-cloak class="ficon-user" ></i>
                                                                    <i class="ficon-arrow-down"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right dropdown-withicons dropdown-fullwidth privacy-dd" role="menu">
                                                                    <li class="list-head text-center"><span>Who should see this?</span></li>
                                                                    <li ng-hide="edit_post_details.NoOfComments > 0 && edit_post_details.Visibility > 1">
                                                                        <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(selectedPrivacy); change_visibility_settings(1);" onClick="$('#visible_for').val(1);">
                                                                            <i class="ficon-globe"></i>Everyone
                                                                            <span class="" ng-bind="'Anyone on '+lang.web_name">Anyone on {{lang.web_name}}</span>
                                                                        </a>
                                                                    </li>
                                                                    <li ng-hide="(edit_post_details.NoOfComments > 0 && edit_post_details.Visibility > 3) || SettingsData.m10=='0'">
                                                                        <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(selectedPrivacy); change_visibility_settings(3);" onClick="$('#visible_for').val(3);">
                                                                            <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                            <span class="" ng-bind="'Your friends' + ((taggedHelpTxtSuffix == '') ? '' : ', ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a ng-click="setPrivacyHelpTxt(4); setActiveIconToPrivacy(selectedPrivacy); change_visibility_settings(4);" onClick="$('#visible_for').val(4);">
                                                                            <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>Only Me
                                                                            <span class="" ng-bind="'Only me' + ((taggedHelpTxtSuffix == '') ? '' : ', ' + taggedHelpTxtSuffix)">Only me and anyone tagged</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>

                                                        <li ng-if="!edit_post && (tagsto.length === 0) && activePostType!='8' && activePostType!='9'" tooltip data-placement="top" data-container="body" ng-attr-data-original-title="{{ selectedPrivacyTooltip}}" ng-init="setActiveIconToPrivacy(selectedPrivacy);">

                                                            <div class="btn-group custom-icondrop">
                                                                <?php if ($this->session->userdata('UserID') == $UserID): ?>
                                                                    <button type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                                        <i ng-if="selectedPrivacy == 1" id="IconSelect" class="ficon-globe"></i>
                                                                        <i ng-if="selectedPrivacy == 3 && SettingsData.m10=='1'" id="IconSelect" class="" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                        <i ng-if="selectedPrivacy == 4" id="IconSelect" class="" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                                        <i class="ficon-arrow-down"></i>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                                        <?php if (isset($DefaultPrivacy)) : ?>
                                                                            <?php if ($DefaultPrivacy == 1) : ?>
                                                                                <i id="IconSelect" class="ficon-globe" ng-class="selectedPrivacyClass"></i>
                                                                            <?php endif; ?>
                                                                            <?php if ($DefaultPrivacy == 2) : ?>
                                                                                <i id="IconSelect" class="icon-follwers" ng-class="selectedPrivacyClass"></i>
                                                                            <?php endif; ?>
                                                                            <?php if ($DefaultPrivacy == 3) : ?>
                                                                                <i id="IconSelect" class="ficon-friends" ng-if="SettingsData.m10=='1'" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                            <?php endif; ?>
                                                                            <?php if ($DefaultPrivacy == 4) : ?>
                                                                                <i id="IconSelect" class="ficon-user" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                        <i class="ficon-arrow-down"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <ul class="dropdown-menu dropdown-menu-right dropdown-withicons dropdown-fullwidth privacy-dd" role="menu">
                                                                    <li class="list-head text-center"><span>Who should see this?</span></li>
                                                                    <li>
                                                                        <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(1);">
                                                                            <i class="ficon-globe"></i>Everyone
                                                                            <span class="" ng-bind="'Anyone on '+lang.web_name">Anyone on {{lang.web_name}}</span>
                                                                        </a>
                                                                    </li>
                                                                    <li ng-if="SettingsData.m10=='1'">
                                                                        <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(3);">
                                                                            <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                            <span class="" ng-bind="'Your friends' + ((taggedHelpTxtSuffix == '') ? '' : ', ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a ng-click="setPrivacyHelpTxt(4); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(4);">
                                                                            <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>Only Me
                                                                            <span class="" ng-bind="'Only me' + ((taggedHelpTxtSuffix == '') ? '' : ', ' + taggedHelpTxtSuffix)">Only me and anyone tagged</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    <?php elseif (isset($ModuleID) && $ModuleID == 3): ?>
                                                        <li ng-cloak ng-show="activePostType!='8' && activePostType!='9'" ng-init="setPrivacyHelpTxt(<?php echo $DefaultPrivacy ?>); setActiveIconToPrivacy(<?php echo $DefaultPrivacy ?>);" tooltip ng-attr-title="(selectedPrivacy == 1) ? 'Anyone on VSocial' : (selectedPrivacy == 3) ? 'Only Me + Friends of '+ FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) : 'Only me + ' + FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) ;" data-placement="top" >
                                                            <?php if ($DefaultPrivacy == 4) : ?>
                                                                <button tooltip data-placement="top" ng-attr-data-original-title="{{'Only me + ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)}}" type="button" class="btn btn-default">
                                                                <?php else : ?>
                                                                    <button tooltip data-placement="top" ng-attr-data-original-title="{{(selectedPrivacy == 1) ? 'Anyone on VSocial' : (selectedPrivacy == 3) ? 'Only Me + Friends of '+ FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) : 'Only me + ' + FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) ;}}" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                                    <?php endif; ?>
                                                                    <?php if (isset($DefaultPrivacy)) { ?>
                                                                        <?php if ($DefaultPrivacy == 1) { ?>
                                                                            <i ng-if="selectedPrivacy == 'false'" id="IconSelect" class="ficon-globe"></i>
                                                                            <i ng-if="selectedPrivacy == '1'" id="IconSelect" class="ficon-globe"></i>
                                                                            <i ng-if="selectedPrivacy == '3' && SettingsData.m10=='1'" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                            <i ng-if="selectedPrivacy == '4'" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                                        <?php } elseif ($DefaultPrivacy == 3 || $DefaultPrivacy == 1) { ?>
                                                                            <i ng-init="set_default_privacy(3)" ng-if="SettingsData.m10=='1' && selectedPrivacy == 'false'" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                            <i ng-init="set_default_privacy(3)" ng-if="SettingsData.m10=='1' && (selectedPrivacy == '3' || selectedPrivacy == '1')" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                            <i ng-if="selectedPrivacy == '4'" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                                        <?php } else { ?>
                                                                            <i ng-init="set_default_privacy(4)" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                    <?php if ($DefaultPrivacy != 4) : ?>
                                                                        <span class="caret"></span>
                                                                    <?php endif; ?>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right dropdown-withicons dropdown-fullwidth privacy-dd" role="menu">
                                                                    <li class="list-head text-center"><span>Who should see this?</span></li>
                                                                    <?php if ($DefaultPrivacy == 1) : ?>
                                                                        <li ng-if="edit_post" ng-if="edit_post_details.Visibility == 1">
                                                                            <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(1);" onClick="$('#visible_for').val(1);">
                                                                                <i class="ficon-globe"></i>Everyone
                                                                                <span class="" ng-bind="'Anyone on '+lang.web_name">Anyone on {{lang.web_name}}</span>
                                                                            </a>
                                                                        </li>
                                                                        <li ng-if="!edit_post">
                                                                            <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(1);">
                                                                                <i class="ficon-globe"></i>Everyone
                                                                                <span class="" ng-bind="'Anyone on '+lang.web_name">Anyone on {{lang.web_name}}</span>
                                                                            </a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <?php if ($DefaultPrivacy < 4) : ?>
                                                                        <li ng-if="SettingsData.m10=='1' && edit_post && edit_post_details.Visibility < 1">
                                                                            <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(3);" onClick="$('#visible_for').val(3);">
                                                                                <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                                <span class="" ng-bind="'Only Me + Friends of ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                            </a>
                                                                        </li>
                                                                        <li ng-if="SettingsData.m10=='1' && !edit_post && isFriend()">
                                                                            <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(3);">
                                                                                <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                                <span class="" ng-bind="'Only Me + Friends of ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                            </a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                    <li>
                                                                        <a ng-click="setPrivacyHelpTxt(4); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(4);">
                                                                            <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>Only Me
                                                                            <span> (+)</span>
                                                                            <span class="" ng-bind="'Only me + ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Only me and anyone tagged</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <!-- For Pages -->
                                                    <?php if (isset($IsPage) && $IsPage == '1' && (!$this->settings_model->isDisabled(18))) { ?>
                                                        <li ng-if="!edit_post" ng-cloak>
                                                            <div class="dropdown" ng-init="getEntityList()">
                                                                <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                                    <span class="user-img-icon post-as-data" id=""> 
                                                                        <img ng-if="!hideImg" err-name="{{PostAs.Name}}" class="img-circle page-def-image" title="{{PostAs.Name}}" alt="{{PostAs.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/36x36/{{PostAs.ProfilePicture}}">
                                                                        <span class="spacel-icon"> <i class="caret"></i> </span>
                                                                    </span>
                                                                </button>
                                                                <div class="postasDropdown mCustomScrollbar dropdown-menu" role="menu">
                                                                    <ul class=" dropwith-img pull-left" role="menu">
                                                                        <li ng-repeat=" entitylist in  entityList" ng-click="set_post_as(entitylist)">
                                                                            <a href="javascript:void(0);">
                                                                                <span class="mark-icon">
                                                                                    <img err-name="{{entitylist.Name}}" class="img-circle" title="User" alt="User" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/36x36/{{entitylist.ProfilePicture}}">
                                                                                </span> {{entitylist.Name}}
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php } ?>
                                                    <!-- For Pages -->


                                                    <li ng-cloak ng-if="activePostType!='8' && activePostType!='9'">


                                                        <div class="btn-group">                            
                                                            <button ng-disabled=" (isWallAttachementUploading || noContentToPost || summernoteBtnDisabler)" class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">
                                                                Post                                  
                                                            </button>
                                                            <button ng-disabled=" (isWallAttachementUploading || noContentToPost || summernoteBtnDisabler)" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="ficon-arrow-down"></i></button>
                                                            <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>                                
                                                            <ul class="dropdown-menu">
                                                                <li><a ng-click="SubmitWallpost();">Post</a></li>
                                                                <li><a ng-click="showPreview()">Preview</a></li>
                                                                <li ng-hide="edit_post && singleActivity.StatusID == '2'"><a ng-click="saveDraft();">Save as draft</a></li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                    <li ng-cloak ng-if="activePostType=='8'">
                                                        <div class="btn-group">                            
                                                            <button ng-disabled="VisualPost.PostTitle=='' || VisualPost.PostContent=='' || VisualPost.Facts==''" class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">
                                                                Post                                  
                                                            </button>
                                                            <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                                                        </div>
                                                    </li>
                                                    <li ng-cloak ng-if="activePostType=='9'">
                                                        <div class="btn-group">                      
                                                            <button ng-disabled="Contest.PostTitle=='' || Contest.PostContent=='' || Contest.ButtonText=='' || Contest.ContestDate=='' || Contest.ContestTime=='' || Contest.NoOfWinners<1" class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">
                                                                Post                                  
                                                            </button>
                                                            <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                                                        </div>
                                                    </li>
                                                </ul>

                                                <input type="hidden" name="Status" id="status" value="2" />
                                                <input type="hidden" name="PostType" id="post_type" ng-value="activePostType" />
                                                <input ng-if="!edit_post" type="hidden" name="Visibility" id="visible_for" value="<?php echo isset($DefaultPrivacy) ? $DefaultPrivacy : 1; ?>" />
                                                <input ng-if="edit_post" type="hidden" name="Visibility" id="visible_for2" ng-value="edit_post_details.Visibility" />
                                                <input type="hidden" ng-if="!edit_post" name="Commentable" id="comments_settings" value="1" />
                                                <input type="hidden" ng-if="edit_post" name="Commentable" id="comments_settings2" ng-value="edit_post_details.CommentsAllowed" />
                                                <input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
                                                <input type="hidden" name="ModuleEntityOwner" id="module_entity_owner" value="0" /> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $this->load->view('include/post/preview') ?>

                    </div>
                </div>
            </div>






        </div>
    </div>
</form>
