<script type="text/javascript">
    var IsAdminActivity = 1;
    var TimeZone = 'Asia/Calcutta';
</script>

<div id="activityFeedId-{{ FeedIndex}}" 
    ng-repeat="data in activityData track by $index" 
    repeat-done="wallRepeatDone(); callToolTip();" 
    ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index; initTagsItem($index); " 
    viewport-watch class="news-feed-listing" 
    ng-class="{'panel panel-primary not-collapsed':config_detail.IsCollapse == 1, 'not-collapsed':config_detail.IsCollapse == 0,'overlay-content': data.stickynote}">
    <!-- new Template -->
    <div class="inner-wall-post ">
        <div class="feed-header-block">
            <div ng-if="IsSingleActivity && data.PostTitle != ''" class="post-type-title" ng-bind="data.PostTitle"></div>
            <span class="sticky" ng-if="IsNewsFeed == '0' && (data.IsSticky == '1' || data.SelfSticky == '1')">                                        
                <i class="ficon-pin rotate-45"></i>
            </span>
            <div class="feed-header">
                <a class="thumb-48 " entitytype="page" entityguid="{{data.UserGUID}}" ng-if="data.PostAsModuleID == '18' && data.ActivityType !== 'ProfilePicUpdated' && data.ActivityType !== 'ProfileCoverUpdated'" ng-href="{{data.SiteURL + 'page/' + data.UserProfileURL}}">
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 " entitytype="user" entityguid="{{data.UserGUID}}" ng-if="data.PostAsModuleID == '3' && data.ActivityType !== 'ProfilePicUpdated' && data.ActivityType !== 'ProfileCoverUpdated'" >
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 " entitytype="user" entityguid="{{data.UserGUID}}" ng-if="(data.ActivityType == 'ProfilePicUpdated' || data.ActivityType == 'ProfileCoverUpdated') && data.ModuleID !== '18'" >
                    <img ng-if="data.UserProfilePicture !== '' && data.UserProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.UserProfilePicture}}">
                    <span ng-if="(data.UserProfilePicture == '' || data.UserProfilePicture == 'user_default.jpg') && data.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(data.UserName)"></span></span>
                </a>
                <a class="thumb-48 " entitytype="page" entityguid="{{data.EntityGUID}}" ng-if="(data.ActivityType == 'ProfilePicUpdated' || data.ActivityType == 'ProfileCoverUpdated') && data.ModuleID == '18'" ng-href="{{data.SiteURL + 'page/' + data.EntityProfileURL}}">
                    <img ng-if="data.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.EntityProfilePicture}}">
                </a>
                <a class="thumb-48 " entitytype="user" entityguid="{{data.UserGUID}}" ng-if="data.PostType == '7' && data.ModuleID == '3'" >
                    <img err-Name="{{data.UserName}}"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.UserProfilePicture}}">
                </a>
                <a class="thumb-48 " entitytype="group" entityguid="{{data.EntityGUID}}" ng-if="data.PostType == '7' && data.ModuleID == '1'" ng-href="{{data.SiteURL + 'group/' + data.EntityProfileURL}}">
                    <img ng-if="data.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/' + data.EntityProfilePicture}}">
                </a>
                <div class="user-info">
                    <span ng-bind-html="getTitleMessage(data)"></span>
                    <span tooltip data-placement="top" data-original-title="Expert" ng-if="data.IsExpert == '1'" class="icon group-expert">
                        <svg height="14px" width="14px" class="svg-icons">
                        <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnExpert"></use>
                        </svg>
                    </span>
                    <ul class="sub-navigation">
                        <li>
                            <span ng-if="data.Occupation">{{data.Occupation}}</span>
                            <span ng-if="data.Locality.Name">{{data.Locality.Name}}, {{data.Locality.WName}} (Ward {{data.Locality.WNumber}})</span>
                            <span ng-cloak ng-bind="createDateObject(UTCtoTimeZone(data.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></span>
                        </li>
                        
                    </ul>
                </div>
              
            </div>
            
        </div>
        <div class="collapse-content feed-act-{{data.ActivityGUID}}">
            <div bindonce id="act-{{data.ActivityGUID}}" class="activitywrapper" ng-class="{'inview' : data.Viewed == '0'}">                
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
                                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnArrowDown"></use>
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
                                                <img ng-if="history.ActivityType == 'ProfilePicUpdated'" ng-src="{{data.ImageServerPath + 'upload/profile/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType == 'ProfileCoverUpdated'" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType != 'ProfilePicUpdated' && history.ActivityType != 'ProfileCoverUpdated' && history.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                                <img ng-if="history.ActivityType == 'ProfilePicUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profile/' + media.ImageName}}" />
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
                               <!-- <div ng-repeat="link in history.Links" ng-if="history.Links && (history.showAllLinks == 1 || $index < 3)" class="network-block clearfix m-t-15" ng-include src="partialURL+'activity/Network.html'"></div> -->
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
                                <img ng-if="data.ActivityType == 'ProfilePicUpdated'" ng-src="{{data.ImageServerPath + 'upload/profile/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType == 'ProfileCoverUpdated'" ng-src="{{data.ImageServerPath + 'upload/profilebanner/1200x300/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName !== 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/album/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType != 'ProfilePicUpdated' && data.ActivityType != 'ProfileCoverUpdated' && data.Album[0].AlbumName == 'Wall Media' && media.MediaType == 'Image'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/wall/750x500/' + media.ImageName}}" />
                                <img ng-if="data.ActivityType == 'ProfilePicUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath + 'upload/profile/' + media.ImageName}}" />
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
                   <!-- <div ng-repeat="link in data.Links" ng-if="data.Links && (data.showAllLinks == 1 || $index < 3)" class="network-block clearfix m-t-15" ng-include src="partialURL+'activity/Network.html'"></div> -->
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
                           <li ng-if="data.LikeAllowed == '1'" ng-class="(data.IsLike == '1') ? 'active' : '';" class="iconlike" uib-tooltip="{{(data.IsLike == '1') ? 'Unlike' : 'Like' ;}}">
                                <span ng-disabled="data.IsDeleted == 1" ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);">
                                    <i class="ficon-heart"></i>
                                </span>
                            </li>
                            <li ng-if="data.NoOfLikes > 0" ng-bind="data.NoOfLikes" class="view-count">
                            </li>

                            <li ng-if="data.CommentsAllowed == 1">
                                <a ng-click="postCommentEditor(data.ActivityGUID, FeedIndex);  data.showeditor = true;" ng-if="LoginSessionKey!='' && data.NoOfComments == 0">
                                    Be the first to comment
                                </a>
                                
                                <a ng-if="data.NoOfComments > 0" ng-bind="'Comments (' + data.NoOfComments + ')'" ng-click="viewAllComntEmit(FeedIndex, data.ActivityGUID);"></a>
                            </li>
                          
                        </ul>                        
                        <div class="section-groups">
                           
                            <div class="pull-right  pull-right-page">
                                <div class="post-as-page"> 
                                    <div class="dropdown m-t-m2 m-r-10">
                                        <button type="button" data-guid="act-{{data.ActivityGUID}}" id="btn-{{data.ActivityGUID}}" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
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
                                                    <a ng-class="(data.actionas.UserGUID == entitylist.UserGUID) ? 'active' : '';" 
                                                    ng-click="changeActionAs(entitylist, data.ActivityGUID);  refresh_show_user_pic();">
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
                                        <a><img err-name="{{ActivityFriend.FirstName + ' ' + ActivityFriend.LastName}}" ng-src="{{data.ImageServerPath + 'upload/profile/' + ActivityFriend.ProfilePicture}}" class="img-circle"  ></a>
                                    </figure>
                                    <div class="description">
                                        <a class="name" ng-bind="ActivityFriend.FirstName + ' ' + ActivityFriend.LastName"></a>
                                        <span class="location" ng-bind="ActivityFriend.AnswerCount + ' Answers'"></span>
                                    </div>
                                    <ul class="edit-save" ng-cloak>
                                        <li>
                                            <span class="icon">
                                                <svg  class="svg-icons" height="18px" width="18px">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnPlus"></use>
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
                            <span class="text">View All Comments</span>
                            <span class="caret"></span>
                        </a>
                    </div>
                    <div class="comment-on-post"> 
                        <ul class="list-group list-group-thumb sm">
                            <li class="list-group-item" ng-repeat="comnt in data.Comments|orderBy:'-BestAnswer' track by comnt.CommentGUID">
                                <div class="comment-listing-content">
                                    <div class="list-group-body">
                                        <div  class="btn-toolbar btn-toolbar-right dropdown">
                                            <a class="rotate-90 block" data-toggle="dropdown" role="button"><span class="icn"><i class="ficon-dots f-16"></i></span></a>                                            
                                            <ul class="dropdown-menu feedaction dropdown-menu-right">
                                                
                                                <li ng-click="deleteCommentEmit(comnt.CommentGUID, data.ActivityGUID, '', '', comnt.IsOwner);"><a>Delete</a></li>
                                                
                                            </ul>                                            
                                        </div>
                                        <figure class="list-figure">
                                            <a ng-cloak ng-href="{{base_url + comnt.ProfileLink}}" class="ng-thumb-30 " entitytype="user" entityguid="{{comnt.UserGUID}}"><img err-Name="{{comnt.Name}}"   ng-src="{{data.ImageServerPath + 'upload/profile/' + comnt.ProfilePicture}}"></a>
                                        </figure>
                                        <div class="list-group-content">
                                            <h5 class="list-group-item-heading">                                               
                                                <a ng-href="{{base_url + comnt.ProfileLink}}" ng-bind="comnt.Name">Leon Yates</a>
                                            </h5>
                                            <ul class="list-activites">
                                                <li >
                                                    <span ng-if="comnt.Occupation" class="ng-binding ng-scope">{{comnt.Occupation}}<br></span>
                                                    <span ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(comnt.CreatedDate));}}" ng-bind="date_format((comnt.CreatedDate));"></span>
                                                </li>
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
                                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#fileIcon"></use>
                                                                        </svg> 
                                                                        <span ng-bind="'.' + file.MediaExtension"></span>
                                                                    </span>
                                                                    <span ng-show="file.progress" class="file-name" ng-bind="file.OriginalName"></span>
                                                                    <i ng-show="file.progress" class="dwonload icon hover" ng-click="removeEditAttachement('file', comnt.Files, FeedIndex);">
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
                                                        <a ng-cloak ng-if="reply.ModuleID == '18'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="ng-thumb-30 " entitytype="page" entityguid="{{reply.UserGUID}}"><img ng-if="reply.ProfilePicture !== ''"   ng-src="{{data.ImageServerPath + 'upload/profile/' + reply.ProfilePicture}}"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '3'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="ng-thumb-30 " entitytype="user" entityguid="{{reply.UserGUID}}"><img err-Name="{{reply.Name}}"   ng-src="{{data.ImageServerPath + 'upload/profile/' + reply.ProfilePicture}}"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '18'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="" entitytype="page" entityguid="{{reply.UserGUID}}" ng-bind="reply.Name"></a>
                                                        <a ng-cloak ng-if="reply.ModuleID == '3'" ng-href="{{data.SiteURL + reply.ProfileLink}}" class="" entitytype="user" entityguid="{{reply.UserGUID}}" ng-bind="reply.Name"></a>
                                                        <span tooltip data-placement="top" data-original-title="Expert" ng-if="reply.IsExpert == '1'" class="icon group-expert">
                                                            <svg height="14px" width="14px" class="svg-icons">
                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnExpert"></use>
                                                            </svg>
                                                        </span>
                                                        <span class="location" ng-if="reply.Occupation">{{reply.Occupation}}</span>
                                                        
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
                                                        <li ng-if="reply.CanDelete == '1'" ng-click="deleteCommentEmit(reply.CommentGUID, data.ActivityGUID, comnt.CommentGUID, '', reply.IsOwner);" data-toggle="tooltip" data-placement="top"  data-original-title="Remove"><i class="icon-n-close"></i> </li>
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
    </div>
</div>
<div ng-cloak ng-if="activityData.length == 0" class="panel panel-primary">
        <div class="panel-body nodata-panel">
            <div class="nodata-text p-v-lg">
                <span class="nodata-media">                    
                    <p class="text-off ng-binding">No content shared yet!</p>
                </div>
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
                                    <img ng-if="singleActivity.ActivityType == 'ProfilePicUpdated'" ng-src="{{singleActivity.ImageServerPath + 'upload/profile/' + media.ImageName}}" />
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
