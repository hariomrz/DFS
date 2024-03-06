<!--<aside class="col-md-9 col-sm-8 ">-->
    <div class="poll-filter-view" ng-cloak="">
        <ul class="filter-tags">
            <li data-ng-class="!poll_search_term?'hide':''"><span ng-bind="poll_search_term"></span> <i class="icon-n-close" data-ng-click="remove_filter('15','poll_search_term');">&nbsp;</i></li>
            <li data-ng-class="!poll_date_search_term?'hide':''"><span ng-bind="poll_date_search_term"></span><i class="icon-n-close" data-ng-click="remove_filter('14','poll_date_search_term');">&nbsp;</i></li>
            <li data-ng-class="!filter_anonymous?'hide':''">Anonymous<i class="icon-n-close" data-ng-click="remove_filter('13','filter_anonymous');">&nbsp;</i></li>
            <li data-ng-class="!filter_expired?'hide':''">Expired<i class="icon-n-close" data-ng-click="remove_filter('11','filter_expired');">&nbsp;</i></li>
            <li data-ng-class="!filter_archive?'hide':''">Archive<i class="icon-n-close" data-ng-click="remove_filter('1','filter_archive');">&nbsp;</i></li>
        </ul>
    </div>
  
<?php 
  //} 
  if(!isset($AllActivity)) {
  switch ($ModuleID) {
    case 3 :
      $EntityType = 'User';
      break;
    case 1 :
      $EntityType = 'Group';
      break;
    case 14 :
      $EntityType = 'Event';
      break;
    case 19 :
      $EntityType = 'Activity';
      break;
    case 21 :
      $EntityType = 'Media';
      break;
    case 13 :
      $EntityType = 'Album';
      break;
    case 18 :
      $EntityType = 'Page';
      break;
    default:
      $EntityType = '';
      break;
   } 
?>
    <input type="hidden" ng-controller="logCtrl" ng-init="viewCount('<?php echo $EntityType ?>','<?php echo $ModuleEntityGUID ?>')" />
<?php 
  } 
?>
  <input type="hidden" id="FeedSortBy" value="2" />
  <input type="hidden" id="IsMediaExists" value="2" />
  <input type="hidden" id="PostOwner" value="" />
  <input type="hidden" id="ActivityFilterType" value="0" />
  <input type="hidden" id="AsOwner" value="0" />
<?php if($IsGroup==1 && $IsAdmin) { ?>
    <section class="news-feed" ng-if="wallReqCnt>1 || tr>0" ng-cloak>
<?php } else { ?>
    <section class="news-feed" ng-cloak>
<?php } ?>

        
          <div ng-cloak ng-show="IsReminder==1 && ReminderFilter==1" class="reminder-filter-view">
              <ul class="filter-tags">
                  <li ng-repeat="RFD in ReminderFilterDate">
                    <span ng-bind="getReminderDateFormat(RFD)"></span> 
                    <i ng-click="clearReminderFilter(RFD);" class="icon-n-close">&nbsp;</i>
                  </li> 
              </ul>
          </div>
          <section class="news-feed" ng-cloak ng-controller="NewsFeedCtrl" id="NewsFeedCtrl" 
                infinite-scroll="GetwallPost()" 
                infinite-scroll-distance="2" 
                infinite-scroll-use-document-bottom="true" 
                infinite-scroll-disabled="is_busy"
                   >  
            <!-- <activity-item repeat-done="wallRepeatDone();" loggedinname="{{LoggedInName}}" loggedinprofilepicture="{{LoggedInProfilePicture}}" ng-repeat="postItem in activityData track by $index" data="postItem" 
            index="{{$index}}" ng-if="(postItem.ActivityType!='AlbumAdded' && postItem.ActivityType!='AlbumUpdated') || postItem.Album.length>0" ng-cloak> </activity-item> -->
            <div                 
                ng-repeat="data in activityData track by $index" 
                repeat-done="wallRepeatDone();" 
                ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index;" 
                viewport-watch
            >
                
                <div class="inner-wall-post poll-feed-listing" ng-include="getTemplateUrl(data)" ></div>
            </div>    
            <?php $this->load->view('include/feed-loader'); ?>

        <div ng-cloak ng-if="tr==0 || (trr==0 && IsReminder==1)" class="panel panel-info">
          <div class="panel-body nodata-panel">
            <div class="nodata-text">
              <span class="nodata-media">
                <img ng-src="{{AssetBaseUrl}}/img/empty-img/empty-no-newsfeed.png" >
              </span>
              <h5>Create a poll! </h5>
              <p class="text-off">Got something you want an opinion on? Go ahead and create a poll!</p>
            </div>
          </div>
        </div>
<!--    <div  class="wallloader">
       <div class="panel-loading"> <span class="loading"></span> </div>
    </div>-->
  </section>
<?php 
    if($IsGroup=='1'){
      $this->load->view('groups/group_invite');
    } 
  ?>
<!--</aside>-->
<div ng-controller="PollCtrl"  id="PollCtrl2">

<div role="dialog" class="modal fade" id="votesModal" aria-labelledby="myModalLabel" aria-hidden="false" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button data-dismiss="modal" class="close" type="button">
            <span aria-hidden="true"><i class="icon-close"></i></span>
        </button>
        <h4 class="modal-title" id="myModalLabel"><span ng-bind="totalVotes"></span> <span data-ng-if="totalVotes==1">Person</span><span data-ng-if="totalVotes>1">People</span> voted</h4>
      </div>
      <div class="modal-body padd-l-r-0 non-footer">
        <div class="designer-scroll mCustomScrollbar">
            <ul class="list-items-group">
                <li ng-repeat="list in VotesDetails track by $index" class="list-group-item">
                    <div class="list-items-sm">
                        <div class="list-inner">                            
                            <figure>
                                <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                    <img class="img-circle " ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}">
                                </a>
                                <a ng-if="list.ModuleID=='18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                    <img class="img-circle " ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}">
                                </a>
                            </figure>
                            
                            
                            <div class="list-item-body">
                                <h4 class="list-heading-xs">                                                                        
                                    <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                                    <a ng-if="list.ModuleID=='18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>                                    
                                </h4>
                                                                
                                    <small class="location" data-ng-if="list.Location != '' && list.Location.Location != ''" data-ng-bind="list.Location.Location"></small>
                                
                            </div>
                            </div>
                    </div>
                </li>
                
                <li class="load-more" data-ng-show="IsVotesLoadMore == '1'">
                    <i class="loading"></i>
                </li>   
                
            </ul>
            
            <div class="enscroll-track vertical-track">
                <a  href="" class="vertical-handle">
                    <div class="top"></div>
                    <div class="bottom"></div>
                </a>
             </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- posted For Modal -->
<div class="modal fade postedForModal" id="postedForModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel3">Posted for</h4>
            </div>
            <div class="modal-body padd-l-r-0 non-footer">
                <div class="designer-scroll mCustomScrollbar">
                    <ul class="list-group awaitinglist">
                        <li ng-repeat="list in postedForUser" class="list-group-item">
                            <figure class="media-left">
                                <a ng-if="list.ModuleID=='1'" entitytype="Group" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                    <img ng-if="list.ProfilePicture==''" class="img-circle mCS_img_loaded" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                                    <img ng-if="list.ProfilePicture!==''" class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}" />
                                </a>
                                <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                    <img ng-if="list.ProfilePicture==''" class="img-circle mCS_img_loaded" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                                    <img ng-if="list.ProfilePicture!==''" class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}" />
                                </a>
                            </figure>
                           <div class="description">

                              <a ng-if="list.ModuleID=='1'" entitytype="Group" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.FirstName"></a>
                              <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.FirstName+' '+list.LastName"></a>

                           <p data-ng-if="list.ProfileTypeName != ''" data-ng-bind="list.ProfileTypeName"></p>
                           <p data-ng-if="list.Location.Location != ''" class="gray-text" data-ng-bind="list.Location.Location"></p>
                           </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

  <!-- Invited Modals Starts -->
  <div role="dialog" class="modal fade" id="votesModalInvited" aria-labelledby="myModalLabel" aria-hidden="false" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button data-dismiss="modal" class="close" type="button">
              <span aria-hidden="true"><i class="icon-close"></i></span>
          </button>
          <h4 class="modal-title" id="myModalLabel"><span ng-bind="totalVotes"></span> <span data-ng-if="totalVotes==1">Person</span><span data-ng-if="totalVotes>1">People</span> voted</h4>
        </div>

        <div class="modal-body padd-l-r-0 non-footer">
          <div class="designer-scroll mCustomScrollbar">
               <ul class="list-group awaitinglist list-group-horizontal scrollbox scrollbox-md-height" tabindex="0">
                    <li ng-repeat="list in VotesDetails track by $index" class="list-group-item ">
                          <figure class="media-left">
                              <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                  <img class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}">
                              </a>
                              <a ng-if="list.ModuleID=='18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                  <img class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{list.ProfilePicture}}">
                              </a>
                          </figure>
                         <div class="description">
                            <a ng-if="list.ModuleID=='3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                            <a ng-if="list.ModuleID=='18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                         <p data-ng-if="list.ProfileTypeName != ''" data-ng-bind="list.ProfileTypeName"></p>
                         <p data-ng-if="list.Location.Location != ''" class="gray-text" data-ng-bind="list.Location.Location"></p>
                         </div>
                    </li>

                    <li class="load-more" data-ng-show="IsVotesLoadMore == '1'">
                      <i class="loading"></i>
                    </li>    
                 </ul>
                 <div class="enscroll-track vertical-track">
                          <a  href="" class="vertical-handle">
                              <div class="top"></div>
                              <div class="bottom"></div>
                          </a>
                  </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Invited Modals Ends -->


    <div ng-include="like_details_modal_tmplt"></div>



<input type="hidden" id="ActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : '' ; ?>" />
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
                    <div ng-if="singleActivity.ShareDetails && singleActivity.PollData.length=='0'" class="col-sm-6 col-md-6 col-xs-12 social">
                        <!-- FacebookShare(data.ShareDetails.Link,data.ShareDetails.Summary,'V Social 6',data.ShareDetails.Image); -->
                        <span data-dismiss="modal" ng-click="$emit('FacebookShareEmit', singleActivity.ShareDetails.Link, singleActivity.ShareDetails.Summary, singleActivity.ShareDetails.Summary, singleActivity.ShareDetails.Image);">
                            <span style="text-decoration:none;color:#000000;display:inline-block;cursor:pointer;" class="stButton"><span class="stLarge" style="background-image: url('<?php echo ASSET_BASE_URL ?>img/facebook_32.png');"></span></span>
                        </span>
                        <script type="text/javascript">
                       // if (LoginSessionKey !== '') {
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
                       // }
                        </script>
                        <!--  st_title="{{strip(singleActivity.ShareDetails.Summary)}}" -->
                        <!--  <span data-dismiss="modal" class='st_twitter_large' st_image="{{singleActivity.ShareDetails.Image}}" st_title="{{strip(singleActivity.ShareDetails.Summary)}}" st_summary="{{singleActivity.ShareDetails.Summary}}" st_via="vinfotech" st_url="{{singleActivity.ShareDetails.Link}}" displayText='Tweet'></span> -->
                        <span>
 <a href="https://twitter.com/intent/tweet?text={{strip(singleActivity.ShareDetails.Summary)}}&url={{singleActivity.ShareDetails.Link}}&via=vinfotech"
   onclick="popupCenter(this.href,'Twitter', 500, 300);
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
                                    <img ng-if="singleActivity.ActivityType!='ProfilePicUpdated' && singleActivity.ActivityType!='ProfileCoverUpdated' && singleActivity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Image'"   ng-src="{{singleActivity.ImageServerPath+'upload/album/750x500/'+media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType!='ProfilePicUpdated' && singleActivity.ActivityType!='ProfileCoverUpdated' && singleActivity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Image'"   ng-src="{{singleActivity.ImageServerPath+'upload/wall/750x500/'+media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType!='ProfilePicUpdated' && singleActivity.ActivityType!='ProfileCoverUpdated' && singleActivity.Album[0].AlbumName!=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'"   ng-src="{{singleActivity.ImageServerPath+'upload/album/750x500/'+  media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                    <img ng-if="singleActivity.ActivityType!='ProfilePicUpdated' && singleActivity.ActivityType!='ProfileCoverUpdated' && singleActivity.Album[0].AlbumName=='Wall Media' && media.MediaType=='Video' && media.ConversionStatus=='Finished'"   ng-src="{{singleActivity.ImageServerPath+'upload/wall/750x500/'+ media.ImageName.substr(0, media.ImageName.lastIndexOf('.')) + '.jpg'}}" />
                                    <img ng-if="singleActivity.ActivityType=='ProfilePicUpdated'" ng-src="{{singleActivity.ImageServerPath+'upload/profile/220x220/'+media.ImageName}}" />
                                    <img ng-if="singleActivity.ActivityType=='ProfileCoverUpdated'" ng-src="{{singleActivity.ImageServerPath+'upload/profilebanner/1200x300/'+media.ImageName}}" />
                                    <!-- Media Ends -->
                                    <i class="icon-n-video-big" ng-if="media.MediaType=='Video' && media.ConversionStatus=='Finished'"></i>
                                    <div ng-if="$last && singleActivity.Album[0].TotalMedia > 4 && singleActivity.Album[0].Media.length > 1" class="more-content"><span ng-bind="'+' + (singleActivity.Album[0].TotalMedia - 3)"></span></div>
                                </a>
                                <div class="post-video" ng-if="media.MediaType=='Video' && media.ConversionStatus=='Pending'">
                                    <div class="wall-video pending-rating-video">
                                        <i class="icon-video-c"></i>
                                    </div>
                                </div>
                            </figure>
                        </div>
                        <div ng-if="singleActivity.PollData.length == 0" class="share-content">
                            <div class="share-inr-space tagging">
                                <a href="javascript:void(0);" ng-if="singleActivity.PostType!=='7'" ng-bind="singleActivity.UserName"></a>
                                <a href="javascript:void(0);" ng-if="singleActivity.PostType=='7'" ng-bind="singleActivity.EntityName"></a>
                                <p ng-bind-html="textToLink(singleActivity.PostContent)"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right wall-btns">
                    <!-- Privacy Buttons -->
                    <button id="shareComment" class="own-wall-settings btn btn-default btn-icon btn-onoff on" type="button"> <i class="ficon-comment f-lg"></i> <span>On</span> </button>
                    <div class="btn-group custom-icondrop own-wall-settings own-wall-privacy">
                        <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle drop-icon" type="button"> 
                            <i class="ficon-friends"></i> 
                            <span class="caret"></span> 
                        </button>
                        <ul role="menu" class="dropdown-menu pull-left dropdown-withicons">
                            <li><a onClick="$('#shareVisibleFor').val(1);" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-globe"></i></span>Everyone</a></li>
                            <!-- <li><a onClick="$('#shareVisibleFor').val(2);" href="javascript:void(0);"><span class="mark-icon"><i class="icon-follwers"></i></span>Friends of Friend</a></li> -->
                            <li><a onClick="$('#shareVisibleFor').val(3);" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-friends"></i></span>Friends</a></li>
                            <li><a onClick="$('#shareVisibleFor').val(4);" href="javascript:void(0);"><span class="mark-icon"><i class="ficon-user"></i></span>Only Me</a></li>
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
<input type="hidden" id="shareCommentSettings" value="0" />
<input type="hidden" id="ShareModuleEntityGUID" value="" />
<input type="hidden" id="ShareEntityUserGUID" value="" />
<!-- Share Popup Code Ends -->

<?php $this->load->view('include/invite-modal-popup') ?>
</div>