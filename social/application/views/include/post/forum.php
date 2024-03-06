<form id="wallpostform" method="post" ng-hide="config_detail.ModuleID == '18' && pageDetails.IsFollowed == '0'" ng-show="!IsSingleActivity || edit_post" ng-cloak>
    <div ng-class="(!IsSingleActivity) ? 'post-type-view' : '';" ng-hide="IsSingleActivity && !postEditormode">
        <div class="modal fade" aria-hidden="false" tabindex="-1" id="postNewsFeedTypeModal" data-backdrop="static" ng-click="confirmCloseEditor($event)">
            <div class="modal-dialog no-modal-close-container modal-emoji-fixed" ng-class="(SettingsData.m40=='1') ? 'modal-mlg' : 'modal-lg post-without-editor' ;">
                <div class="modal-content">


                    <div class="post-type" ng-show="postEditormode && ShowPreview == '0'" ng-cloak>
                        <div class="post-type-info">
                            <span ng-bind="lang.exit_fullscreen_view"></span>
                        </div>
                        <div ng-if="!edit_post && config_detail.ModuleID != '14' && config_detail.ModuleID != '18'" class="post-header">
                            <ul ng-if="override_post_permission.length == 0" class="post-type-tab">
                                <li ng-repeat="type in ContentTypes" repeat-done="updateActivePostTypeDefault(ContentTypes)"><a target="_self" ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                            </ul>
                            <ul ng-if="override_post_permission.length > 0" class="post-type-tab">
                                <li ng-repeat="type in override_post_permission" repeat-done="updateActivePostTypeDefault(override_post_permission)"><a target="_self" ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                            </ul>
                        </div>

                        <div ng-if="edit_post && config_detail.ModuleID != '14' && config_detail.ModuleID != '18'" class="post-header">
                            <ul class="post-type-tab"><li><a target="_self" class="selected" ng-bind="allow_post_types[activePostType]"></a></li></ul>
                        </div>

                        <div ng-if="config_detail.ModuleID == '14' || config_detail.ModuleID == '18'" class="post-header">
                            <ul ng-if="override_post_permission.length == 0" class="post-type-tab">
                                <li ng-repeat="type in ContentTypes" ng-if="type.Value == '1'" repeat-done="updateActivePostTypeDefault(ContentTypes)"><a target="_self" ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                            </ul>
                            <ul ng-if="override_post_permission.length > 0" class="post-type-tab">
                                <li ng-repeat="type in override_post_permission" ng-if="type.Value == '1'" repeat-done="updateActivePostTypeDefault(override_post_permission)"><a target="_self" ng-click="updateActivePostType(type.Value);" ng-class="(type.Value == activePostType) ? 'selected' : '';" ng-bind="type.Label"></a></li>
                            </ul>
                        </div>

                        <div class="post-section">
                            <div ng-show="SettingsData.m40=='1'" class="post-title">
                                <input id="PostTitleInput" maxlength="140" ng-class="get_title_class()" onkeyup="if (140 - this.value.length == 1){ $('#PostTitleLimit').html(140 - this.value.length + ' character remaining'); } else { $('#PostTitleLimit').html(140 - this.value.length + ' characters'); }" name="PostTitle" ng-keyup="titleKeyup = '1';" ng-init="titleKeyup = '0'" type="text" class="form-control post-placeholder" placeholder="Post Title" required ng-model="postTitle"> 
                                <span id="PostTitleLimit" class="place-holder">140 characters</span>
                            </div>
                            <div class="post-content">
                                <div id="postEditor" class="textarea">
                                    <div ng-cloak ng-if="SettingsData.m40!='1'" class="post-thumbnail">
                                        <div class="list-items-sm"> 
                                          <div class="list-inner">
                                            <figure>
                                                <img ng-if="LoggedInProfilePicture" err-src="{{ImageServerPath + 'upload/profile/220x220/' + LoggedInProfilePicture}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+LoggedInProfilePicture}}" class="img-circle" alt="{{LoggedInFirstName+' '+LoggedInLastName}}" title="{{LoggedInFirstName+' '+LoggedInLastName}}">
                                                <img ng-if="+LoggedInProfilePicture==0 && LoginSessionKey==''" ng-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" class="img-circle" alt="{{LoggedInFirstName+' '+LoggedInLastName}}" title="{{LoggedInFirstName+' '+LoggedInLastName}}">
                                                <span class="thumb-alpha" ng-if="LoginSessionKey!='' && (LoggedInProfilePicture=='' || LoggedInProfilePicture=='user_default.jpg')">
                                                    <span ng-style="RandomBG" class="default-thumb">
                                                        <span class="default-thumb-placeholder" ng-bind="getInitials(LoggedInFirstName,LoggedInLastName)"></span>
                                                    </span>
                                                </span>
                                            </figure>
                                            <div class="list-item-body">
                                              <h4 class="list-heading-xs bold"><a class="ellipsis" ng-bind="FirstName+' '+LastName"></a></h4>                    
                                            </div>
                                          </div>
                                        </div>
                                    </div>
                                    <summernote  ng-model="PostContent" on-init="summernoteDropdown();" data-posttype="Post" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); parseTaggedInfo(contents); onSummerNoteChange(evt);" id="PostContent" config="options" on-image-upload="imageUpload(files)"></summernote>
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
<!--                                            <span ng-hide="media.progress" class="loader"></span>-->
                                            <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                        </li>
                                        <li ng-repeat="(mediaKey, media) in medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" ng-class="{ selected : (mediaInputIndex === media.data.MediaGUID) }">
                                            <img ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" >

                                            <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="videoprocess">
                                                <a target="_self" class="active"><span></span></a>
                                            </div>

                                            <i ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="ficon-cross"></i>
<!--                                            <span ng-hide="media.progress" class="loader"></span>-->
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
<!--                                            <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                            <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                            <i  class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                            <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                            <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('edit_file', fileKey, file.data.MediaGUID)"></i>
                                        </li>
                                        <li ng-repeat="(fileKey, file) in files">
<!--                                            <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                            <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                            <i class="ficon-file-type" ng-class="file.data.MediaExtension || file.ext"><span ng-bind="'.' + (file.data.MediaExtension || file.ext)"></span></i>
                                            <span class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                            <i class="ficon-cross" ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)"></i>
                                        </li>
                                    </ul>
                                </div>

                                <div ng-show="IsNewsFeed == '1' && !edit_post" class="tags-section">
                                    <div class="dropable sortable" droppable='items.list1' ng-move='moveObject(from, to, fromList, toList)' ng-create='createObject(object, to, list)'></div>
                                    <div class="groups-tag groups-tag-list" id="list1"> 
                                        <div class="form-control">
                                            <tags-input ng-class="{'disabled-input': tagsto.length > 0}" ng-model="tagsto" add-from-autocomplete-only="true" display-property="Name" placeholder="Select category" replace-spaces-with-dashes="false" limit-tags="1" template="tagTemplateUser"  maxTags="1" minTags="1" mintags="1">
                                                <auto-complete debounce-delay="500" source="loadVisibleCategorylist($query)" min-length="0" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                            </tags-input>
                                            <script type="text/ng-template" id="tagTemplateUser">
                                                <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                                                <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                                                <a target="_self" class="ficon-cross tag-remove ng-scope" ng-click="$removeTag()"></a>
                                                </div>
                                            </script>
                                            <script type="text/ng-template" id="userlistTemplate">
                                                <div class="list-items-xs">
                                                <div class="list-inner">
                                                <figure>
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{ImageServerPath+'upload/profile/220x220/'+data.ProfilePicture}}" >
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" >
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
                                            <span ng-if=" (tagsto.length > 0)" class="place-holder"></span>
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
                                <div ng-cloak ng-if="SettingsData.m40=='1'" class="post-footer-inner">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-5">
                                            <div class="checkbox-list">
                                                <div ng-cloak ng-if="!edit_post" class="checkbox checkbox-inline">
                                                    <input type="checkbox" value="" id="dCommenting">
                                                    <label for="dCommenting">Disable Commenting</label>
                                                </div>
                                                <div ng-cloak ng-if="edit_post" class="checkbox checkbox-inline">
                                                    <input type="checkbox" ng-checked="!edit_post_details.CommentsAllowed" id="dCommenting">
                                                    <label for="dCommenting">Disable Commenting</label>
                                                </div>

                                                <div ng-if="!edit_post" class="checkbox checkbox-inline" ng-class="{'hide' : !memTagCount}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                    <input id="notifyAll" type="checkbox" value="1" ng-model="NotifyAll">
                                                    <label for="notifyAll">Notify all group members</label>
                                                </div>
                                                <div ng-if="edit_post" class="checkbox checkbox-inline" ng-class="{'hide' : data.IsEntityOwner == '0' || data.ModuleID !== '1'}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                                    <input id="notifyAll" type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-model="edit_post_details.NotifyAll">
                                                    <label for="notifyAll">Notify all group members</label>
                                                </div>
                                            </div>


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
                                                        <button ng-disabled=" (isWallAttachementUploading || noContentToPost || summernoteBtnDisabler)" class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">
                                                            Post                                  
                                                        </button>
                                                        <button ng-disabled=" (isWallAttachementUploading || noContentToPost || summernoteBtnDisabler)" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="ficon-arrow-down"></i></button>
                                                        <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>                                
                                                        <ul class="dropdown-menu">
                                                            <li><a target="_self" ng-click="SubmitWallpost();">Post</a></li>
                                                            <li><a target="_self" ng-click="showPreview()">Preview</a></li>
                                                            <li ng-hide="edit_post && singleActivity.StatusID == '2'"><a target="_self" ng-click="saveDraft();">Save as draft</a></li>
                                                        </ul>
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
                                <!-- New -->
                                <div ng-cloak ng-if="SettingsData.m40=='0'" class="post-footer-inner">
                                      <div class="row gutter-5">
                                          <div class="col-sm-3 col-xs-2 post-footer-left">                  
                                          <a target="_self" ng-href="{{SiteUrl+'user_profile/post_article'}}" class="btn btn-default btn-sm" ng-cloak ng-if="config_detail.ModuleID=='34' && (config_detail.IsAdmin || config_detail.IsSuperAdmin) && SettingsData.m38=='1'" ng-hide="show_privacy">
                                            <span class="icon">
                                              <i class="ficon-pencil f-md"></i>
                                            </span>
                                            <span class="text hidden-xs">Write an Article</span>
                                          </a>                 
                                        </div>
                                        <div class="col-sm-9 col-xs-10 post-footer-right">                 
                                          <ul class="post-buttons">

                                            <li>
                                              <a class="btn btn-default btn-sm" ngf-select="uploadWallFiles($files, $invalidFiles)" multiple='multiple' ngf-validate-async-fn="validateFileSize($file);">
                                                <span class="icon"> 
                                                  <i class="ficon-image-media"></i>
                                                </span>
                                                <span class="text hidden-xs">Photo/Video</span>
                                              </a>
                                            </li>
                                            <li>
                                              <a class="btn btn-default btn-sm" ng-click="updateTagList(!addTagList)">
                                                <span class="icon">
                                                  <i class="ficon-user-tag f-lg"></i>
                                                </span>
                                                <span class="text hidden-xs">Tag</span>
                                              </a>
                                            </li>

                                            <li ng-cloak ng-hide="!show_privacy" ng-if="(edit_post && !postInGroup && (tagsto.length === 0) && edit_post_details.ModuleID!='34') && activePostType!='8' && activePostType!='9' && ModuleID == 3 && IsAdmin == 1" ng-init="setActiveIconToPrivacy(selectedPrivacy);">
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
                                                                <span class="" ng-bind="'Anyone on VSocial'">Anyone on VSocial</span>
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
                                            <li ng-cloak ng-hide="!show_privacy" ng-if="!edit_post && (tagsto.length === 0) && activePostType!='8' && activePostType!='9' && ModuleID == 3 && IsAdmin == 1" tooltip data-placement="top" data-container="body" ng-attr-data-original-title="{{ selectedPrivacyTooltip}}" ng-init="setActiveIconToPrivacy(selectedPrivacy);">

                                                <div class="btn-group custom-icondrop">
                                                    
                                                    
                                                    
                                                        <button ng-if="wlEttDt.LoggedInUserID == wlEttDt.CreaterUserID" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                            <i ng-if="selectedPrivacy == 1" id="IconSelect" class="ficon-globe"></i>
                                                            <i ng-if="selectedPrivacy == 3 && SettingsData.m10=='1'" id="IconSelect" class="" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                            <i ng-if="selectedPrivacy == 4" id="IconSelect" class="" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                            <i class="ficon-arrow-down"></i>
                                                        </button>
                                                    
                                                    
                                                    
                                                    
                                                    
                                                        <button  ng-if="wlEttDt.LoggedInUserID != wlEttDt.CreaterUserID" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">                                                                        
                                                                    <i id="IconSelect" class="ficon-globe" ng-class="selectedPrivacyClass" ng-if="DefaultPrivacy == 1"></i>                                                                           
                                                                    <i id="IconSelect" class="icon-follwers" ng-class="selectedPrivacyClass" ng-if="DefaultPrivacy == 2"></i>                                                                            
                                                                    <i id="IconSelect" class="ficon-friends" ng-if="SettingsData.m10=='1' && DefaultPrivacy == 3" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>                                                                            
                                                                    <i id="IconSelect" class="ficon-user" ng-if="DefaultPrivacy == 4" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>                                                                            
                                                            <i class="ficon-arrow-down"></i>
                                                        </button>
                                                    
                                                    
                                                    
                                                    
                                                    <ul class="dropdown-menu dropdown-menu-right dropdown-withicons dropdown-fullwidth privacy-dd" role="menu">
                                                        <li class="list-head text-center"><span>Who should see this?</span></li>
                                                        <li>
                                                            <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(1);">
                                                                <i class="ficon-globe"></i>Everyone
                                                                <span class="" ng-bind="'Anyone on VSocial'">Anyone on VSocial</span>
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
                                            <li ng-cloak ng-hide="!show_privacy" ng-if="ModuleID == 3 && IsAdmin != 1" ng-cloak ng-show="activePostType!='8' && activePostType!='9'" ng-init="setPrivacyHelpTxt(DefaultPrivacy); setActiveIconToPrivacy(DefaultPrivacy);" tooltip ng-attr-title="(selectedPrivacy == 1) ? 'Anyone on VSocial' : (selectedPrivacy == 3) ? 'Only Me + Friends of '+ FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) : 'Only me + ' + FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) ;" data-placement="top" >
                                                
                                                    <button ng-if="DefaultPrivacy == 4" tooltip data-placement="top" ng-attr-data-original-title="{{'Only me + ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)}}" type="button" class="btn btn-default">
                                                   
                                                    <button ng-if="DefaultPrivacy != 4" tooltip data-placement="top" ng-attr-data-original-title="{{(selectedPrivacy == 1) ? 'Anyone on VSocial' : (selectedPrivacy == 3) ? 'Only Me + Friends of '+ FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) : 'Only me + ' + FirstName + ( ( taggedHelpTxtSuffix == '' ) ? '' : ' + ' + taggedHelpTxtSuffix ) ;}}" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                        
                                                        
                                                            
                                                                <i ng-if="selectedPrivacy == 'false' && DefaultPrivacy == 1" id="IconSelect" class="ficon-globe"></i>
                                                                <i ng-if="selectedPrivacy == '1' && DefaultPrivacy == 1" id="IconSelect" class="ficon-globe"></i>
                                                                <i ng-if="selectedPrivacy == '3' && SettingsData.m10=='1' && DefaultPrivacy == 1" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                <i ng-if="selectedPrivacy == '4' && DefaultPrivacy == 1" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                            
                                                                
                                                            
                                                                <i ng-init="set_default_privacy(3)" ng-if="SettingsData.m10=='1' && selectedPrivacy == 'false' && (DefaultPrivacy == 1 || DefaultPrivacy == 3)" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                <i ng-init="set_default_privacy(3)" ng-if="SettingsData.m10=='1' && (selectedPrivacy == '3' || selectedPrivacy == '1') && (DefaultPrivacy == 1 || DefaultPrivacy == 3)" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>
                                                                <i ng-if="selectedPrivacy == '4' && (DefaultPrivacy == 1 || DefaultPrivacy == 3)" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                            
                                                                <i ng-init="set_default_privacy(4) && !(DefaultPrivacy == 1 || DefaultPrivacy == 3)" id="IconSelect" ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>
                                                            
                                                        
                                                        <span class="caret" ng-if="DefaultPrivacy != 4"></span>
                                                        
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right dropdown-withicons dropdown-fullwidth privacy-dd" role="menu">
                                                        <li class="list-head text-center"><span>Who should see this?</span></li>
                                                        
                                                            <li ng-if="edit_post" ng-if="edit_post_details.Visibility == 1 && DefaultPrivacy == 1">
                                                                <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(1);" onClick="$('#visible_for').val(1);">
                                                                    <i class="ficon-globe"></i>Everyone
                                                                    <span class="" ng-bind="::'Anyone on VSocial'"></span>
                                                                </a>
                                                            </li>
                                                            <li ng-if="!edit_post && DefaultPrivacy == 1">
                                                                <a ng-click="setPrivacyHelpTxt(1); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(1);">
                                                                    <i class="ficon-globe"></i>Everyone
                                                                    <span class="" ng-bind="::'Anyone on VSocial'"></span>
                                                                </a>
                                                            </li>
                                                        
                                                        
                                                        
                                                       
                                                            <li ng-if="SettingsData.m10=='1' && edit_post && edit_post_details.Visibility < 1 && DefaultPrivacy < 4">
                                                                <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(3);" onClick="$('#visible_for').val(3);">
                                                                    <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                    <span class="" ng-bind="'Only Me + Friends of ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                </a>
                                                            </li>
                                                            <li ng-if="SettingsData.m10=='1' && !edit_post && isFriend() && DefaultPrivacy < 4">
                                                                <a ng-click="setPrivacyHelpTxt(3); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(3);">
                                                                    <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-friends-plus' : 'ficon-friends';"></i>Friends
                                                                    <span class="" ng-bind="'Only Me + Friends of ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Your friends and anyone tagged</span>
                                                                </a>
                                                            </li>
                                                        
                                                        
                                                        
                                                        <li>
                                                            <a ng-click="setPrivacyHelpTxt(4); setActiveIconToPrivacy(selectedPrivacy);" onClick="$('#visible_for').val(4);">
                                                                <i ng-class="(taggedEntityInfoCount > 0) ? 'ficon-user-plus' : 'ficon-user';"></i>Only Me
                                                                <span> (+)</span>
                                                                <span class="" ng-bind="'Only me + ' + FirstName + ((taggedHelpTxtSuffix == '') ? '' : ' + ' + taggedHelpTxtSuffix)">Only me and anyone tagged</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                            </li>

                                            <li>
                                                <div class="btn-group">                            
                                                    <button ng-disabled="(isWallAttachementUploading || noContentToPost)" class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">
                                                        Post                                  
                                                    </button>
                                                    <button ng-disabled="(isWallAttachementUploading || noContentToPost)" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="ficon-arrow-down"></i></button>
                                                    <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>                                
                                                    <ul class="dropdown-menu">
                                                        <li><a ng-click="SubmitWallpost();">Post</a></li>
                                                        <li><a ng-click="showPreview()">Preview</a></li>
                                                        <li ng-hide="edit_post && singleActivity.StatusID == '2'"><a ng-click="saveDraft();">Save as draft</a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                          </ul>
                                        </div>
                                      </div>
                                        <input type="hidden" name="Status" id="status" value="2" />
                                        <input type="hidden" name="PostType" id="post_type" ng-value="activePostType" />
                                        <input ng-if="!edit_post" type="hidden" name="Visibility" id="visible_for" value="{{DefaultPrivacy}}" />
                                        <input ng-if="edit_post" type="hidden" name="Visibility" id="visible_for2" ng-value="edit_post_details.Visibility" />
                                        <input type="hidden" ng-if="!edit_post" name="Commentable" id="comments_settings" value="1" />
                                        <input type="hidden" ng-if="edit_post" name="Commentable" id="comments_settings2" ng-value="edit_post_details.CommentsAllowed" />
                                        <input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
                                        <input type="hidden" name="ModuleEntityOwner" id="module_entity_owner" value="0" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('include/post/preview') ?>

                </div>
            </div>
        </div>
    </div>
</form>