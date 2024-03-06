<?php
    $DefaultPrivacy = 1;
?>
    <form id="wallpostform" method="post" ng-hide="config_detail.ModuleID=='18' && pageDetails.IsFollowed=='0'" ng-show="!IsSingleActivity || edit_post" ng-cloak>
        <div ng-class="(!IsSingleActivity) ? 'post-type-view' : '' ;" ng-hide="IsSingleActivity && !postEditormode">
            
            
            
            
            <div class="stiky-overlay" data-type="post-overlay" id="postNewsFeedTypeModal" data-backdrop="static" ng-click="confirmCloseEditor($event);"  ng-class="{active : overlayShow}"></div>
            <div ng-show="ShowPreview=='0'">
                <div ng-cloak ng-hide="IsSingleActivity" class="wall-post" data-type="post-type">
                    <span ng-click="updateActivePostTypeDefault(postasuser.ContentTypes); showPostEditor(0, 1); setEditorPosition(0); setpostasuser(users[0]);">What's on your mind</span>
                    <div ng-hide="singleActivity.StatusID=='2' && edit_post" class="post-nav-icn icon">
                        <svg height="14px" width="14px" class="svg-icons" ng-click="viewPostType();updateActivePostTypeDefault(postasuser.ContentTypes);">
                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnPostnav"></use>
                        </svg>
                    </div>
                </div>
                <div id="posttypeContent" class="post-type-block" ng-show="postTypeview == 1" ng-cloak>
                    <div class="post-type">
                        <ul class="post-type-list" data-type="post-type-list">
                            <li class="col-xs-4" ng-if="override_post_permission.length==0" ng-cloak ng-repeat="type in postasuser.ContentTypes" ng-class="(type.Value==activePostType) ? 'active' : '' ;" ng-click="updateActivePostType(type.Value); showPostEditor();">
                                <span class="icon">
                         <svg height="30px" width="30px" class="svg-icons">
                            <use xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#{{getPostIcon(type.Value)}}"></use>
                          </svg>
                     </span>
                                <span class="title" ng-bind="type.Label"></span>
                            </li>
                            <li ng-if="postasuser.ContentTypes.length==4" class="col-xs-4"></li>
                            <li ng-if="postasuser.ContentTypes.length==4" class="col-xs-4"></li>
                            <li ng-if="override_post_permission.length>0" ng-cloak class="col-xs-4" ng-repeat="type in override_post_permission" ng-class="(type.Value==activePostType) ? 'active' : '' ;" ng-click="updateActivePostType(type.Value); showPostEditor();">
                                <span class="icon">
                         <svg height="30px" width="30px" class="svg-icons">
                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#{{getPostIcon(type.Value)}}"></use>
                          </svg>
                     </span>
                                <span class="title" ng-bind="type.Label"></span>
                            </li>
                            <li ng-if="override_post_permission.length>0 && override_post_permission.length==4" class="col-xs-4"></li>
                            <li ng-if="override_post_permission.length>0 && override_post_permission.length==4" class="col-xs-4"></li>
                        </ul>
                    </div>
                </div>
                <div class="post-editor" id="postEditor" ng-show="postEditormode" ng-cloak>
                    <div class="loader postEditorLoader" style="top:30%;">&nbsp;</div>
                    <div class="post-ws-editor">
                        <div class="current-selected" data-type="post-type" ng-click="viewPostType()">
                            <span class="icon">
                    <svg height="16px" width="16px" class="svg-icons">
                      <use xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#{{getPostIcon(activePostType)}}"></use>
                    </svg>
                  </span>
                        </div>
                        <summernote ng-model="PostContent" data-posttype="Post" on-change="change(contents);" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); onSummerNoteChange(evt);" id="PostContent" config="options" on-image-upload="imageUpload(files)"></summernote>
                        <div class="post-title">
                            <input id="PostTitleInput" maxlength="140" ng-class="get_title_class()" onkeyup="if(140-this.value.length==1){ $('#PostTitleLimit').html(140-this.value.length+' character remaining'); } else { $('#PostTitleLimit').html(140-this.value.length+' characters remaining'); }" name="PostTitle" ng-keyup="titleKeyup='1';" ng-init="titleKeyup='0'" type="text" class="form-control post-placeholder" required ng-model="postTitle">
                            <label for="PostTitleInput">Post Title</label>
                            <span id="PostTitleLimit" class="place-holder"></span>
                        </div>
                        <div class="network-wrapper network-scroll mCustomScrollbar" ng-if="parseLinks.length>0" ng-cloak>
                            <div class="network-view">
                                <div ng-repeat="parseLink in parseLinks" repeat-done="callScrollBar();" class="network-block clearfix">
                                    <a ng-click="removeParseLink(parseLink.URL)" class="removeNerwork">
                                        <svg height="10px" width="10px" class="svg-icons">
                                            <use xlink:href="assets/img/sprite.svg#closeIcn"></use>
                                        </svg>
                                    </a>
                                    <div class="network-media-block networkmediaList">
                                        <div class="slider-wrap">
                                            <ul class="networkmedia" data-uix-bxslider="mode: 'horizontal', pager: false, controls: true, minSlides: 1, maxSlides:1, slideWidth: 170, slideMargin:0, infiniteLoop: false, hideControlOnEnd: false" ng-show="parseLink.Thumbs.length>0">
                                                <li ng-repeat="img in parseLink.Thumbs" notify-when-repeat-finished>
                                                    <img err-SRC="{{SiteURL}}assets/img/profiles/user_default.jpg" ng-src="{{'<?php echo site_url() ?>'+img}}"  />
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="network-media-detail">
                                        <div ng-class="(showEditable.Title=='1') ? 'edit-mode' : '' ;" class="network-title">
                                            <a ng-dblclick="showEditableVal('Title',1)" ng-show="showEditable.Title==0" class="name" href="javascript:void(0);" ng-bind="parseLink.Title"></a>
                                            <input ng-keypress="enterUrl($event)" ng-show="showEditable.Title==1" type="text" class="form-control" ng-model="parseLink.Title">
                                            <a ng-click="showEditable.Title=0;" class="removeChoice">
                                                <svg height="10px" width="10px" class="svg-icons">
                                                    <use xlink:href="assets/img/sprite.svg#closeIcn"></use>
                                                </svg>
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
                                                        <use xlink:href="assets/img/sprite.svg#closeIcn"></use>
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
<!--                                        <div ng-hide="media.progress" class="active media-holder">
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
<!--                                        <div ng-hide="media.progress" class="active media-holder">
                                            <div class="loader loader-attach-file" style="display:block"></div>
                                        </div>-->
                                        <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>
                                        <div ng-if="(media.data.MediaType == 'PHOTO')" ng-show="media.progress" class="media-holder">
                                            <a class="active">
                                                <span>
                                        <img ng-show="media.progress" ng-src="{{media.data.ImageServerPath}}/220x220/{{media.data.ImageName}}" err-src="{{media.data.ImageServerPath}}/{{media.data.ImageName}}" >
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
<!--                                        <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                        <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                        <span  class="file-type {{ file.data.MediaExtension || file.ext }}">
                                  <svg class='svg-icon' width='26px' height='28px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='<?php echo site_url() ?>assets/img/sprite.svg#fileIcon'></use>
                                  </svg>
                                  <span ng-bind=" '.' + (file.data.MediaExtension || file.ext) "></span>
                                        </span>
                                        <span class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                        <i ng-show="file.progress" ng-click="removeWallAttachement('edit_file', fileKey, file.data.MediaGUID)" class='dwonload  icon hover'>
                                  <svg class='svg-icons' width='20px' height='20px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='<?php echo site_url() ?>assets/img/sprite.svg#closeIcon'></use>
                                  </svg>
                              </i>
                                    </li>
                                    <li ng-repeat="(fileKey, file) in files">
<!--                                        <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                                        <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                                        <span  class="file-type {{ file.data.MediaExtension || file.ext}}">
                                  <svg class='svg-icon' width='26px' height='28px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='<?php echo site_url() ?>assets/img/sprite.svg#fileIcon'></use>
                                  </svg>
                                  <span ng-bind=" '.' + (file.data.MediaExtension || file.ext) "></span>
                                        </span>
                                        <span class='file-name' ng-bind="file.data.OriginalName || file.name"></span>
                                        <i ng-show="file.progress" ng-click="removeWallAttachement('file', fileKey, file.data.MediaGUID)" class='dwonload  icon hover'>
                                  <svg class='svg-icons' width='20px' height='20px'>
                                    <use xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='<?php echo site_url() ?>assets/img/sprite.svg#closeIcon'></use>
                                  </svg>
                              </i>
                                    </li>
                                </ul>
                            </div>
                            <div ng-show="( ( mediaCount > 0 ) && !isWallAttachementUploading )" ng-cloak style="display:block;" class="comments same-caption about-media">
                                <textarea ng-model="saySomthingAboutMedia[mediaInputIndex]" id="mc-default" class="form-control mc" placeholder="Say something about {{ ( ( mediaInputIndex == 'ALL' ) && ( mediaCount > 1 ) ) ? 'these media.' : 'this media.' }}"></textarea>
                            </div>
                            <div class="select-group-tags">
                                <div ng-show="IsNewsFeed=='1' && (!edit_post || singleActivity.StatusID=='10')" class="select-tags">
                                    <div class="group-contacts">
                                        <div class="dropable sortable" droppable='items.list1' ng-move='moveObject(from, to, fromList, toList)' ng-create='createObject(object, to, list)'></div>
                                        <div class="input-group groups-tag" id="list1">
                                            <span class="input-group-addon"><i class="icon-n-memeber" title="Select contacts or groups to start a new conversation." data-toggle="tooltip" data-placement="bottom"></i> </span>
                                            <div class="form-control">
                                                <tags-input ng-model="tagsto" add-from-autocomplete-only="true" display-property="name" placeholder="{{(tagsto.length>0) ? '' : 'Select Contacts' ;}}" replace-spaces-with-dashes="false" on-tag-added="tagAddedGU($tag)" on-tag-removed="tagRemovedGU($tag)" limit-tags="1">
                                                    <auto-complete source="loadGroupFriendslist($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="userlistTemplate">
                                                    <a href="javascript:void(0);" class="m-conv-list-ProfilePicture"><img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/'+data.ProfilePicture}}" >
                                                <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="<?php echo site_url() ?>assets/img/profiles/user_default.jpg" >
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
                                                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
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
                            </div>
                        </div>
                        <!-- Wall Actions-->
                        <div class="clearfix">
                            <div class="post-actions-footer clearfix">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="btn-thumb-group">
                                            <div ng-if="!edit_post" class="row">
                                                <div class="col-sm-6">
                                                    <div class="dropup">
                                                        <button class="btn btn-default btn-icn btn-thumb btn-block" type="button" data-toggle="dropdown">
                                                            <span class="caret"></span>
                                                            <span class="btn-text">
                                                            <span ng-if="show_user_pic" ng-cloak class="icn">
                                                                <img err-name="{{postasuser.FirstName+' '+postasuser.LastName}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{postasuser.ProfilePicture}}">
                                                            </span>
                                                            <span class="text" ng-bind="postasuser.FirstName+' '+postasuser.LastName"></span>
                                                            </span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">
                                                            <ul class="dropdown-menu-thumb scrollY scrollH200" aria-labelledby="postDropDown" data-gethtml="dropdown">
                                                                <li ng-repeat="u in users">
                                                                    <a ng-class="(u.UserID==postasuser.UserID) ? 'active' : '' ;" ng-click="setpostasuser(u);refresh_show_user_pic();check_post_permission(3,u.UserGUID)">
                                                                        <span class="icn">
                                                                            <img err-name="{{u.FirstName+' '+u.LastName}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{u.ProfilePicture}}">
                                                                        </span>
                                                                        <span class="text" ng-bind="u.FirstName+' '+u.LastName"></span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <input type="hidden" ng-value="postasuser.UserID" id="postasuserid" />
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="dropup">
                                                        <button ng-cloak ng-if="PostAs.Name" class="btn btn-default btn-icn btn-thumb btn-block" type="button" data-toggle="dropdown">
                                                            <span class="caret"></span>
                                                            <span class="btn-text">
                                                            <span ng-if="show_entity_pic" ng-cloak class="icn">
                                                                <img err-name="{{PostAs.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{PostAs.Image}}">
                                                            </span>
                                                            <span class="text" ng-bind="PostAs.Name"></span>
                                                            </span>
                                                        </button>
                                                        <button ng-cloak ng-if="!PostAs.Name" class="btn btn-default btn-icn btn-thumb btn-block" type="button" data-toggle="dropdown">
                                                            <span class="caret"></span>
                                                            <span class="btn-text">
                                                            <span ng-cloak ng-if="entities.length>0" class="text">Select Entity</span>
                                                            <span ng-cloak ng-if="entities.length==0" class="text">No Records Found</span>
                                                            </span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">
                                                            <ul class="dropdown-menu-thumb scrollY scrollH200" aria-labelledby="postDropDown" data-gethtml="dropdown">
                                                                <li ng-if="entity_lists.GROUP.length>0">
                                                                    <label>Group</label>
                                                                </li>
                                                                <li ng-repeat="e in entity_lists.GROUP">
                                                                    <a ng-class="(PostAs.ModuleID==e.ModuleEntityID && PostAs.ModuleEntityGUID==e.ModuleEntityGUID) ? 'active' : '' ;" ng-click="setpostasgroup(e); refresh_show_entity_pic();check_post_permission(e.ModuleID,e.ModuleEntityGUID);">
                                                                        <span class="icn">
                                                                        <img err-name="{{e.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{e.Image}}">
                                                                    </span>
                                                                        <span class="text" ng-bind="e.Name"></span>
                                                                    </a>
                                                                </li>
                                                                <li ng-if="entity_lists.PAGE.length>0">
                                                                    <label>Page</label>
                                                                </li>
                                                                <li ng-repeat="e in entity_lists.PAGE">
                                                                    <a ng-class="(PostAs.ModuleID==e.ModuleEntityID && PostAs.ModuleEntityGUID==e.ModuleEntityGUID) ? 'active' : '' ;" ng-click="setpostasgroup(e); refresh_show_entity_pic();check_post_permission(e.ModuleID,e.ModuleEntityGUID);">
                                                                        <span class="icn">
                                                                        <img err-name="{{e.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{e.Image}}">
                                                                    </span>
                                                                        <span class="text" ng-bind="e.Name"></span>
                                                                    </a>
                                                                </li>
                                                                <li ng-if="entity_lists.EVENT.length>0">
                                                                    <label>Event</label>
                                                                </li>
                                                                <li ng-repeat="e in entity_lists.EVENT">
                                                                    <a ng-class="(PostAs.ModuleID==e.ModuleEntityID && PostAs.ModuleEntityGUID==e.ModuleEntityGUID) ? 'active' : '' ;" ng-click="setpostasgroup(e); refresh_show_entity_pic();check_post_permission(e.ModuleID,e.ModuleEntityGUID);">
                                                                        <span class="icn">
                                                                        <img err-name="{{e.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{e.Image}}">
                                                                    </span>
                                                                        <span class="text" ng-bind="e.Name"></span>
                                                                    </a>
                                                                </li>
                                                                <li ng-if="entity_lists.FORUMCATEGORY.length>0">
                                                                    <label>Category</label>
                                                                </li>
                                                                <li ng-repeat="e in entity_lists.FORUMCATEGORY">
                                                                    <a ng-class="(PostAs.ModuleID==e.ModuleEntityID && PostAs.ModuleEntityGUID==e.ModuleEntityGUID) ? 'active' : '' ;" ng-click="setpostasgroup(e); refresh_show_entity_pic();check_post_permission(e.ModuleID,e.ModuleEntityGUID);">
                                                                        <span class="icn">
                                                                        <img err-name="{{e.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{e.Image}}">
                                                                    </span>
                                                                        <span class="text" ng-bind="e.Name"></span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <input type="hidden" ng-value="postasgroup.ModuleID" id="PostAsGroupModuleID" />
                                                        <input type="hidden" ng-value="postasgroup.ModuleEntityGUID" id="PostAsGroupModuleEntityID" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-12">
                                        <ul class="button-list-toolbar right">
                                            <li>
                                                <button tooltip data-placement="top" title="Add Tags" ng-click="addTagList = true;" type="button" class="btn btn-sm btn-default btn-icn">
                                                    <span class="icon">
                                                  <svg  class="svg-icons" height="16px" width="16px">
                                                       <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
                                                  </svg>
                                              </span>
                                                </button>
                                            </li>
                                            <li>
                                                <button tooltip data-placement="top" title="Attach Media" type="button" class="btn btn-sm btn-default btn-icn" ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);">
                                                    <span class="icon">
                                              <svg  class="svg-icons" height="20px" width="20px">
                                              <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnAttachment"></use>
                                              </svg>
                                          </span>
                                                </button>
                                            </li>
                                            <li>
                                                <div class="dropup">
                                                    <button tooltip data-placement="top" title="Privacy" type="button" class="btn btn-sm btn-default btn-icn" data-toggle="dropdown" aria-expanded="false">
                                                        <svg class="svg-icons" width="14px" height="14px">
                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>admin/img/sprite.svg#{{getPrivacyIcon()}}"></use>
                                                        </svg>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                        <li>
                                                            <a onClick="$('#visible_for').val(1);" ng-click="setPrivacyIcon('globeIco')">
                                                                <span class="icn">
                                                    <svg class="svg-icons" width="14px" height="14px">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>admin/img/sprite.svg#globeIco"></use>
                                                      </svg>
                                                </span>
                                                                <span class="text">Everyone</span></a>
                                                        </li>
                                                        <!-- <li><a onClick="$('#visible_for').val(2);" ng-click="setPrivacyIcon('friendsIco')"><span class="icn">
                                                    <svg class="svg-icons" width="14px" height="14px">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="< ?php echo ASSET_BASE_URL ?>admin/img/sprite.svg#friendsIco"></use>
                                                      </svg>
                                                </span><span class="text">Friends of Friends</span></a></li> -->
                                                        <li><a onClick="$('#visible_for').val(3);" ng-click="setPrivacyIcon('friendsIco')"><span class="icn">
                                                    <svg class="svg-icons" width="14px" height="14px">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>admin/img/sprite.svg#friendsIco"></use>
                                                      </svg>
                                                </span><span class="text">Friends</span></a></li>
                                                        <li><a onClick="$('#visible_for').val(4);" ng-click="setPrivacyIcon('userIco')"><span class="icn">
                                                    <svg class="svg-icons" width="14px" height="14px">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>admin/img/sprite.svg#userIco"></use>
                                                      </svg>
                                                </span><span class="text">Only Me</span></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="btn-group post-button">
                                                    <button ng-disabled=" ( isWallAttachementUploading || noContentToPost || summernoteBtnDisabler) " class="btn btn-primary" id="ShareButton" ng-click="SubmitWallpost();" type="button">Post </button>
                                                    <button ng-disabled=" ( isWallAttachementUploading || noContentToPost || summernoteBtnDisabler) " class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
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
            <?php $this->load->view('admin/users/preview') ?>
        </div>
    </form>
    <?php $this->load->view('groups/multiple_instant_group_popup'); ?>
    <input type="hidden" id="FeedSortBy" value="2" />
    <input type="hidden" id="IsMediaExists" value="2" />
    <input type="hidden" id="PostOwner" value="" />
    <input type="hidden" id="ActivityFilterType" value="0" />
    <input type="hidden" id="AsOwner" value="0" />
    <input type="hidden" name="EditActivityGUID" id="EditActivityGUID" value="" />
