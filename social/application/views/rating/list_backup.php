<div ng-repeat="list in ratingList" ng-init="RatingIndex = $index;" class="feed-list" id="r-{{list.RatingGUID}}" repeat-done="textautoSize();" ng-cloak>
    <div class="feed-header" ng-class="'feed-act-' + list.ActivityGUID">
        <label ng-if="list.IsEdited == 1" class="review-edited"><?php echo lang('edited') ?></label>      
        <div class="panel-header" ng-if="list.MutualFriends.TotalRecords > 0 || (list.IsOwner == 1 && list.CreatedBy.ModuleID == '3')">
            <span ng-if="list.IsOwner == 0 && list.MutualFriends.TotalRecords > 0" class="color-999" ng-bind-html="getMutualFriends(list.MutualFriends);"></span>
            <span ng-if="list.IsOwner == 1 && list.CreatedBy.ModuleID == '3'" class="semi-bold"><?php echo lang('my_review_caps') ?></span>
        </div>
        <div class="feed-header-left">
            <figure class="thumb-sm">
                <a ng-href="{{'<?php echo site_url() ?>' + list.CreatedBy.ProfileURL}}">
                    <img  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + list.CreatedBy.ProfilePicture}}">
                </a>
            </figure>
            <div><a ng-href="{{'<?php echo site_url() ?>' + list.CreatedBy.ProfileURL}}" ng-bind="list.CreatedBy.EntityName"></a></div>            
        </div>
    </div>
    <div class="feed-body" ng-class="(list.Polllist.length > 0) ? 'poll-feed-listing' : '';">
        <h4> 
            <span ng-class="{'badgerate-1':(list.RateValue < 1.6),'badgerate-2':(list.RateValue > 1.5 && list.RateValue < 2.6),'badgerate-3':(list.RateValue > 2.5 && list.RateValue < 3.6),'badgerate-4':(list.RateValue > 3.5 && list.RateValue < 4.6),'badgerate-5':(list.RateValue > 4.5)}" ng-bind="list.RateValue"></span> 
            <a ng-bind-html="textToLink(list.Review.Title)"></a>
        </h4>
        <p ng-bind-html="textToLink(list.Review.Description)">        
        </p>
        
        <ul class="rated-list">
            <li ng-repeat="RPV in list.RatingParameterValue" >
                <span ng-class="{'badgerate-1':(RPV.RateValue < 1.6),'badgerate-2':(RPV.RateValue > 1.5 && RPV.RateValue < 2.6),'badgerate-3':(RPV.RateValue > 2.5 && RPV.RateValue < 3.6),'badgerate-4':(RPV.RateValue > 3.5 && RPV.RateValue < 4.6),'badgerate-5':(RPV.RateValue > 4.5)}" 
                      ng-bind="RPV.RateValue">
                </span> 
                {{RPV.ParameterName}}
            </li>                     
        </ul>
        
        
        <div class="post-media " ng-if="list.Album.length > 0" 
             id="lg-{{list.RatingGUID}}" ng-class="{'single' : (list.Album[0].Media.length == 1), 'two' : (list.Album[0].Media.length == 2), 'morethan-two' : (list.Album[0].Media.length > 2) }">
            <figure
                ng-if="list.Album[0].Media[0].MediaType == 'Image'  && list.Album.length > 0"
                ng-repeat="media in list.Album[0].Media" 
                ng-init="callLightGallery(list.RatingGUID)" 
                ng-data-thumb="{{getImagePath(media.MediaType, media.ImageName)}}" 
                ng-data-src="{{getImagePath(media.MediaType, media.ImageName, 'original')}}"
            >
                <img  ng-if="media.MediaType == 'Image'" ng-src="{{getImagePath(media.MediaType, media.ImageName)}}">
            </figure>
            <figure
                ng-if="list.Album[0].Media[0].MediaType == 'Video'  && list.Album.length > 0 && media.ConversionStatus == 'Finished'"
                ng-repeat="media in list.Album[0].Media" 
                ng-init="(media.ConversionStatus == 'Finished') ? callLightGallery(list.RatingGUID) : '';"  
                ng-data-html="{{'#m-' + media.MediaGUID}}" 
                ng-data-thumb="{{getVideoPath(media.ImageName) + 'jpg'}}"
            >
                <img  ng-src="{{getVideoPath(media.ImageName, 1) + 'jpg'}}"/>
                <div style="display:none;" 
                    id="m-{{list.Album[0].Media[0].MediaGUID}}" 
                    class="video-block" 
                    ng-if="list.Album[0].Media[0].MediaType == 'Video' && list.Album[0].Media[0].ConversionStatus !== 'Pending'">
                   <video ng-repeat="media in list.Album[0].Media" width="100%" controls="" class="object">
                       <source type="video/mp4" src="" dynamic-url dynamic-url-src="{{getVideoPath(media.ImageName) + 'mp4'}}"></source>
                       <source type="video/ogg" src="" dynamic-url dynamic-url-src="{{getVideoPath(media.ImageName) + 'ogg'}}"></source>
                       <source type="video/webm" src="" dynamic-url dynamic-url-src="{{getVideoPath(media.ImageName) + 'webm'}}"></source>
                       Your browser does not support HTML5 video.
                   </video>
               </div>
            </figure>
        </div>
        
        <div class="content-row">
            <button id="rf-{{list.RatingGUID}}" ng-click="flagRating(list.RatingGUID)" ng-if="list.Flaggable == '1' && list.IsFlagged == '0' && list.IsOwner == 0" data-toggle="modal" data-target="#reportAbuse" data-placement="top" data-toggle="tooltip" class="btn btn-default btn-sm pull-right m-t-5" type="button" data-original-title="flag"><i class="icon-flag"></i></button>
            
            <div ng-if="list.TotalVoteCount > 0">
                {{list.PositiveVoteCount}} 
                <span class="color-999">of</span> {{list.TotalVoteCount}} <span class="color-999">users found this review helpful.</span>
            </div>
            <sapn  ng-if="list.TotalVoteCount == 0 && list.IsOwner == 0" class="color-999">Be the first one to answer this.</sapn>

            <span ng-if="list.IsVoted == 0 && list.IsOwner == 0">Was this useful? <span class="inline-link"> <a ng-click="vote(list.RatingGUID, 'YES')">Yes</a>  <a ng-click="vote(list.RatingGUID, 'NO')">No</a></span></span>
            <span ng-if="list.IsVoted == 1 && list.JustVoted == 1" class="color-999">Thank you for your vote</span>


        </div>
    </div>
    <div class="feed-footer" ng-class="(list.NoOfComments > 0) ? 'is-comments' : '';">
        <span>
            <div class="load-more load-more-comment" ng-if="(list.NoOfComments > 2 && list.Comments.length < list.NoOfComments) || (list.NoOfComments > 4)">
               <a  ng-if="list.Comments.length < list.NoOfComments"  data-ng-click="viewAllComntEmit(FeedIndex, list.ActivityGUID);" ng-bind="'See all ' + list.NoOfComments + ' comments'"></a>
                
                <div class="btn-group pull-right" ng-if="list.NoOfComments > 4">
                    <button class="btn btn-xs btn-link dw-button" data-toggle="dropdown"><span ng-if="list.PostType != 2"> Sort Comments</span><span ng-if="list.PostType == 2"> Sort Answers</span><span class="caret"></span></button>
                    <ul class="active-with-icon dropdown-menu">
                        <li onclick="addActiveClass(this)"><a data-ng-click="viewAllComntEmit(FeedIndex, list.ActivityGUID, 'Recent');">Recently Updated</a></li>
                        <li onclick="addActiveClass(this)"><a data-ng-click="viewAllComntEmit(FeedIndex, list.ActivityGUID, 'Popular');">Popularity</a></li>
                        <li onclick="addActiveClass(this)"><a data-ng-click="viewAllComntEmit(FeedIndex, list.ActivityGUID, 'Network');">My Network</a></li>
                    </ul>
                </div>
                
            </div>



            <ul class="listing-group list-group-close">
                <li ng-repeat="comnt in list.Comments|orderBy:'-BestAnswer' track by comnt.CommentGUID" id="{{comnt.CommentGUID}}" ng-class="{'most-appropriate-answer':comnt.BestAnswer == '1'}">
                    <div class="list-items-sm">         
                        <div class="comment-action"  ng-if="edit_comment_box != comnt.CommentGUID && list.PostType != '2' && (list.IsEntityOwner == '1' || comnt.IsOwner == 1) && comnt.CanDelete == '1'">
                            <i class="ficon-dots" data-toggle="dropdown" aria-expanded="true">&nbsp;</i>
                            <ul class="dropdown-menu" >
                                <li ng-if="comnt.IsOwner == 1" ng-click="commentEditBlock(comnt.CommentGUID, list.ActivityGUID, comnt)"><a>Edit</a></li>
                                <li ng-click="deleteCommentEmit(comnt.CommentGUID, list.ActivityGUID);" ng-if="comnt.CanDelete == '1' && list.PostType !== '2'"><a>Delete</a></li>
                                <li ng-click="insert_to_editor(list.ActivityGUID, comnt.PostComment, FeedIndex);"><a>Quote</a></li>
                            </ul>
                        </div>
                        
                        <div class="comment-action"  ng-if="edit_comment_box != comnt.CommentGUID && list.PostType == '2' && (list.IsEntityOwner == '1' || comnt.IsOwner == 1) && comnt.CanDelete == '1'">
                            <i class="ficon-dots" data-toggle="dropdown" aria-expanded="true">&nbsp;</i>
                            <ul class="dropdown-menu" >
                                <li ng-click="commentEditBlock(comnt.CommentGUID, list.ActivityGUID, comnt)"><a>Edit</a></li>
                                <li ng-if="list.PostType == '2' && list.IsOwner == '1'"><a ng-click="mark_best_answer(list.ActivityGUID, comnt.CommentGUID)">Most Appropriate Answer</a></li>
                                <li ng-if="list.PostType == '2' && comnt.CanDelete == '1'"><a ng-click="deleteCommentEmit(comnt.CommentGUID, list.ActivityGUID, '', list.PostType);">Remove Answer</a></li>
                                </ul>
                                </div>


            <div class="list-inner">
                <figure>
                    <a ng-cloak ng-if="comnt.ModuleID == '18'" ng-href="{{list.SiteURL + comnt.ProfileLink}}" class="loadbusinesscard" entitytype="page" entityguid="{{comnt.UserGUID}}"><img ng-if="comnt.ProfilePicture !== ''" class="img-circle"   ng-src="{{ImageServerPath + 'upload/profile/220x220/' + comnt.ProfilePicture}}"></a>
                    <a ng-cloak ng-if="comnt.ModuleID == '3'" ng-href="{{list.SiteURL + comnt.ProfileLink}}" class="loadbusinesscard" entitytype="user" entityguid="{{comnt.UserGUID}}"><img err-Name="{{comnt.Name}}" class="img-circle"   ng-src="{{ImageServerPath + 'upload/profile/220x220/' + comnt.ProfilePicture}}"></a>
                            </figure>
                            <div class="list-item-body" ng-if="edit_comment_box != comnt.CommentGUID">
                                <div>
                                    <a class="user-name" ng-bind="comnt.Name"></a> 
                                    
                                    <span ng-bind-html="textToLinkComment(comnt.PostComment)"></span>
                                    
                                </div>

                                <div ng-if="comnt.Media && comnt.Media.length > 0" ng-class="getMediaClass(comnt.Media)">
                                    <figure ng-repeat="m in comnt.Media| limitTo:4">
                                        <img ng-click="$emit('showMediaPopupGlobalEmit', m.MediaGUID, '');" ng-src="{{ImageServerPath + 'upload/comments/' + m.ImageName}}">
                                        <span ng-if="$index == 3 && comnt.Media.length > 4" class="more-content" ng-bind="'+' + comnt.Media.length - 4"></span>
                                    </figure>
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
                                        <li ng-if="list.PostType !== '2'">
                                            <span class="like-btn" tooltip data-placement="top" ng-attr-data-original-title="{{(comnt.IsLike == '1') ? 'Unlike' : 'Like' ;}}">
                                                <i data-ng-click="$emit('likeEmit', comnt.CommentGUID, 'COMMENT', list.RatingGUID);" class="ficon-heart sm" ng-class="(comnt.IsLike) ? 'active' : '';"></i>
                                                <abbr ng-if="comnt.NoOfLikes > 0" ng-bind="comnt.NoOfLikes" ng-click="$emit('likeDetailsEmit', comnt.CommentGUID, 'COMMENT');" class="sm"></abbr>
                                            </span>
                                        </li>
                                        <li><span ng-attr-title="{{getTimeFromDate(UTCtoTimeZone(comnt.CreatedDate));}}" ng-bind="date_format(comnt.CreatedDate, 'MMM D, YYYY');"></span></li>
                                    </ul>
                                    
                                    <ul class="feed-action-right"   ng-if="comnt.BestAnswer == 1 && data.PostType == '2'">
                                        <li class="appropriate">
                                          <span class="text" >Most appropriate answer</span>
                                          <span class="icon" ><img ng-src="{{data.SiteURL + 'assets/img/appropriate-answer.png'}}" ></span>
                                        </li>
                                    </ul>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <span>
                <div class="post-comments">
                    <div class="user-thmb" style="display:none">
                        <img ng-if="list.IsOwner == 0" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + LoggedInProfilePicture}}" class="img-circle current-profile-pic" />
                        <img ng-if="list.IsOwner == 1" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + list.CreatedBy.ProfilePicture}}" class="img-circle current-profile-pic" />
                    </div>
                    <div class="wall-comments">
                        <div class="textarea-wrap">
                            <textarea custom-comment-box id="cmt-{{list.RatingGUID}}" data-ng-keypress="submitComment($event, list.RatingGUID, list.IsOwner, list.CreatedBy.ModuleID, RatingIndex, list.ActivityGUID)" class="form-control comment-text textNtags" placeholder="Write a comment..."></textarea>
                        </div>

                        <div class="attach-on-comment">
                            <span class="icon" ngf-select="uploadFiles($files, $invalidFiles, list.RatingGUID, RatingIndex)" multiple ngf-validate-async-fn="validateFileSize($file);">
                                <i class="ficon-attachment"></i>
                            </span> 
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </span>
            <!-- New Ends -->
        </span>
    </div>
</div>