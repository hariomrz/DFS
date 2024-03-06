<div>
    <div ng-controller="DashboardFeedController" ng-init="getUserOrientation();"> 
        <div>
            <?php $this->load->view('admin/orientation/filters'); ?>
            <section class="main-container">
                <div class="container">
                    <div class="page-heading">
                        <div class="row">
                            <div class="col-xs-1">
                                <h4 class="page-title">Posts ({{activityTotalRecord}})</h4>
                            </div>
                            <div class="col-xs-9">
                                <ul class="tags-list">
                                    <li ng-click="set_top_post_filter(1)" class="pointer" ng-class="{ 'tag-primary' : ( TopPostFilter == 1 ) }">
                                        <span>All POST</span>
                                    </li>
                                    <li ng-click="set_top_post_filter(2)" class="pointer" ng-class="{ 'tag-primary' : ( TopPostFilter == 2 ) }">
                                        <span>TOP POST</span>
                                    </li>
                                    <li ng-click="set_top_post_filter(3)" class="pointer" ng-class="{ 'tag-primary' : ( TopPostFilter == 3 ) }">
                                        <span>USER ORIENTATION</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-xs-2" >
                                <button ng-disabled="!showPublishButton" ng-click="saveUserOrientation();" class="btn btn-default btn-sm" type="button">SAVE</button>                                   
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?php $this->load->view('admin/orientation/feedList'); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <business-card data="businesscard"></business-card>

        <div class="modal fade" id="activity_details_popup_orientation" ng-cloak ng-if="popupActivityDataOri">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-close" ng-click="close_activity_details_popup_orientation();"></i></span></button>
                        <h4>Activity Details</h4>
                    </div>
                    <div class="modal-body">            
                        <div class="popup-content no-padding">
                            <div class="row-flued"> 
                                <div class="col-xs-7">
                                    <div class="panel panel-primary">
                                        <div class="panel-body">
                                            <ul class="list-group list-group-thumb sm">
                                                <li class="list-group-item">
                                                    <div class="list-group-body">
                                                        <figure class="list-figure">
                                                            <a class="thumb-48 " entitytype="page" entityguid="{{popupActivityDataOri.subject_user.UserGUID}}" ng-if="popupActivityDataOri.activity.PostAsModuleID == '18' && popupActivityDataOri.activity.ActivityTypeID !== 23 && popupActivityDataOri.activity.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + popupActivityDataOri.subject_user.UserProfileURL}}">
                                                                <img ng-if="popupActivityDataOri.activity.EntityProfilePicture !== 'user_default.jpg'" err-name="{{popupActivityDataOri.activity.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityDataOri.activity.EntityProfilePicture}}">
                                                            </a>
                                                            <a class="thumb-48 " entitytype="user" entityguid="{{popupActivityDataOri.subject_user.UserGUID}}" ng-if="popupActivityDataOri.activity.PostAsModuleID == '3' && popupActivityDataOri.activity.ActivityTypeID !== '23' && popupActivityDataOri.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + popupActivityDataOri.subject_user.UserProfileURL}}">
                                                                <img ng-if="popupActivityDataOri.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{popupActivityDataOri.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityDataOri.subject_user.ProfilePicture}}">
                                                            </a>
                                                            <a class="thumb-48 " entitytype="user" entityguid="{{popupActivityDataOri.subject_user.UserGUID}}" ng-if="(popupActivityDataOri.activity.ActivityTypeID == '23' || popupActivityDataOri.activity.ActivityTypeID == '24') && popupActivityDataOri.activity.ModuleID !== '18'" ng-href="{{baseUrl + popupActivityDataOri.subject_user.UserProfileURL}}">
                                                                <img err-name="{{popupActivityDataOri.subject_user.UserName}}" ng-if="popupActivityDataOri.subject_user.ProfilePicture !== '' && popupActivityDataOri.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityDataOri.subject_user.ProfilePicture}}">
                                                            </a>
                                                            <a class="thumb-48 " entitytype="page" entityguid="{{popupActivityDataOri.activity.EntityGUID}}" ng-if="(popupActivityDataOri.activity.ActivityTypeID == 23 || popupActivityDataOri.activity.ActivityTypeID == 24) && popupActivityDataOri.activity.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + popupActivityDataOri.activity.EntityProfileURL}}">
                                                                <img ng-if="popupActivityDataOri.activity.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityDataOri.activity.EntityProfilePicture}}">
                                                            </a>
                                                        </figure>
                                                        <div class="list-group-content" ng-init="activityTitleMessage='';">
                                                            <h6 class="list-group-item-heading" ng-bind-html="getTitleMessageOri(popupActivityDataOri)"></h6>
                                                            <ul class="list-activites">
                                                                <li ng-if="popupActivityDataOri.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(utc_to_time_zone(popupActivityDataOri.comment_details.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                                                <li ng-if="popupActivityDataOri.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(utc_to_time_zone(popupActivityDataOri.activity.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <!-- <ng-include ng-if="((popupActivityDataOri.activity.ActivityTypeID != 23) && (popupActivityDataOri.activity.ActivityTypeID != 24) && (popupActivityDataOri.activity.ActivityTypeID != 25) && (popupActivityDataOri.activity.ActivityTypeID != 16))" src="partialPageUrl + 'popupActivityContent.html'"></ng-include> -->
                                                    <div class="list-group-bottom">
                                                        <div id="std-{{popupActivityDataOri.activity.ActivityGUID}}" class="post-type-title">    
                                                            <p ng-if="popupActivityDataOri.activity.PostTitle!=''">
                                                                {{popupActivityDataOri.activity.PostTitle}}
                                                            </p>
                                                        </div>
                                                        <p ng-if="activityPostType[popupActivityDataOri.activity.ActivityTypeID]" class="list-group-item-text" ng-bind-html="textToLink(popupActivityDataOri.activity.PostContent)"></p>
                                                        <div ng-if="( popupActivityDataOri.activity.Album[0].Media.length > 0 ) " ng-class="layoutClass(popupActivityDataOri.activity.Album[0].Media)" class="feed-content mediaPost">
                                                            <figure ng-repeat="media in popupActivityDataOri.activity.Album[0].Media|limitTo:4" class="media-thumbwrap">
                                                                <video ng-if="popupActivityDataOri.activity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'" id="playerVideo" width="100%" controls controlsList="nodownload">
                                                                    <source type="video/mp4" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                    <source type="video/ogg" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                    <source type="video/webm" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                </video>
                                                                <video ng-if="popupActivityDataOri.activity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'" id="playerVideo" width="100%" controls controlsList="nodownload">
                                                                    <source type="video/mp4" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                    <source type="video/ogg" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                    <source type="video/webm" src="" dynamic-url dynamic-url-src="{{imageServerPath+ 'upload/wall/' +media.ImageName}}"></source>
                                                                </video>
                                                                <a ng-if="(media.ConversionStatus !== 'Pending')"  class="mediaThumb">
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID!=23 && popupActivityDataOri.activity.ActivityTypeID!=24 && popupActivityDataOri.activity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'" title="" alt="" ng-src="{{imageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID!=23 && popupActivityDataOri.activity.ActivityTypeID!=24 && popupActivityDataOri.activity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'" title="" alt="" ng-src="{{imageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID==23" ng-src="{{imageServerPath+'upload/profile/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID==24" ng-src="{{imageServerPath+'upload/profilebanner/1200x300/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID!=23 && popupActivityDataOri.activity.ActivityTypeID!=24 && popupActivityDataOri.activity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'" style="width:1px;" ng-src="{{imageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID!=23 && popupActivityDataOri.activity.ActivityTypeID!=24 && popupActivityDataOri.activity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'" style="width:1px;" ng-src="{{imageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID==23" style="width:1px;" ng-src="{{imageServerPath+'upload/profile/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.ActivityTypeID==24" style="width:1px;" ng-src="{{imageServerPath+'upload/profilebanner/1200x300/'+media.ImageName}}" />
                                                                    <div ng-if="$last && popupActivityDataOri.activity.Album[0].TotalMedia>4 && popupActivityDataOri.activity.Album[0].TotalMedia>1" class="more-content"><span ng-bind="'+'+(popupActivityDataOri.activity.Album[0].TotalMedia-4)"></span></div>
                                                                    <div class="t"></div>
                                                                    <div class="r"></div>
                                                                    <div class="b"></div>
                                                                    <div class="l"></div>
                                                                </a>
                                                                
                                                                <a ng-if="(media.ConversionStatus !== 'Pending')"  class="mediaThumb" image-class="{{layoutClass(popupActivityDataOri.activity.Album[0].Media)}}">
                                                                    <img ng-if="popupActivityDataOri.activity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'" title="" alt="" ng-src="{{imageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                                                                    <img ng-if="popupActivityDataOri.activity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'" title="" alt="" ng-src="{{imageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                                                                </a>
                                                                
                                                                <a  ng-if="( isCommentAttachment && ( media.ConversionStatus !== 'Pending' ) )" class="mediaThumb" image-class="{{addMediaClasses(popupActivityDataOri.activity.Album[0].TotalMedia)}}" >
                                                                    <img ng-if="media.MediaType == 'Image'" ng-src="{{imageServerPath + 'upload/comments/533x300/' + media.ImageName}}" alt="">
                                                                    <div ng-if="( $last && ( popupActivityDataOri.activity.Album[0].TotalMedia > 4 ) )" class="more-content"><span ng-bind="'+' + ( popupActivityDataOri.activity.Album[0].TotalMedia - 4 )"></span></div>
                                                                    <div class="t"></div>
                                                                    <div class="r"></div>
                                                                    <div class="b"></div>
                                                                    <div class="l"></div>
                                                                </a>
                                                                <div class="post-video" ng-if="media.MediaType=='Video' && media.ConversionStatus=='Pending'">
                                                                    <div class="wall-video pending-rating-video">
                                                                        <i class="icon-video-c"></i>
                                                                    </div>
                                                                </div>
                                                            </figure>
                                                        </div>
                                                        <div class="list-group-footer">
                                                            <ul class="list-group-inline">
                                                                <li>
                                                                    <a class="bullet">
                                                                        <i class="ficon-heart"></i>
                                                                    </a>
                                                                    <a ng-if="activityPostType[popupActivityDataOri.activity.ActivityTypeID] && ( ( popupActivityDataOri.activity.NoOfLikes != '' ) && ( popupActivityDataOri.activity.NoOfLikes > 0 ) )" class="text" ng-bind="popupActivityDataOri.activity.NoOfLikes"></a>
                                                                    <a ng-if="( popupActivityDataOri.activity.ActivityTypeID == 20 ) && ( ( activityData.comment_details.NoOfLikes != '' ) && ( activityData.comment_details.NoOfLikes > 0 ) )" class="text" ng-bind="activityData.comment_details.NoOfLikes"></a>
                                                                </li>
                                                                <li ng-if="activityPostType[popupActivityDataOri.activity.ActivityTypeID]">
                                                                    <a class="bullet">
                                                                        <i class="ficon-comment"></i>
                                                                    </a>
                                                                    <a class="text" ng-if="( ( popupActivityDataOri.activity.NoOfComments != '' ) && ( popupActivityDataOri.activity.NoOfComments > 0 ) )" ng-bind="popupActivityDataOri.activity.NoOfComments"></a>
                                                                </li>
                                                                <li ng-if="( popupActivityDataOri.activity.ActivityTypeID == 20 && !activityData.parent_comment_details.PostCommentID )">
                                                                    <a class="bullet">
                                                                        <i class="ficon-reply"></i>
                                                                    </a>
                                                                    <a  class="text" ng-if="( ( activityData.comment_details.NoOfReplies != '' ) && ( activityData.comment_details.NoOfReplies > 0 ) )" ng-bind="activityData.comment_details.NoOfReplies"></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-5">
                                    <div class="panel panel-primary">
                                        <div class="panel-body user-sm-info">
                                            <ul class="list-group list-group-thumb md">
                                                <li class="list-group-item">
                                                    <div class="list-group-body">
                                                        <figure class="list-figure">
                                                            <a class="" entitytype="user">
                                                                <img ng-if="((userPostDetail.UserDetails.ProfilePicture != '') && (userPostDetail.UserDetails.ProfilePicture !='user_default.jpg'))" class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + userPostDetail.UserDetails.ProfilePicture}}">
                                                                <span ng-if="((userPostDetail.UserDetails.ProfilePicture == '') || (userPostDetail.UserDetails.ProfilePicture =='user_default.jpg'))" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(userPostDetail.UserDetails.Name)"></span></span>
                                                            </a>
                                                        </figure>
                                                        <div class="list-group-content">
                                                            <h4 class="list-group-item-heading lg">
                                                                <a ng-bind="userPostDetail.UserDetails.Name"></a>
                                                                <a uib-tooltip="VIP User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsVIP == 1 )" class="icn circle-icn circle-primary">
                                                                    <i class="ficon-check"></i>
                                                                </a>
                                                                <a uib-tooltip="Association User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsAssociation == 1 )" class="icn circle-icn circle-primary">
                                                                    <i class="ficon-check"></i>
                                                                </a>
                                                            </h4>
                                                            <span class="text-base block" ng-if="userPostDetail.UserDetails.Occupation">
                                                                <span>{{userPostDetail.UserDetails.Occupation | limitTo: 35}}</span>
                                                            </span>
                                                            <span class="text-base block" >
                                                                <span ng-if="userPostDetail.UserDetails.Locality.Name">{{userPostDetail.UserDetails.Locality.Name}}, {{userPostDetail.UserDetails.Locality.WName}} (Ward {{userPostDetail.UserDetails.Locality.WNumber}})</span>
                                                            </span>
                                                            <ul class="user-info-list collapse" id="userInfoToggle">
                                                                <li class="row">
                                                                    <div class="col-xs-6">
                                                                        <label class="label-text">Posts <span class="text" ng-bind="': ' + userPostDetail.UserDetails.PostCount"></span></label>
                                                                    </div>
                                                                    <div class="col-xs-6">
                                                                        <label class="label-text">Responses <span class="text" ng-bind="': ' + userPostDetail.UserDetails.CommnetCount"></span></label>
                                                                    </div>
                                                                </li>
                                                                <li class="row text-right" ng-if="userPostDetail.UserDetails.MemberSince">
                                                                    <div class="col-xs-12"><small ng-bind="createDateObj(userPostDetail.UserDetails.MemberSince) | date : ' \'Member Since : \' dd \'-\' MMM yyyy'">Member Since : 02-Aug 2006</small></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <a class="ficon-arrow-down feed-expand collapsed" data-toggle="collapse" href="#userInfoToggle"></a>
                                        </div>
                                        <div ng-if="(userPostDetail.UserDetails)" class="panel-body custom-scroll scroll-sms" >
                                            <div ng-if="userPostDetail.ActivityVisibility" class="form-group no-bordered">
                                                <label class="control-label bolder">POST VISIBILITY</label>
                                                <!-- <span class="action pull-right">
                                                    <a class="ficon-edit mrgn-l-20" ng-click="setCurrentWardList();" uib-tooltip="Edit Visibility" tooltip-append-to-body="true" data-toggle="modal"></a>
                                                </span> -->
                                                <ul class="tags-list clearfix">
                                                    <li ng-repeat="visibility in userPostDetail.ActivityVisibility">
                                                        <span>{{visibility.WID == 1 ? 'All Ward' : 'Ward '+visibility.WNumber}}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div ng-if="userPostDetail.ActivityTags.Question" class="form-group no-bordered">
                                                <label class="checkbox">
                                                    <input ng-checked="userPostDetail.ActivityTags.Question.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Question, popupActivityDataOri.activity.ActivityID, 0, 'que', popupActivityDataOri.activity.ActivityGUID)" id="que_{{popupActivityDataOri.activity.ActivityID}}" value="{{userPostDetail.ActivityTags.Question.TagID}}" type="checkbox" class="check-content-filter">
                                                    <span class="label bold">Question</span>
                                                </label>
                                                <div class="input-icon" ng-if="userPostDetail.ActivityTags.Question.IsExist == 2">
                                                    <i class="ficon-price-tag"></i>
                                                    <tags-input
                                                        ng-model="userPostDetail.ActivityTags.Question.CCategory"
                                                        display-property="Name"
                                                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Question, popupActivityDataOri.activity.ActivityID)"
                                                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Question, popupActivityDataOri.activity.ActivityID)"
                                                        placeholder="Add question category"
                                                        readonly="readonly"
                                                        replace-spaces-with-dashes="false"
                                                        add-from-autocomplete-only="true"
                                                        template="qtag">
                                                        <auto-complete source="loadTagCategories($query, popupActivityDataOri.activity.ActivityID, 'que')" load-on-focus="true" min-length="0"></auto-complete>
                                                    </tags-input>
                                                    <script type="text/ng-template" id="qtag">
                                                        <div class="tag-template added-by-admin">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                </div>
                                            </div>
                                            <div ng-if="userPostDetail.ActivityTags.Custom" class="form-group no-bordered">
                                                <label class="checkbox">
                                                    <input ng-checked="userPostDetail.ActivityTags.Custom.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Custom, popupActivityDataOri.activity.ActivityID, 0, 'cla', popupActivityDataOri.activity.ActivityGUID)" id="cla_{{popupActivityDataOri.activity.ActivityID}}" value="{{userPostDetail.ActivityTags.Custom.TagID}}" type="checkbox" class="check-content-filter">
                                                    <span class="label bold">Classified</span>
                                                </label>
                                                <div class="input-icon" ng-if="userPostDetail.ActivityTags.Custom.IsExist == 1">
                                                    <i class="ficon-price-tag"></i>
                                                    <tags-input
                                                        ng-model="userPostDetail.ActivityTags.Custom.CCategory"
                                                        display-property="Name"
                                                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Custom, popupActivityDataOri.activity.ActivityID)"
                                                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Custom, popupActivityDataOri.activity.ActivityID)"
                                                        placeholder="Add classified category"
                                                        readonly="readonly"
                                                        replace-spaces-with-dashes="false" 
                                                        add-from-autocomplete-only="true"
                                                        template="ctag">
                                                        <auto-complete source="loadTagCategories($query, popupActivityDataOri.activity.ActivityID, 'cla')" load-on-focus="true" min-length="0"></auto-complete>
                                                    </tags-input>
                                                    <script type="text/ng-template" id="ctag">
                                                        <div class="tag-template added-by-admin">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                </div>
                                            </div>
                                            <div ng-if="userPostDetail.ActivityTags" class="form-group no-bordered">
                                                <label class="control-label bolder">POST TAGS</label>
                                                <div class="input-icon tag-suggestions">
                                                    <i class="ficon-price-tag"></i>
                                                    <tags-input
                                                        ng-model="userPostDetail.ActivityTags.Normal"
                                                        display-property="Name"
                                                        on-tag-added="addMemberTags('ACTIVITY', $tag, popupActivityDataOri.activity.ActivityGUID, 0)"
                                                        on-tag-removed="removeMemberTags('ACTIVITY', $tag, popupActivityDataOri.activity.ActivityGUID, 0)"
                                                        placeholder="Add more tags"
                                                        replace-spaces-with-dashes="false" 
                                                        add-from-autocomplete-only="true"
                                                        template="tag1">
                                                        <auto-complete source="loadMemberTags($query, popupActivityDataOri.activity.ActivityID, 0, 'ACTIVITY', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
                                                    </tags-input>
                                                    <script type="text/ng-template" id="tag1">
                                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                </div>
                                            </div>
                                            <div class="form-group no-bordered">
                                                <label class="control-label bolder">USER TAGS</label>
                                                <div class="input-icon tag-suggestions">
                                                    <i class="ficon-price-tag"></i>
                                                    <tags-input
                                                        ng-model="userPostDetail.UserTags.User_ReaderTag"
                                                        display-property="Name"
                                                        on-tag-added="addMemberTags('USER', $tag, popupActivityDataOri.subject_user.UserGUID, 3)"
                                                        on-tag-removed="removeMemberTags('USER', $tag, popupActivityDataOri.subject_user.UserGUID, 3)"
                                                        placeholder="Add user type"
                                                        replace-spaces-with-dashes="false" 
                                                        add-from-autocomplete-only="true"
                                                        template="tag7">
                                                        <auto-complete source="loadMemberTags($query, popupActivityDataOri.subject_user.UserID, 3, 'USER', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
                                                    </tags-input>
                                                    <script type="text/ng-template" id="tag7">
                                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<input type="hidden" id="LoggedInUserGUID" value="<?php echo $this->session->userdata('AdminLoginSessionKey') ?>" />
