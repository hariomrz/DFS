<form id="wallpostform" method="post" ng-hide="config_detail.ModuleID=='18' && pageDetails.IsFollowed=='0'" ng-show="!IsSingleActivity || edit_post" ng-cloak>
<div ng-class="(!IsSingleActivity) ? 'post-type-view' : '' ;" ng-hide="IsSingleActivity && !postEditormode">
    <div class="stiky-overlay" data-type="post-overlay" ng-click="confirmCloseEditor()" ng-class="{active : overlayShow}"></div>
    <div ng-show="ShowPreview=='0'">
        <div id="posttypeContent" class="post-type-block" ng-show="postTypeview == 1" ng-cloak> 
            <div class="post-type">
                <ul class="post-type-list" data-type="post-type-list">
                    <li ng-if="override_post_permission.length==0" class="col-xs-4" ng-repeat="type in ContentTypes" ng-class="(type.Value==activePostType) ? 'active' : '' ;" ng-click="updateActivePostType(type.Value); showPostEditor();">
                        <span class="icon">
                         <svg height="30px" width="30px" class="svg-icons">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="" ng-href="{{AssetBaseUrl}}img/sprite.svg#{{getPostIcon(type.Value)}}"></use>
                          </svg>
                     </span>
                        <span class="title" ng-bind="type.Label"></span>
                    </li>

                    <li ng-if="override_post_permission.length==0 && ContentTypes.length==4" class="col-xs-4"></li>
                    <li ng-if="override_post_permission.length==0 && ContentTypes.length==4" class="col-xs-4"></li>

                    <li ng-if="override_post_permission.length>0" class="col-xs-4" ng-repeat="type in override_post_permission" ng-class="(type.Value==activePostType) ? 'active' : '' ;" ng-click="updateActivePostType(type.Value); showPostEditor();">
                        <span class="icon">
                         <svg height="30px" width="30px" class="svg-icons">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="" ng-href="{{AssetBaseUrl}}img/sprite.svg#{{getPostIcon(type.Value)}}"></use>
                          </svg>
                     </span>
                        <span class="title" ng-bind="type.Label"></span>
                    </li>
                    <li ng-if="override_post_permission.length>0 && override_post_permission.length==4" class="col-xs-4"></li>
                    <li ng-if="override_post_permission.length>0 && override_post_permission.length==4" class="col-xs-4"></li>
                </ul>
            </div>
        </div> 
        <div class="post-editor" id="postEditor" ng-cloak> 
            <div class="loader postEditorLoader" style="top:30%;">&nbsp;</div>
            <div class="post-ws-editor">
                <div class="current-selected" data-type="post-type">
                    <span class="icon">
                    <svg height="16px" width="16px" class="svg-icons">
                      <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="" ng-href="{{SiteURL+'assets/img/sprite.svg#icnKnowledge'}}"></use>
                    </svg>
                  </span>
                </div>
                <summernote ng-model="PostContent" data-posttype="Post" on-change="change(contents);" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0)" id="PostContent" config="options" on-image-upload="imageUpload(files)"></summernote>
                <div class="post-title">
                    <input id="PostTitleInput" maxlength="140" onkeyup="$('#PostTitleLimit').html(140-this.value.length+' characters remaining');" name="PostTitle" ng-keyup="titleKeyup='1';" ng-init="titleKeyup='0'" type="text" class="form-control post-placeholder" required ng-model="postTitle"> 
                    <label for="PostTitleInput">Post Title</label>
                    <span id="PostTitleLimit" class="place-holder"></span>
                </div>
                <div class="network-wrapper network-scroll mCustomScrollbar" ng-if="parseLinks.length>0" ng-cloak>
                    <div class="network-view">
                        <div ng-repeat="parseLink in parseLinks" repeat-done="callScrollBar();" class="network-block clearfix">
                            <a ng-click="removeParseLink(parseLink.URL)" class="removeNerwork">
                                <i class="ficon-cross"></i>
                            </a>
                            <div class="network-media-block networkmediaList">
                                <div class="slider-wrap">
                                    <ul class="networkmedia" data-uix-bxslider="mode: 'horizontal', pager: false, controls: true, minSlides: 1, maxSlides:1, slideWidth: 170, slideMargin:0, infiniteLoop: false, hideControlOnEnd: false" ng-show="parseLink.Thumbs.length>0">
                                        <li ng-repeat="img in parseLink.Thumbs" notify-when-repeat-finished>
                                            <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="{{'<?php echo site_url() ?>'+img}}"  />
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="network-media-detail">
                                <div ng-class="(showEditable.Title=='1') ? 'edit-mode' : '' ;" class="network-title">
                                    <a ng-dblclick="showEditableVal('Title',1)" ng-show="showEditable.Title==0" class="name" href="javascript:void(0);" ng-bind="parseLink.Title"></a>
                                    <input ng-keypress="enterUrl($event)" ng-show="showEditable.Title==1" type="text" class="form-control" ng-model="parseLink.Title">
                                    <a ng-click="showEditable.Title=0;" class="removeChoice">
                                        <i class="ficon-cross"></i>
                                    </a>
                                </div>
                                <span class="network-url" ng-bind="parseLink.URL"></span>
                                <div ng-class="(showEditable.Tags=='1') ? 'edit-mode' : '' ;" class="tag-option">
                                    <a ng-click="showEditableVal('Tags',1)" ng-show="showEditable.Tags==0" class="name" href="javascript:void(0);">Add Tags</a>
                                    <div ng-show="showEditable.Tags==1" class="networkTag">
                                        <tags-input ng-model="linktagsto[parseLink.URL]" key-property="Name" display-property="Name" placeholder="add tags separated by commas" replace-spaces-with-dashes="false">
                                            <auto-complete source="loadLinkTags($query)" min-length="0" load-on-focus="false" load-on-empty="true" max-results-to-show="3"></auto-complete>
                                        </tags-input>
                                        <a ng-click="showEditable.Tags=0;" class="removeChoice">
                                            <svg height="10px" width="10px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#closeIcn'}}"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="network-subinfo">
                                    <span class="mediaCount" ng-if="parseLink.Thumbs.length>1" ng-bind="parseLink.Thumbs.length+' Images'"></span>
                                    <div class="checkbox check-primary">
                                        <input ng-model="parseLink.HideThumb" type="checkbox" id="NoThumbnail">
                                        <label for="NoThumbnail">No Thumbnail</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-for-post">
                    <div class="media-upload-view" style="display:block;" ng-cloak ng-show="(mediaCount > 0) || (fileCount > 0)">
                        <ul class="upload-listing" id="listingmedia" ng-show="(mediaCount > 0)">
                            <li ng-show="(mediaCount > 1)" ng-click="setSaySomethingAboutMedia('ALL')" class="selected-capt all-con" ng-class="{ selected : ( ( mediaInputIndex === 'ALL' ) || ( mediaInputIndex === '' ) ) }" style="display : block;">
                                <div data-rel="allshow" class="active media-holder">
                                    <a id="m-default" onClick="toggleMediaCaption('default')" data-rel="allshow">
                                        <div class="alltext">ALL
                                            <label class="capt-num" ng-cloak ng-bind="(mediaCount | number:0)"></label>
                                        </div>
                                    </a>
                                </div>
                            </li>
                            <li ng-repeat="(key, media) in edit_medias" ng-if="media" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" class="photo-itm selected-capt media-item" ng-class="{ selected : ( mediaInputIndex === media.data.MediaGUID ) }">
<!--                                <div ng-hide="media.progress" class="active media-holder">
                                    <div class="loader loader-attach-file" style="display:block"></div>
                                </div>-->
                                <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                <div ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" class="media-holder">
                                    <a class="active">
                                        <span>
                                        <img ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" >
                                    </span>
                                    </a>
                                </div>
                                <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="media-holder videoprocess">
                                    <a class="active"><span></span></a>
                                </div>
                                <mark ng-show="media.progress" ng-click="removeWallAttachement('edit_media', key, media.data.MediaGUID)" class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                            </li>
                            <li ng-repeat="(mediaKey, media) in medias" ng-click="setSaySomethingAboutMedia(media.data.MediaGUID)" class="photo-itm selected-capt media-item" ng-class="{ selected : ( mediaInputIndex === media.data.MediaGUID ) }">
<!--                                <div ng-hide="media.progress" class="active media-holder">
                                    <div class="loader loader-attach-file" style="display:block"></div>
                                </div>-->
                                <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                <div ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" class="media-holder">
                                    <a class="active">
                                        <span>
                                        <img ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" >
                                    </span>
                                    </a>
                                </div>
                                <div ng-if="(media.data.MediaType != 'PHOTO')" ng-show="media.progress" style="background:#ddd;" class="media-holder videoprocess">
                                    <a class="active"><span></span></a>
                                </div>
                                <mark ng-show="media.progress" ng-click="removeWallAttachement('media', mediaKey, media.data.MediaGUID)" class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                            </li>
                        </ul>
                        <ul class="attached-files files-attached-in-post" ng-show="(fileCount > 0)">
                            <li ng-repeat="(fileKey, file) in edit_files">
<!--                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                <span class="file-type {{ file.data.MediaExtension || file.ext }}">
                                  <svg class='svg-icon' width='26px' height='28px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='{{SiteURL+'assets/img/sprite.svg#fileIcon'}}'></use>
                                  </svg>
                                  <span ng-bind=" '.' + (file.data.MediaExtension || file.ext) "></span>
                                </span>
                                <span class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                <i ng-show="file.progress" ng-click="removeWallAttachement('edit_file', fileKey, file.data.MediaGUID)" class='dwonload  icon hover'>
                                  <svg class='svg-icons' width='20px' height='20px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='{{SiteURL+'assets/img/sprite.svg#closeIcon'}}'></use>
                                  </svg>
                              </i>
                            </li>
                            <li ng-repeat="(fileKey, file) in files">
<!--                                <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                <span  class="file-type {{ file.data.MediaExtension || file.ext }}">
                                  <svg class='svg-icon' width='26px' height='28px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='{{SiteURL+'assets/img/sprite.svg#fileIcon'}}'></use>
                                  </svg>
                                  <span ng-bind=" '.' + (file.data.MediaExtension || file.ext )"></span>
                                </span>
                                <span  class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                <i ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)" class='dwonload  icon hover'>
                                  <svg class='svg-icons' width='20px' height='20px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='{{SiteURL+'assets/img/sprite.svg#closeIcon'}}'></use>
                                  </svg>
                              </i>
                            </li>
                        </ul>
                    </div>
                    <div ng-show="( ( mediaCount > 0 ) && !isWallAttachementUploading )" ng-cloak style="display:block;" class="comments same-caption about-media">
                        <textarea ng-model="saySomthingAboutMedia[mediaInputIndex]" id="mc-default" class="form-control mc" placeholder="Say something about {{ ( ( mediaInputIndex == 'ALL' ) && ( mediaCount > 1 ) ) ? 'these media.' : 'this media.' }}"></textarea>
                    </div>
                </div>
                <!-- Wall Actions-->
                <div class="post-actions clearfix">
                    <div>
                        <div ng-show="IsNewsFeed=='1' && (!edit_post || singleActivity.StatusID=='10')"  class="select-tags">
                            <div class="group-contacts">
                                <div class="dropable sortable" droppable='items.list1' ng-move='moveObject(from, to, fromList, toList)' ng-create='createObject(object, to, list)'></div>
                                <div class="input-group groups-tag" id="list1">
                                    <span class="input-group-addon"><i class="icon-n-memeber" title="Select contacts or groups to start a new conversation." data-toggle="tooltip" data-placement="bottom"></i> </span>
                                    <div class="form-control">
                                        <tags-input ng-model="tagsto" add-from-autocomplete-only="true" display-property="name" placeholder="{{(tagsto.length>0) ? '' : 'Select Contacts' ;}}" replace-spaces-with-dashes="false" on-tag-added="tagAddedGU($tag)" on-tag-removed="tagRemovedGU($tag)" limit-tags="1">
                                            <auto-complete source="loadGroupFriendslist($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                        </tags-input>
                                        <script type="text/ng-template" id="userlistTemplate">
                                            <a href="javascript:void(0);" class="m-conv-list-ProfilePicture">
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{ImageServerPath + 'upload/profile/220x220/'+data.ProfilePicture}}" >
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" >
                                            </a>
                                            <a href="javascript:void(0);" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                            <span><i class="icon-lock" ng-if="data.ModuleID==1" ng-class="{'icon-n-closed':data.Privacy==0,'icon-n-group-secret':data.Privacy==2,'icon-n-global':data.Privacy==1}"></i></span>
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="select-tags" ng-show="addTagList">
                            <div class="group-contacts">
                                <div class="input-group groups-tag">
                                    <span class="input-group-addon">
                                        <span class="icon">
                                             <svg  class="svg-icons no-hover" height="16px" width="16px">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnTag'}}"></use>
                                            </svg>
                                        </span>
                                    </span>
                                    <div class="form-control">
                                        <tags-input ng-model="postTagList" display-property="Name" placeholder="Add tags" min-length="1" replace-spaces-with-dashes="false" template="tagsTemplate">
                                            <auto-complete source="getActivityTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="addTagDropdownTemplate"></auto-complete>
                                        </tags-input>
                                        <script type="text/ng-template" id="tagsTemplate">
                                            <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                                <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                                <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                            </div>
                                        </script>
                                        <script type="text/ng-template" id="addTagDropdownTemplate">
                                            <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="post-footer">
                                <div class="col-sm-4 notify-all">
                                  <div class="checkbox" ng-class="{'hide' : !memTagCount}" title="Everyone in this group will be subscribed to recieve notifications for this post." data-toggle="tooltip" data-placement="bottom">
                                      <input id="notifyAll" type="checkbox" value="1" ng-model="NotifyAll">
                                      <label for="notifyAll">Notify all group members</label>
                                  </div>
                            </div>
                                <div class="col-sm-8 col-xs-12">
                                    <div class="pull-right wall-btns">
                                        <ul class="wall-action-btn">
                                            <li class="icon-btn">
                                                <button tooltip data-placement="top" title="Add Tags" ng-click="addTagList = true;" type="button" class="btn btn-default">
                                                    <span class="icon">
                                                  <svg  class="svg-icons" height="16px" width="16px">
                                                       <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnTag'}}"></use>
                                                  </svg>
                                              </span>
                                                </button>
                                            </li>
                                            <li class="icon-btn">
                                              <button tooltip data-placement="top" title="Attach Media" type="button" class="btn btn-default" ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);">
                                                  <span class="icon">
                                                      <svg  class="svg-icons" height="20px" width="20px">
                                                      <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnAttachment'}}"></use>
                                                      </svg>
                                                  </span>
                                              </button>
                                            </li>
                                            <li>
                                                <?php if (isset($IsPage) && $IsPage == '1' && (!$this->settings_model->isDisabled(18))) { ?>
                                                <div class="dropdown" ng-init="getEntityList()">
                                                    <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                        <span class="user-img-icon" id=""> 
                                                    <img class="img-circle page-def-image" title="{{PostAs.Name}}" alt="{{PostAs.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/36x36/{{PostAs.ProfilePicture}}">
                                                    <span class="spacel-icon"> <i class="caret"></i> </span>
                                                        </span>
                                                    </button>
                                                    <div class="postasDropdown mCustomScrollbar dropdown-menu" role="menu">
                                                        <ul class=" dropwith-img pull-left" role="menu">
                                                            <li ng-repeat=" entitylist in  entityList" ng-click="set_post_as(entitylist)">
                                                                <a href="javascript:void(0);">
                                                                    <span class="mark-icon">
                                                                <img class="img-circle" title="User" alt="User" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/36x36/{{entitylist.ProfilePicture}}">
                                                            </span> {{entitylist.Name}}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </li>
                                            <li class="icon-btn" ng-if="activePostType!=='2'">
                                                <button tooltip data-placement="top" title="Comments On/Off" id="commentablePost" type="button" class="btn btn-default btn-onoff on"> <i class="ficon-comment f-lg"></i> </button>
                                            </li>
                                            <?php if(isset($ModuleID) && $ModuleID==3 && $IsAdmin==1) : ?>
                                            <li>
                                                <div class="btn-group custom-icondrop">
                                                    <button tooltip data-placement="top" title="Privacy" type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false">
                                                        <?php if (isset($DefaultPrivacy)) : ?>
                                                        <?php if ($DefaultPrivacy == 1) : ?>
                                                        <i id="IconSelect" class="icon-every"></i>
                                                        <?php endif; ?>
                                                        <?php if ($DefaultPrivacy == 2) : ?>
                                                        <i id="IconSelect" class="icon-follwers"></i>
                                                        <?php endif; ?>
                                                        <?php if ($DefaultPrivacy == 3) : ?>
                                                        <i id="IconSelect" class="icon-frnds"></i>
                                                        <?php endif; ?>
                                                        <?php if ($DefaultPrivacy == 4) : ?>
                                                        <i id="IconSelect" class="icon-onlyme"></i>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu  dropdown-withicons" role="menu">
                                                        <li><a onClick="$('#visible_for').val(1);"><span class="mark-icon"><i class="icon-every"></i></span>Everyone</a></li>
                                                        <!-- <li><a onClick="$('#visible_for').val(2);"><span class="mark-icon"><i class="icon-follwers"></i></span>Friends of Friends</a></li> -->
                                                        <li><a onClick="$('#visible_for').val(3);"><span class="mark-icon"><i class="icon-frnds"></i></span>Friends</a></li>
                                                        <li><a onClick="$('#visible_for').val(4);"><span class="mark-icon"><i class="icon-onlyme"></i></span>Only Me</a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <?php elseif(isset($ModuleID) && $ModuleID==3): ?>
                                            <li class="icon-btn" tooltip ng-attr-title="{{FirstName+' '+LastName}} controls who can see posts on their timeline" data-placement="top" >
                                                <button type="button" class="btn btn-default" disabled="">
                                                    <span class="icon">
                                                            <svg  class="svg-icons" height="20px" width="20px">
                                                                 <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnSettings'}}"></use>
                                                            </svg>
                                                        </span>
                                                </button>
                                            </li>
                                            <?php endif; ?>
                                            <li>
                                                <div class="btn-group post-button">
                                                    <button ng-disabled=" ( isWallAttachementUploading) " class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">Post </button>
                                                    <button ng-disabled=" ( isWallAttachementUploading) " class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                                    <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                                                    <ul class="dropdown-menu">
                                                          <li><a ng-click="SubmitWallpost();">Post</a></li>
                                                          <li><a ng-click="showPreview()">Preview</a></li>
                                                          <li ng-hide="edit_post && singleActivity.StatusID=='2'"><a ng-click="saveDraft();">Save as draft</a></li>
                                                    </ul>

                                                    <input type="hidden" name="Status" id="status" value="2" />
                                                    <input type="hidden" name="PostType" id="post_type" ng-value="activePostType" />
                                                    <input type="hidden" name="Visibility" id="visible_for" value="<?php echo isset($DefaultPrivacy) ? $DefaultPrivacy : 1 ; ?>" />
                                                    <input type="hidden" name="Commentable" id="comments_settings" value="1" />
                                                    <input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
                                                    <input type="hidden" name="ModuleEntityOwner" id="module_entity_owner" value="0" /> 
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <?php $this->load->view('include/post/preview') ?>
</div>
</form>
<?php $this->load->view('groups/multiple_instant_group_popup'); ?>