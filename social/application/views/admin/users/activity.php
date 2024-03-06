<script type="text/javascript">
    var IsAdminActivity = 1;</script>
<div id="activityFeedId-{{ FeedIndex}}" ng-repeat="data in activityData track by $index" repeat-done="wallRepeatDone(); callToolTip();" ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index; initTagsItem($index); " viewport-watch class="news-feed-listing" ng-class="{'overlay-content': data.stickynote}">
    <!-- new Template -->
    <div class="inner-wall-post">
        <div class="feed-header-block">
            <div ng-if="IsSingleActivity && data.PostTitle != ''" class="post-type-title" ng-bind="data.PostTitle"></div>
            <span class="sticky" ng-if="IsNewsFeed == '0' && (data.IsSticky == '1' || data.SelfSticky == '1')">                                        
                <i class="ficon-pin rotate-45"></i>
            </span>
            <div class="feed-header">
                <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{data.UserGUID}}" ng-if="data.PostAsModuleID == '18' && data.ActivityType !== 'ProfilePicUpdated' && data.ActivityType !== 'ProfileCoverUpdated'" ng-href="{{data.SiteURL + 'page/' + data.UserProfileURL}}">
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{data.UserGUID}}" ng-if="data.PostAsModuleID == '3' && data.ActivityType !== 'ProfilePicUpdated' && data.ActivityType !== 'ProfileCoverUpdated'" ng-href="{{data.SiteURL + data.UserProfileURL}}">
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{data.UserGUID}}" ng-if="(data.ActivityType == 'ProfilePicUpdated' || data.ActivityType == 'ProfileCoverUpdated') && data.ModuleID !== '18'" ng-href="{{data.SiteURL + data.UserProfileURL}}">
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{data.EntityGUID}}" ng-if="(data.ActivityType == 'ProfilePicUpdated' || data.ActivityType == 'ProfileCoverUpdated') && data.ModuleID == '18'" ng-href="{{data.SiteURL + 'page/' + data.EntityProfileURL}}">
                    <img ng-if="data.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.EntityProfilePicture}}">
                </a>
                <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{data.UserGUID}}" ng-if="data.PostType == '7' && data.ModuleID == '3'" ng-href="{{data.SiteURL + data.UserProfileURL}}">
                    <img err-Name="{{data.UserName}}"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}">
                </a>
                <a class="thumb-48 loadbusinesscard" entitytype="group" entityguid="{{data.EntityGUID}}" ng-if="data.PostType == '7' && data.ModuleID == '1'" ng-href="{{data.SiteURL + 'group/' + data.EntityProfileURL}}">
                    <img ng-if="data.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.EntityProfilePicture}}">
                </a>
                <div class="user-info">
                    <span ng-bind-html="getTitleMessage(data)"></span>
                    <span tooltip data-placement="top" data-original-title="Expert" ng-if="data.IsExpert == '1'" class="icon group-expert">
                        <svg height="14px" width="14px" class="svg-icons">
                        <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnExpert"></use>
                        </svg>
                    </span>
                    <ul class="sub-navigation">
                        <li ng-click="get_history(data.ActivityGUID)">
                            <!-- <span>Updated</span> -->
                            <span ng-cloak ng-show="data.ActivityType !== 'AlbumUpdated'" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(data.CreatedDate));}}" ng-bind="date_format((data.CreatedDate))"></span>
                            <span ng-cloak ng-show="data.ActivityType == 'AlbumUpdated'" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(data.ModifiedDate));}}" ng-bind="date_format((data.ModifiedDate))"></span>
                        </li>
                        <!-- <li><i class="icon-n-everyone" data-toggle="tooltip" data-placement="top" title="Public" >&nbsp;</i></li> -->
                        <li ng-if="data.IsArchive == 2">This was archived</li>
                        <li ng-if="data.ModuleID == '3'">
                            <a ng-if="data.ModuleID == '3' && data.IsDeleted == 0" class="privacy-dropdown arrow-box" data-toggle="dropdown" data-dropdown="iconmenu">
                                <!-- <i uib-tooltip="Everyone" ng-if="data.Visibility == '1'" class="icon-n-everyone">&nbsp;</i>
                                <i uib-tooltip="Friends of Friend" ng-if="data.Visibility == '2'" class="icon-n-followers">&nbsp;</i> -->
                                <i uib-tooltip="Friends" ng-if="data.Visibility == '3'" class="icon-n-friends">&nbsp;</i>
                                <i uib-tooltip="Only Me" ng-if="data.Visibility == '4'" class="icon-n-onlyme">&nbsp;</i>
                            </a>
                            <ul class="dropdown-menu" data-dropdown="privacydropdown">
                                <li>
                                    <a ng-class="data.Visibility == '1' ? 'active' : ''" ng-click="privacyEmit(data.ActivityGUID, '1');">
                                        <i class="icon-n-everyone"></i>Everyone</a>
                                </li>
                                <!-- <li>
                                    <a ng-class="data.Visibility=='2' ? 'active' : ''" ng-click="privacyEmit(data.ActivityGUID, '2');">
                                        <i class="icon-n-followers"></i>Friends of Friend</a>
                                </li> -->
                                <li>
                                    <a ng-class="data.Visibility == '3' ? 'active' : ''" ng-click="privacyEmit(data.ActivityGUID, '3');">
                                        <i class="icon-n-friends"></i>Friends</a>
                                </li>
                                <li>
                                    <a ng-class="data.Visibility == '4' ? 'active' : ''" ng-click="privacyEmit(data.ActivityGUID, '4');">
                                        <i class="icon-n-onlyme"></i>Only Me</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="btn-toolbar btn-toolbar-right dropdown">
                    <a uib-tooltip="Draft" ng-cloak ng-if="data.StatusID == '10'" class="btn btn-xs btn-default btn-icn" data-toggle="dropdown" role="button">
                        <span class="icn">
                            <i class="ficon-draft"></i>
                        </span>
                    </a>
                    <a class="btn btn-xs btn-default btn-icn" data-toggle="dropdown" role="button"><span class="icn"><i class="ficon-dots"></i></span></a>
                    <input type="hidden" ng-value="baseURL+data.ActivityURL" id="a-{{data.ActivityID}}" />
                    <ul class="dropdown-menu dropdown-menu-right" ng-if="data.IsDeleted == 0">
                        <li><a ng-click="copyToClipboard(data.ActivityID)">Copy URL</a></li>
                        <li><a ng-click="editPost(data.ActivityGUID, $event)">Edit</a></li>
                        <li><a ng-if="data.CommentsAllowed == '1'" ng-cloak ng-click="commentsSwitchEmit('ACTIVITY', data.ActivityGUID)">Turn Comments Off</a></li>
                        <li><a ng-if="data.CommentsAllowed == '0'" ng-cloak ng-click="commentsSwitchEmit('ACTIVITY', data.ActivityGUID)">Turn Comments On</a></li>
                        <li><a ng-click="deleteEmit(data.ActivityGUID);">Remove Post</a></li>
                    </ul>
                    <ul class="dropdown-menu dropdown-menu-right" ng-if="data.IsDeleted == 1">
                        <li><a ng-click="restoreEmit(data.ActivityGUID);">Restore</a></li>
                        <li><a ng-click="deleteEmit(data.ActivityGUID);">Delete Permanently</a></li>
                    </ul>
                </div>
            </div>
            <a ng-if="data.IsOwner == 1 && data.IsEdited == '1' && IsSingleActivity" class="text-sm-off block text-right" ng-click="get_history(data.ActivityGUID)">Version history</a>
        </div>
        <div class="collapse-content feed-act-{{data.ActivityGUID}}">
            <div bindonce id="act-{{data.ActivityGUID}}" class="activitywrapper" ng-class="{'inview' : data.Viewed == '0'}">
                <!-- <a href="javascript:void(0);" data-ng-if="data.IsSticky == '1'" class="sticky-post"><i class="icon-sticky"></i></a> -->
                <div class="feed-body" ng-class="(data.PollData.length > 0) ? 'poll-feed-listing' : '';">
                    <div class="history-view" ng-cloak ng-if="ActivityHistory.length > 0">
                        <ul class="history-listing">
                            <li ng-repeat="history in ActivityHistory">
                                <div class="history-heading">
                                    <span class="history-title collapsed" data-toggle="collapse" data-target="#historyLisint_{{history.HistoryID}}">{{date_format((history.ModifiedDate))}}, Updated by {{history.UpdatedBy}}</span>
                                    <div class="pull-right">
                                        <button class="btn btn-default btn-xs m-r-5" ng-click="revert_history(data.ActivityGUID, history.HistoryID)">Revert</button>
                                        <a class="arrow-acc icon collapsed" data-toggle="collapse" data-target="#historyLisint_{{history.HistoryID}}">
                                            <svg height="12px" width="12px" class="svg-icons">
                                            <use xlink:href="assets/img/sprite.svg#icnArrowDown"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="history-content collapse" id="historyLisint_{{history.HistoryID}}">
                                    <div ng-bind-html="history.PostTitle"> </div>
                                    <div class="commented-content" ng-bind-html="textToLink(history.PostContent)"> </div>
                                    <div ng-if="(history.Files && (history.Files !== '') && (history.Files.length > 0))" class="feed-content">
                                        <ul class="attached-files">
                                            <li ng-repeat="file in history.Files" ng-click="hitToDownload(file.MediaGUID);">
                                                <span class="file-type" ng-class="file.MediaExtension">
                                                    <svg class="svg-icon" width="26px" height="28px">
                                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#fileIcon"></use>
                                                    </svg>
                                                    <span ng-bind="'.' + file.MediaExtension"></span>
                                                </span>
                                                <span class="file-name" ng-cloak searchfieldid="advancedSearchKeyword" make-content-highlighted="file.OriginalName" ng-bind-html="file.OriginalName"></span>
                                                <!--<a href="{{NewsFeedList.baseUrl}}home/download/{{wallMedia.MediaGUID}}">-->
                                                <!--<a class="dwonload hover" ng-href="{{baseURL}}home/download/{{file.MediaGUID}}/wall">-->
                                                <a class="dwonload icon hover">
                                                    <svg class="svg-icons" width="20px" height="20px">
                                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#dwonloadIcon"></use>
                                                    </svg>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div ng-init="(layoutClass(history.Album[0].Media) == 'single-image') ? '' : blocksIt(history.ActivityGUID);" ng-if="(history.Album[0].Media && (history.Album[0].Media !== '') && (history.Album[0].Media.length > 0))" ng-class="layoutClass(history.Album[0].Media)" class="feed-content mediaPost">
                                        <figure ng-repeat="media in history.Album[0].Media|limitTo:4" class="media-thumbwrap">
                                            <a ng-if="media.ConversionStatus !== 'Pending'" class="mediaThumb" image-class="{{layoutClass(history.Album[0].Media)}}">
                                                <!-- Media Thumbs -->
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                                <img ng-if="history.ActivityType == 'ProfilePicUpdated'" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType == 'ProfileCoverUpdated'" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType == 'ProfilePicUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType == 'ProfileCoverUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                                <i class="icon-n-video-big" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"></i>
                                                <!-- Media Thumbs -->
                                                <div ng-if="$last && history.Album[0].TotalMedia > 4 && history.Album[0].Media.length > 1" class="more-content"><span ng-bind="'+' + (history.Album[0].TotalMedia - 4)"></span></div>
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
                                </div>
                                <div ng-repeat="link in history.Links" ng-if="history.Links && (history.showAllLinks == 1 || $index < 3)" class="network-block clearfix m-t-15" ng-include src="partialURL+'activity/Network.html'"></div>
                                <div ng-if="history.Links.length > 3 && history.ShowMoreHide !== '1'" ng-click="seeMoreLink(history.ActivityGUID);" class="text-center">
                                    <a href="javascript:void(0)"  class="btn-link">See More</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <!-- Post.HTML START -->
                    <div class="feed-content">
                        <div ng-if="!IsSingleActivity && data.PostTitle != ''" class="post-type-title">
                            <a ng-href="{{data.ActivityURL}}" target="_blank" ng-bind="data.PostTitle" class="a-link"></a>
                        </div>
                        <div class="post-content" ng-mouseup="get_selected_text($event, data.ActivityGUID);" ng-if="data.PostContent" ng-bind-html="textToLink(data.PostContent, false, 200)"></div>
                        <div ng-if="(data.Files && (data.Files !== '') && (data.Files.length > 0))" class="feed-content">
                            <ul class="attached-files">
                                <li ng-repeat="file in data.Files" ng-click="hitToDownload(file.MediaGUID);">
                                    <span class="file-type" ng-class="file.MediaExtension">
                                        <svg class="svg-icon" width="26px" height="28px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#fileIcon"></use>
                                        </svg>
                                        <span ng-bind="'.' + file.MediaExtension"></span>
                                    </span>
                                    <span class="file-name" ng-cloak searchfieldid="advancedSearchKeyword" make-content-highlighted="file.OriginalName" ng-bind-html="file.OriginalName"></span>
                                    <!--<a href="{{NewsFeedList.baseUrl}}home/download/{{wallMedia.MediaGUID}}">-->
                                    <!--<a class="dwonload hover" ng-href="{{baseURL}}home/download/{{file.MediaGUID}}/wall">-->
                                    <a class="dwonload icon hover">
                                        <svg class="svg-icons" width="20px" height="20px">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#dwonloadIcon"></use>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div ng-init="(layoutClass(data.Album[0].Media) == 'single-image') ? '' : blocksIt(data.ActivityGUID);" ng-if="(data.Album[0].Media && (data.Album[0].Media !== '') && (data.Album[0].Media.length > 0))" ng-class="layoutClass(data.Album[0].Media)" class="feed-content mediaPost">
                        <figure ng-repeat="media in data.Album[0].Media|limitTo:4"  class="media-thumbwrap">
                            <a ng-if="media.ConversionStatus !== 'Pending'" class="mediaThumb" image-class="{{layoutClass(data.Album[0].Media)}}">
                                <!-- Media Thumbs -->
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                <img ng-if="data.ActivityType == 'ProfilePicUpdated'" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType == 'ProfileCoverUpdated'" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType == 'ProfilePicUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType == 'ProfileCoverUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                <i class="icon-n-video-big" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"></i>
                                <!-- Media Thumbs -->
                                <div ng-if="$last && data.Album[0].TotalMedia > 4 && data.Album[0].Media.length > 1" class="more-content"><span ng-bind="'+' + (data.Album[0].TotalMedia - 4)"></span></div>
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
                    <div ng-repeat="link in data.Links" ng-if="data.Links && (data.showAllLinks == 1 || $index < 3)" class="network-block clearfix m-t-15" ng-include src="partialURL+'activity/Network.html'"></div>
                    <div ng-if="data.Links.length > 3 && data.ShowMoreHide !== '1'" ng-click="seeMoreLink(data.ActivityGUID);" class="text-center">
                        <a href="javascript:void(0)"  class="btn-link">See More</a>
                    </div>
                    <!-- Post.HTML ENDS -->
                    <div class="tag-post-edit" ng-if="data.showTags" ng-cloak>
                        <span class="icon">
                            <svg class="svg-icons no-hover" height="16px" width="16px">
                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
                            </svg>
                        </span>
                        <div class="groups-tag">
                            <tags-input ng-model="data.editTags" display-property="Name" placeholder="Add tags" on-tag-added="addPostTag($tag,data.ActivityID)" on-tag-removed="removePostTag($tag,data.ActivityID)" min-length="1" replace-spaces-with-dashes="false" template="editTagsTemplate">
                                <auto-complete source="getActivityTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="editTagDropdownTemplate"></auto-complete>
                            </tags-input>
                            <script type="text/ng-template" id="editTagsTemplate">
                                <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip ng-cloak>
                                <span class="ng-binding ng-scope" searchfieldid="advancedSearchKeyword" make-content-highlighted="data.Name" ng-bind-html="data.Name"></span>
                                <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                </div>
                            </script>
                            <script type="text/ng-template" id="editTagDropdownTemplate">
                                <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                            </script>
                        </div>
                        <ul class="edit-save">
                            <li ng-click="updatePostTags(data.ActivityID);">
                                <span class="icon" uib-tooltip="Save">
                                    <svg class="svg-icons" height="18px" width="18px">
                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnCheck"></use>
                                    </svg>
                                </span>
                            </li>
                            <li ng-click="initTagsItem(FeedIndex);">
                                <span class="icon" uib-tooltip="Close">
                                    <svg class="svg-icons" height="22px" width="22px">
                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIcon"></use>
                                    </svg>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="feed-post-activity">
                        <ul ng-if="LoginSessionKey !== ''" class="feed-like-nav">
                            <li class="btn-group rq-button" ng-if="data.PostType == 2">
                                <button uib-tooltip="Request Answer" ng-hide="data.Visibility == '4'" class="btn btn-default btn-xs" ng-disabled="requestAns" ng-click="get_activity_friend_list('init', data.ActivityGUID)">Request</button>
                            </li>
                            <li ng-if="data.LikeAllowed == '1'" ng-class="(data.IsLike == '1') ? 'active' : '';" class="iconlike" uib-tooltip="{{(data.IsLike == '1') ? 'Unlike' : 'Like' ;}}">
                                <span ng-disabled="data.IsDeleted == 1" ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);">
                                    <i class="ficon-heart"></i>
                                </span>
                            </li>
                            <li ng-if="data.NoOfLikes > 0" uib-tooltip-html="{{getLikeTooltip(data.LikeList)}} | trusted" ng-bind="data.NoOfLikes" class="view-count">
                            </li>
                            <li ng-cloak ng-if="data.ShareAllowed == '1'">
                                <span uib-tooltip="Share" ng-disabled="data.IsDeleted == 1" type="button" ng-click="shareEmit(data.ActivityGUID);" data-target="#sharemodal" data-toggle="modal"> 
                                    <svg height="16px" width="16px" class="svg-icons">
                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnShare"></use>
                                    </svg>
                                </span>
                            </li>
                            <li ng-click="toggleTagsItem(FeedIndex, data.ActivityGUID);" uib-tooltip="Add Tags">
                                <svg height="16px" width="16px" class="svg-icons">
                                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
                                </svg>
                            </li>
                            <li class="view-count" ng-if="data.EntityTags && data.EntityTags.length > 0" ng-click="toggleTagsItem(FeedIndex, data.ActivityGUID);">
                                <span ng-bind="data.EntityTags.length"></span>
                            </li>
                        </ul>
                        <ul ng-show="LoginSessionKey == ''" class="feed-like-nav">
                            <li class="btn-group rq-button" ng-if="data.PostType == 2">
                                <button uib-tooltip="Request Answer" ng-hide="data.Visibility == '4'" class="btn btn-default btn-xs" ng-disabled="requestAns" ng-click="get_activity_friend_list('init', data.ActivityGUID)">Request</button>
                            </li>
                            <li ng-if="data.PostType !== '2'" ng-class="(data.IsLike == '1') ? 'active' : '';" class="iconlike" uib-tooltip="{{(data.IsLike == '1') ? 'Unlike' : 'Like' ;}}">
                                <span ng-disabled="data.IsDeleted == 1" ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);">
                                    <i class="ficon-heart"></i>
                                </span>
                            </li>
                            <li ng-if="data.PostType !== '2' && data.NoOfLikes > 0" ng-init="callToolTip();" uib-tooltip-html uib-tooltip="{{getLikeTooltip(data.LikeList)}}" ng-bind="data.NoOfLikes" class="view-count">
                            </li>
                            <li ng-if="data.PostType == '2'" class="btn-group rq-button">
                                <button uib-tooltip="Upvote" data-ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);" class="btn btn-default btn-xs">Upvote</button>
                                <button class="btn btn-default btn-xs" ng-bind="data.NoOfLikes"></button>
                            </li>
                            <li ng-if="data.PostType == '2'" class="btn-group rq-button">
                                <a uib-tooltip="Downvote" data-ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID, 1);" class="dw-button">Downvote</a>
                            </li>
                            <li ng-cloak uib-tooltip="Share">
                                <span ng-disabled="data.IsDeleted == 1" type="button" ng-click="shareEmit(data.ActivityGUID);" data-target="#sharemodal" data-toggle="modal"> 
                                    <svg height="16px" width="16px" class="svg-icons">
                                    <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnShare"></use>
                                    </svg>
                                </span>
                            </li>
                            <li ng-if="data.EntityTags && data.EntityTags.length > 0" ng-click="toggleTagsItem(FeedIndex, data.ActivityGUID);" uib-tooltip="Add Tags">
                                <svg height="16px" width="16px" class="svg-icons">
                                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
                                </svg>
                            </li>
                            <li class="view-count" ng-if="data.EntityTags && data.EntityTags.length > 0" ng-click="toggleTagsItem(FeedIndex, data.ActivityGUID);">
                                <span ng-bind="data.EntityTags.length"></span>
                            </li>
                        </ul>
                        <div class="section-groups">
                            <span class="feed-type icon" uib-tooltip="{{getPostTooltip(data.PostType)}}">
                                <svg height="18px" width="18px" class="svg-icons">
                                <use xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#{{getPostIcon(data.PostType)}}"></use>
                                </svg>
                            </span> 
                            <div class="pull-right  pull-right-page">
                                <div class="post-as-page">
                                    <div class="dropdown m-t-m2 m-r-10">
                                        <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                            <span ng-if="show_user_pic" ng-cloak class="user-img-icon post-as-data" data-module-id="3" data-module-entityid="{{data.actionas.UserGUID}}">
                                                <img err-name="{{data.actionas.Name}}" class="img-circle show-pic" alt="User" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/36x36/' + data.actionas.ProfilePicture}}">
                                                <span class="post-as-view">
                                                    <span ng-if="data.actionas.Name" ng-bind="data.actionas.Name"></span>
                                                    <span ng-if="!data.actionas.Name" ng-bind="data.actionas.FirstName + ' ' + data.actionas.LastName"></span>
                                                    <i class="icon-caret"></i>
                                                </span>
                                            </span>
                                        </button>
                                        <div class="postasDropdown mCustomScrollbar dropdown-menu dropdown-menu-sm dropdown-menu-right" role="menu">
                                            <ul class="dropdown-menu-thumb" role="menu">
                                                <li ng-repeat="entitylist in  users">
                                                    <a ng-class="(data.actionas.UserGUID == entitylist.UserGUID) ? 'active' : '';" ng-click="changeActionAs(entitylist, data.ActivityGUID); refresh_show_user_pic();">
                                                        <span class="icn">
                                                            <img err-name="{{entitylist.Name}}" class="img-circle" title="User" alt="User" ng-src="{{data.ImageServerPath + 'upload/profile/36x36/' + entitylist.ProfilePicture}}">
                                                        </span> 
                                                        <span class="text">{{entitylist.Name}}</span> 
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-add-note" ng-if="data.RquestedFriendList.length > 0 || data.SuggestedFriendList.length > 0 || data.SearchFriendList != ''" ng-cloak>
                    <div class="requested-list">
                        <div class="tag-view"  ng-cloak>
                            <ul class="tag-list">
                                <li ng-repeat="RequestFriend in data.RquestedFriendList">
                                    <span>
                                        {{RequestFriend.FirstName + ' ' + RequestFriend.LastName}}
                                    </span>

                                    <i class="ficon-cross ng-scope" ng-click="remove_select_data(RequestFriend, $index, data.ActivityGUID)"></i>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="rq-suggested-list">
                        <h5>Have this question too? Request Answers</h5>
                        <div class="rquested-list-view" custom-scroll>
                            <ul class="list-group thumb-30">
                                <li ng-repeat="ActivityFriend in data.SuggestedFriendList| limitTo:3" ng-click="add_request_friend(ActivityFriend, $index, data.ActivityGUID)">
                                    <figure>
                                        <a><img err-name="{{ActivityFriend.FirstName + ' ' + ActivityFriend.LastName}}" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + ActivityFriend.ProfilePicture}}" class="img-circle"  ></a>
                                    </figure>
                                    <div class="description">
                                        <a class="name" ng-bind="ActivityFriend.FirstName + ' ' + ActivityFriend.LastName"></a>
                                        <span class="location" ng-bind="ActivityFriend.AnswerCount + ' Answers'"></span>
                                    </div>
                                    <ul class="edit-save" ng-cloak>
                                        <li>
                                            <span class="icon">
                                                <svg  class="svg-icons" height="18px" width="18px">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnPlus"></use>
                                                </svg>
                                            </span>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="req-autosugget" ng-if="data.SuggestedTotalRecords > 3 || data.SearchFriendList !== ''">
                        <input type="text" id="sr_{{data.ActivityGUID}}" ng-model="data.SearchFriendList" class="form-control" placeholder="Search" ng-keydown="SearchActivityFriend(data.ActivityGUID)">
                    </div>
                    <div class="req-footer" ng-if="data.RquestedFriendList.length > 0">
                        <div class="ask-ans">
                            <textarea name="" 
                                      id="note_{{data.ActivityGUID}}" 
                                      class="form-control" 
                                      ng-model="ActivityDetail.Note" 
                                      placeholder="Please help me find an answer to this question?">

                            </textarea>
                        </div>
                        <div class="button-fotter-group">
                            <div class="pull-right">
                                <button class="btn btn-default" ng-click="hideRequest()">Cancel</button>
                                <button class="btn btn-primary m-l-5" ng-click="send_request(data.ActivityGUID)">Send Request</button>
                            </div>
                        </div>
                    </div>
                </div>              
              
                <div class="panel-footer panel-footer-comments" ng-show="data.Comments.length > 0 && data.CommentsAllowed == 1">
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
                                <div class="comment-listing-content">
                                    <div class="list-group-body">
                                        <div class="btn-toolbar btn-toolbar-right dropdown">
                                            <a class="rotate-90 block" data-toggle="dropdown" role="button"><span class="icn"><i class="ficon-dots f-16"></i></span></a>                                            
                                            <ul class="dropdown-menu feedaction dropdown-menu-right">
                                                <li ng-click="commentEditBlock(comnt.CommentGUID, data.ActivityGUID, comnt)"><a>Edit</a></li>
                                                <li ng-click="deleteCommentEmit(comnt.CommentGUID, data.ActivityGUID);"><a>Delete</a></li>
                                                <li ng-click="insert_to_editor(data.ActivityGUID, comnt.PostComment, FeedIndex);"><a>Quote</a></li>
                                            </ul>                                            
                                        </div>
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
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#fileIcon"></use>
                                                    </svg> 
                                                    <span ng-bind=" '.' + file.MediaExtension"></span>
                                                </span>
                                                <span class="file-name" ng-bind="file.OriginalName"></span>
                                                <i class="dwonload icon hover">
                                                    <svg class="svg-icons" width="20px" height="20px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#dwonloadIcon"></use>
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
                                                <li tooltip data-placement="top" data-original-title="Reply" ng-click="replyToComment(comnt.CommentGUID, data.ActivityGUID, 5, comnt, 0)">
                                                    <svg height="18px" width="18px" class="svg-icons">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnReply"></use>
                                                    </svg>
                                                </li>
                                            </ul>
                                        </div>
                                        <!-- Edit Comment Start -->
                                        <div class="comment-edit-block hide " id="comment-edit-block-{{comnt.CommentGUID}} " ng-cloak=" ">
                                            <div class="post-comments ">
                                                <div class="comment-section ">
                                                    <div class="current-selected ">
                                                        <span class="icon ">
                                                            <button class="btn btn-default btn-sm ">Quote Post</button>
                                                      <!--  <svg height="16px " width="16px " class="svg-icons ">
                                                         <use xmlns:xlink="http://www.w3.org/1999/xlink " xlink:href="assets/img/sprite.svg#icnDiscussions " ng-href="assets/img/sprite.svg#icnQuote "></use>
                                                       </svg> -->

                                                        </span>
                                                    </div>
                                                    <summernote id="cmt-{{comnt.CommentGUID}} " config="commentOptions " placeholder="Write a comment " on-focus="focus(evt) " editable="editable " editor="editor " on-blur="CheckBlur(data.ActivityGUID) " on-keyup="checkEditorData(evt, FeedIndex);">
                                                    </summernote>
                                                    <!-- Post Action -->
                                                    <div class="post-actions clearfix border-top ">
                                                        <div class="media-upload-view " id="attachments-cmt-{{ comnt.CommentGUID}} " ng-cloak ng-show="(comnt.Media.length > 0) || (comnt.Files.length > 0)">
                                                            <ul class="attached-list" id="listingmedia">
                                                                <li class="photo-itm media-item" ng-repeat=" ( mediaIndex, media ) in comnt.Media">
                                                                    <div ng-hide="media.progress" class="loader" style="display: block;"></div>
                                                                    <span ng-if="(media.MediaType == 'Video')" ng-show="media.progress" class="videoprocess" style="background: #ddd;"></span>
                                                                    <img ng-if="(media.MediaType == 'Image' || media.MediaType == 'PHOTO')" ng-show="media.progress" ng-src="{{ImageServerPath}}upload/comments/220x220/{{media.ImageName}}" >
                                                                    <i ng-show="media.progress" class="icon-n-close-w" ng-click="removeEditAttachement('media', comnt.Media, $index);"></i>
                                                                </li>
                                                            </ul>
                                                            <ul class="attached-files ">
                                                                <li ng-repeat="( fileIndex, file ) in comnt.Files">
                                                                    <div ng-hide="file.progress" class="loader" style="display: block;"></div>
                                                                    <span ng-show="file.progress" class="file-type {{file.MediaExtension}}">
                                                                        <svg class="svg-icon" width="26px" height="28px">
                                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#fileIcon"></use>
                                                                        </svg> 
                                                                        <span ng-bind="'.' + file.MediaExtension"></span>
                                                                    </span>
                                                                    <span ng-show="file.progress" class="file-name" ng-bind="file.OriginalName"></span>
                                                                    <i ng-show="file.progress" class="dwonload icon hover" ng-click="removeEditAttachement('file', comnt.Files, FeedIndex);">
                                                                        <svg class="svg-icons" width="20px" height="20px">
                                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#closeIcon"></use>
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
                                                                            <li class="icon-btn" ng-if="comnt.Media.length == 0">
                                                                                <button ngf-select="EdituploadFiles($files, $invalidFiles,comnt, 1)" accept=".png, .jpg, .jpeg" ngf-validate-async-fn="validateFileSize($file);" type="button" class="btn btn-default" onclick="$('#fileAttach').trigger('click');">
                                                                                    <span class="icon">
                                                                                        <svg class="svg-icons" height="20px" width="20px">
                                                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnAttachment"></use>
                                                                                        </svg>
                                                                                    </span>
                                                                                </button>
                                                                            </li>
                                                                            <li>
                                                                                <button id="PostBtn-{{comnt.CommentGUID}}" data-ng-click="EditcommentEmit($event, data.ActivityGUID, comnt)" class="btn btn-primary p-h" type="button">Post </button>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--// Post Action -->
                                        </div>
                                        <!-- Edit Comment Ends -->
                                    </div>
                                    <!-- Reply Start -->
                                    <div class="p-t-sm semi-bold clearfix" ng-if="comnt.NoOfReplies > 0">
                                        <a class="pull-left" ng-if="comnt.Replies.length < comnt.NoOfReplies" ng-click="getCommentReplies(comnt.CommentGUID, data.ActivityGUID, comnt.NoOfReplies)" ng-cloak><span ng-if="comnt.NoOfReplies == 1" ng-bind="comnt.NoOfReplies + ' Reply'"></span><span ng-if="comnt.NoOfReplies > 1" ng-bind="comnt.NoOfReplies + ' Replies'"></span> <span class="caret"></span></a>
                                        <a class="pull-left" ng-if="comnt.Replies.length == comnt.NoOfReplies" ng-click="hideCommentReplies(comnt.CommentGUID, data.ActivityGUID)" ng-cloak>Hide <span class="caret"></span></a>
                                    </div>
                                    <ul class="comment-listing">
                                        <li ng-repeat="reply in comnt.Replies">
                                            <div class="comment-listing-content">
                                                <div class="comment-header">
                                                    <div class="user-info">
                                                        <a ng-cloak ng-if="reply.ModuleID == '18'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="ng-thumb-30 loadbusinesscard" entitytype="page" entityguid="{{reply.UserGUID}}"><img ng-if="reply.ProfilePicture !== ''"   ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + reply.ProfilePicture}}"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '3'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="ng-thumb-30 loadbusinesscard" entitytype="user" entityguid="{{reply.UserGUID}}"><img err-Name="{{reply.Name}}"   ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + reply.ProfilePicture}}"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '18'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="loadbusinesscard" entitytype="page" entityguid="{{reply.UserGUID}}" ng-bind="reply.Name"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '3'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="loadbusinesscard" entitytype="user" entityguid="{{reply.UserGUID}}" ng-bind="reply.Name"></a>
                                                        <span tooltip data-placement="top" data-original-title="Expert" ng-if="reply.IsExpert == '1'" class="icon group-expert">
                                                            <svg height="14px" width="14px" class="svg-icons">
                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnExpert"></use>
                                                            </svg>
                                                        </span>
                                                        <div class="location" ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(reply.CreatedDate));}}" ng-bind="date_format((reply.CreatedDate));"></div>
                                                    </div>
                                                </div>
                                                <div class="commented-content" ng-bind-html="textToLinkComment(reply.PostComment)">
                                                </div>
                                                <div class="feed-post-activity">
                                                    <ul class="feed-like-nav">
                                                        <li class="iconlike" tooltip data-placement="top" ng-attr-data-original-title="{{(reply.IsLike == '1') ? 'Unlike' : 'Like' ;}}" ng-class="(reply.IsLike == 1) ? 'active' : '';" data-ng-click="likeEmit(reply.CommentGUID, 'COMMENT', data.ActivityGUID, 0, comnt.CommentGUID);">
                                                            <i class="ficon-heart"></i>
                                                        </li>
                                                        <li class="view-count" ng-click="likeDetailsEmit(reply.CommentGUID, 'COMMENT');" ng-bind="reply.NoOfLikes"></li>
                                                    </ul>
                                                    <ul class="feed-remove-nav">
                                                        <li ng-if="reply.CanDelete == '1'" ng-click="deleteCommentEmit(reply.CommentGUID, data.ActivityGUID, comnt.CommentGUID);" data-toggle="tooltip" data-placement="top"  data-original-title="Remove"><i class="icon-n-close"></i> </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="write-on-comment reply-comment" style="display:none;" id="r-{{comnt.CommentGUID}}">
                                        <textarea id="rply-{{comnt.CommentGUID}}" onblur="if (this.value == '') { data - ids =" r-{{comnt.CommentGUID}} " data-ng-keypress="replyEmit($event, comnt.CommentGUID, data.ActivityGUID) " class="form-control " placeholder="{{(data.PostType=='2' ) ? 'Reply to answer...' : 'Reply to comment...' ;}} "></textarea>
                                    </div>
                                </div>
                                <!-- Reply Ends -->
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="feed-footer" id="cmt-div-{{data.ActivityGUID}}" ng-if="data.IsDeleted == 0 && data.CommentsAllowed == 1 && data.StatusID !== '10'">
                    <span class="place-holder-label" ng-click="postCommentEditor(data.ActivityGUID, FeedIndex)" ng-bind="(data.PostType == '2') ? 'Write an answer...' : 'Write a comment...'"></span>
                    <div class="comment-section"  ng-cloak ng-if="show_comment_box == data.ActivityGUID">
                        <div class="loader commentEditorLoader" style="top:45%;">&nbsp;</div>
                        <div class="current-selected">
                            <span class="icon" ng-if="data.PostContent !== ''" ng-click="insert_to_editor(data.ActivityGUID, data.PostContent, FeedIndex)">
                                <button class="btn btn-default btn-sm">Quote Post</button>
                            <!-- <svg height="16px" width="16px" class="svg-icons">
                              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnQuote" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnQuote"></use>
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
                                <ul class="attached-files">
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
                                                    <button id="PostBtn-{{data.ActivityGUID}}" data-ng-click="commentEmit($event, data.ActivityGUID, FeedIndex, '.feed-act-' + data.ActivityGUID + ' ', data)" class="btn btn-primary p-h loader-btn" type="button">Post </button>
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
             
            </div>
        </div>
        <span class="icon-feed-expand" collapse-feed>
            <span class="arrow-acc icons">
                <svg height="14px" width="14px" class="svg-icons">
                <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnArrowDown"></use>
                </svg>
            </span>
        </span>
    </div>
</div>
<!-- Share Popup Code Starts -->
<div class="modal fade" id="sharemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel">SHARE THIS POST</h4>
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
                    <!-- Social Share -->
                    <div ng-if="singleActivity.ShareDetails && singleActivity.PollData.length == '0'" class="col-sm-6 col-md-6 col-xs-12 social">
                        <!-- FacebookShare(data.ShareDetails.Link,data.ShareDetails.Summary,'V Social 6',data.ShareDetails.Image); -->
                        <span data-dismiss="modal" ng-click="$emit('FacebookShareEmit', singleActivity.ShareDetails.Link, singleActivity.ShareDetails.Summary, singleActivity.ShareDetails.Summary, singleActivity.ShareDetails.Image);">
                            <span style="text-decoration:none;color:#000000;display:inline-block;cursor:pointer;" class="stButton"><span class="stLarge" style="background-image: url('<?php echo ASSET_BASE_URL ?>img/facebook_32.png');"></span></span>
                        </span>
                        <script type="text/javascript">
                            if (LoginSessionKey !== '') {
                            window.fbAsyncInit = function() {
                            FB.init({
                            appId: FacebookAppId,
                                    xfbml: true,
                                    version: 'v2.5'
                            });
                            };
                            (function(d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) {
                            return;
                            }
                            js = d.createElement(s);
                            js.id = id;
                            js.src = "//connect.facebook.net/en_US/sdk.js";
                            fjs.parentNode.insertBefore(js, fjs);
                            }(document, 'script', 'facebook-jssdk'));
                            }
                        </script>
                        <!--  st_title="{{strip(singleActivity.ShareDetails.Summary)}}" -->
                        <!--  <span data-dismiss="modal" class='st_twitter_large' st_image="{{singleActivity.ShareDetails.Image}}" st_title="{{strip(singleActivity.ShareDetails.Summary)}}" st_summary="{{singleActivity.ShareDetails.Summary}}" st_via="vinfotech" st_url="{{singleActivity.ShareDetails.Link}}" displayText='Tweet'></span> -->
                        <span style="margin-right: 8px;">
                            <a href="https://twitter.com/intent/tweet?text={{strip(singleActivity.ShareDetails.Summary)}}&url={{singleActivity.ShareDetails.Link}}&via=vinfotech"
                               onclick="popupCenter(this.href, 'Twitter', 500, 300);
                                   return false;">
                                <span style="text-decoration:none;color:#000000;display:inline-block;cursor:pointer;" class="stButton"><span class="stLarge" style="background-image: url(http://w.sharethis.com/images/twitter_32.png);"></span></span>
                            </a>
                        </span>
                    </div>
                    <!-- Social Share Ends -->
                </div>
                <div class="own-wall share-wall" ng-class="(singleActivity.PollData.length > 0) ? 'poll-feed-listing' : '';">
                    <div class="share-content-bottom">
                        <div class="hide comments about-media about-name">
                            <input type="text" class="form-control" id="friend-src" placeholder="Friend's name" value="" />
                        </div>
                        <div id="FriendSearchResult"></div>
                        <div class="comments about-media">
                            <textarea class="form-control" id="PCnt" placeholder="Say something about this post"></textarea>
                        </div>
                        <!-- Poll Share Start -->
                        <div ng-if="singleActivity.PollData.length > 0" class="share-image share-poll-feed">
                            <div class="feed-content" ng-bind-html="textToLink(singleActivity.PostContent)"></div>
                            <div class="poll-feed-description pollQuestion">
                                <ul class="poll-que-list">
                                    <li ng-repeat="pdata in singleActivity.PollData[0].Options" class="">
                                        <div class="upload-view ">
                                            <div class="upload-viewlist">
                                                <span ng-repeat="media in pdata.Media" data-src="{{singleActivity.ImageServerPath + 'upload/poll/' + media.ImageName}}">
                                                    <img ng-src="{{singleActivity.ImageServerPath + 'upload/poll/' + media.ImageName}}" >
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="radio">
                                                <label ng-bind="pdata.Value"></label>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Poll Share Ends -->
                        <div ng-if="singleActivity.PollData.length == 0" class="media-block mediaPost media-photo" ng-class="layoutClass(singleActivity.mediaData)" ng-if="singleActivity.mediaData != undefined && singleActivity.mediaData !== ''">
                            <figure class="media-thumbwrap" ng-repeat="media in singleActivity.mediaData" >
                                <a href="javascript:void(0);" ng-class="singleActivity.mediaData.length > 1 ? 'imgFill' : 'singleImg';" class="media-thumb media-thumb-fill">

                                    <!-- Media Starts -->
                                    <img ng-if="singleActivity.ActivityType != 'ProfilePicUpdated' && singleActivity.ActivityType != 'ProfileCoverUpdated' && singleActivity.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{singleActivity.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType != 'ProfilePicUpdated' && singleActivity.ActivityType != 'ProfileCoverUpdated' && singleActivity.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'"   ng-src="{{singleActivity.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType != 'ProfilePicUpdated' && singleActivity.ActivityType != 'ProfileCoverUpdated' && singleActivity.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{singleActivity.ImageServerPath + 'upload/album/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                    <img ng-if="singleActivity.ActivityType != 'ProfilePicUpdated' && singleActivity.ActivityType != 'ProfileCoverUpdated' && singleActivity.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"   ng-src="{{singleActivity.ImageServerPath + 'upload/wall/750x500/' + media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                    <img ng-if="singleActivity.ActivityType == 'ProfilePicUpdated'" ng-src="{{singleActivity.ImageServerPath + 'upload/profile/220x220/' + media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType == 'ProfileCoverUpdated'" ng-src="{{singleActivity.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                    <!-- Media Ends -->
                                    <i class="icon-n-video-big" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Finished'"></i>
                                    <div ng-if="$last && singleActivity.Album[0].TotalMedia > 4 && singleActivity.Album[0].Media.length > 1" class="more-content"><span ng-bind="'+' + (singleActivity.Album[0].TotalMedia - 3)"></span></div>
                                </a>
                                <div class="post-video" ng-if="media.MediaType == 'Video' && media.ConversionStatus == 'Pending'">
                                    <div class="wall-video pending-rating-video">
                                        <i class="icon-video-c"></i>
                                    </div>
                                </div>
                            </figure>
                        </div>
                        <div ng-if="singleActivity.PollData.length == 0" class="share-content">
                            <div class="share-inr-space tagging">
                                <a href="javascript:void(0);" ng-if="singleActivity.PostType !== '7'" ng-bind="singleActivity.UserName"></a>
                                <a href="javascript:void(0);" ng-if="singleActivity.PostType == '7'" ng-bind="singleActivity.EntityName"></a>
                                <p ng-bind-html="textToLink(singleActivity.PostContent)"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right wall-btns">
                    <!-- Privacy Buttons -->
                    <button id="shareComment" class="own-wall-settings btn btn-default btn-icon btn-onoff on" type="button"> <i class="icon-on"></i> <span>On</span> </button>
                    <div class="btn-group custom-icondrop own-wall-settings own-wall-privacy">
                        <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle drop-icon" type="button"> <i class="icon-share"></i> <span class="caret"></span> </button>
                        <ul role="menu" class="dropdown-menu pull-left dropdown-withicons">
                            <li><a onClick="$('#shareVisibleFor').val(1);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-every"></i></span>Everyone</a></li>
                            <!-- <li><a onClick="$('#shareVisibleFor').val(2);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-follwers"></i></span>Friends of Friend</a></li> -->
                            <li><a onClick="$('#shareVisibleFor').val(3);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-frnds"></i></span>Friends</a></li>
                            <li><a onClick="$('#shareVisibleFor').val(4);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-onlyme"></i></span>Only Me</a></li>
                        </ul>
                    </div>
                    <!-- Privacy Buttons -->
                    <button class="btn btn-primary" ng-click="shareActivity()" type="button">SHARE</button>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="shareVisibleFor" value="1" />
<input type="hidden" id="shareCommentSettings" value="1" />
<input type="hidden" id="ShareModuleEntityGUID" value="" />
<input type="hidden" id="ShareEntityUserGUID" value="" />
<!-- Share Popup Code Ends -->
<style type="text/css">
    #divLoader {
        position: fixed;
        bottom: 15%;
        top: auto;
    }
</style>