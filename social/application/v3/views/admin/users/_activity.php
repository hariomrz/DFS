<div class="panel panel-primary" ng-repeat="data in activityData" repeat-done="callToolTip()">
    <div class="panel-body feed-act-{{data.ActivityGUID}}">
       <ul class="list-group list-group-thumb sm">
            <li class="list-group-item">
                <div class="list-group-body">
                    <div class="btn-toolbar btn-toolbar-right dropdown">
                        <a class="btn btn-xs btn-default btn-icn" data-toggle="dropdown" role="button"><span class="icn"><i class="ficon-dots"></i></span></a>
                        <input type="hidden" ng-value="baseURL+data.ActivityURL" id="a-{{data.ActivityID}}" />
                        <ul class="dropdown-menu dropdown-menu-right" ng-if="data.IsDeleted == 0">
                            <li><a ng-click="copyToClipboard(data.ActivityID)">Copy URL</a></li>
                            <li><a ng-click="editPost(data.ActivityGUID,$event)">Edit</a></li>
                            <li><a ng-if="data.CommentsAllowed=='1'" ng-cloak ng-click="commentsSwitchEmit('ACTIVITY', data.ActivityGUID)">Turn Comments Off</a></li>
                            <li><a ng-if="data.CommentsAllowed=='0'" ng-cloak ng-click="commentsSwitchEmit('ACTIVITY', data.ActivityGUID)">Turn Comments On</a></li>
                            <li><a ng-click="deleteEmit(data.ActivityGUID);">Remove Post</a></li>
                        </ul>
                        <ul class="dropdown-menu dropdown-menu-right" ng-if="data.IsDeleted == 1">
                            <li><a ng-click="restoreEmit(data.ActivityGUID);">Restore</a></li>
                            <li><a ng-click="deleteEmit(data.ActivityGUID);">Delete Permanently</a></li>
                        </ul>    
                    </div>
                    <figure class="list-figure">
                        <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{data.UserGUID}}" ng-href="{{baseURL + data.UserProfileURL}}">
                            <img err-name="{{data.UserName}}"   class="img-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}">
                        </a>
                    </figure>
                    <div class="list-group-content">
                        <h5 class="list-group-item-heading">                                                    
                            <span ng-bind-html="getTitleMessage(data)"></span>
                        </h5>
                        <ul class="list-activites">
                            <li ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(data.CreatedDate));}}" ng-bind="date_format((data.CreatedDate))"></li>
                            <li><span class="icn"><i class="ficon-earth"></i></span></li>
                        </ul>
                    </div>   
                </div>
                <h4 class="post-type-title"><a ng-bind="data.PostTitle"></a></h4>

                <!-- -->

                <!-- Tags add edit view -->

                <div class="tag-post-edit" ng-if="data.showTags" ng-cloak>
                <span class="icon">
                 <svg class="svg-icons no-hover" height="16px" width="16px">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#icnTag"></use>
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
                        <span class="icon">
                         <svg class="svg-icons" height="18px" width="18px">
                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnCheck"></use>
                        </svg>
                    </span>
                    </li>
                    <li ng-click="initTagsItem(FeedIndex);">
                        <span class="icon">
                         <svg class="svg-icons" height="22px" width="22px">
                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#closeIcon"></use>
                        </svg>
                    </span>
                    </li>
                </ul>
            </div>

                <!-- // Tags  add edit view -->

                <div class="mediaPost" ng-class="get_img_class(data.Album[0].Media)">
                    <figure ng-repeat="media in data.Album[0].Media|limitTo:4"  class="media-thumbwrap">
                        <a ng-if="media.ConversionStatus!=='Pending'" class="mediaThumb" image-class="{{layoutClass(data.Album[0].Media)}}">
                            <!-- Media Thumbs -->
                            <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'"   ng-src="{{data.ImageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                            <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'"   ng-src="{{data.ImageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                            
                            <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'"   ng-src="{{data.ImageServerPath+'upload/album/750x500/'+  media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg' }}" />
                            <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'"   ng-src="{{data.ImageServerPath+'upload/wall/750x500/'+ media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />

                            <img ng-if="data.ActivityType=='ProfilePicUpdated'" ng-src="{{data.ImageServerPath+'upload/profile/220x220/'+media.ImageName}}" />
                            <img ng-if="data.ActivityType=='ProfileCoverUpdated'" ng-src="{{data.ImageServerPath+'upload/profilebanner/1200x300/'+media.ImageName}}" />

                             <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'" style="width:1px;" ng-src="{{data.ImageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                            <img ng-if="data.ActivityType!='ProfilePicUpdated' && data.ActivityType!='ProfileCoverUpdated' && data.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'" style="width:1px;" ng-src="{{data.ImageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                            <img ng-if="data.ActivityType=='ProfilePicUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath+'upload/profile/220x220/'+media.ImageName}}" />
                            <img ng-if="data.ActivityType=='ProfileCoverUpdated'" style="width:1px;" ng-src="{{data.ImageServerPath+'upload/profilebanner/1200x300/'+media.ImageName}}" /> 

                            <i class="icon-n-video-big" ng-if="media.MediaType=='Video' && media.ConversionStatus=='Finished'"></i>
                            <!-- Media Thumbs -->
                            <div ng-if="$last && data.Album[0].TotalMedia>4 && data.Album[0].Media.length>1" class="more-content"><span ng-bind="'+'+(data.Album[0].TotalMedia-4)"></span></div>
                            <div class="t"></div>
                            <div class="r"></div>
                            <div class="b"></div>
                            <div class="l"></div>

                        </a>
                        <!-- Video Process Thumb -->
                        <div class="post-video" ng-if="media.MediaType=='Video' && media.ConversionStatus=='Pending'">
                          <div class="wall-video pending-rating-video">
                              <i class="icon-video-c"></i>
                          </div>  
                        </div>
                        
                        <!-- Video Process Thumb -->
                    </figure>
                </div>
                <!-- -->

                <div class="list-group-footer">                                                         
                    <ul class="list-group-inline pull-left">
                        <li>
                            <a ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);" class="bullet" ng-class="(data.IsLike=='1') ? 'active' : '' ;">
                                <i class="ficon-heart"></i>
                            </a> 
                            <a class="text" ng-if="data.NoOfLikes>0" ng-bind="data.NoOfLikes"></a>
                        </li>
                        <li ng-click="toggleTagsItem(FeedIndex,data.ActivityGUID);" tooltip data-placement="top" title="Add Tags">
                        <svg height="16px" width="16px" class="svg-icons">
                            <use xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnTag"></use>
                        </svg>
                    </li>
                    </ul>
                    <span class="blue-circle pull-right icn" ngx-tipsy="s" ng-attr-original-title="{{getPostTooltip(data.PostType)}}">
                        <svg height="16px" width="16px" class="svg-icons">
                            <use  xlink:href="" ng-href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#{{getPostIcon(data.PostType)}}"></use>
                        </svg>
                    </span>
                </div>

            </li>
        </ul>
    </div>
    <?php $this->load->view('admin/users/comments') ?>
</div>