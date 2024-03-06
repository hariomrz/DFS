<div class="wrapper grow-network" ng-controller="InviteFriendCtrl" id="InviteFriendCtrl" ng-cloak>
  <div class="custom-modal">
    <div class="row offset-title"> 
      <div class="col-xs-12">
    		<h4 class="label-title secondary-title" ng-bind="::lang.grow_your_network"><a href="<?php echo site_url('dashboard');?>" class="name pull-right" ng-bind="::lang.gn_skip_to_newsfeed"></a></h4>
      </div>  
    </div>
      
    <?php if(!$this->settings_model->isDisabled(10)): // Check if friend module is enabled ?>
    <div class="find-friends fadeInDown">
      <div class="row">
        <div class="col-sm-4 hidden-xs">
          <div class="circle-nav-block">
             <div class="circle-nav-img"> <img src="<?php echo ASSET_BASE_URL ?>img/network-icon.svg" > </div>
          </div>  
        </div>    
        <div class="col-sm-8 find-your-friends">
            <h3 ng-bind="::lang.find_your_friends"></h3>
            <p ng-bind="::lang.connect_social_network_find_friends"></p>    
              <ul class="social-btn">
                <li>
                  <a  ng-click="grow_network('facebook')" class="fb">
                    <span class="icon">
                      <i class="ficon-facebook"></i>
                    </span>
                  </a> 
                </li>
                <li><a  ng-click="grow_network('google')" class="gp">
                    <span class="icon">
                        <i class="ficon-googleplus"></i>
                    </span>
                  </a>
                </li>
                <li>
                  <a  ng-click="grow_network('outlook')" class="olk">
                    <span class="icon">
                      <i class="ficon-outlook"></i>
                    </span>
                </a> 
              </li>
              </ul>
        </div>    
      </div>  
   </div>
    <?php endif; ?>
  
<div class="find-friends-wrapper">
   <!-- Google Starts -->
    <div class="row block-title" ng-cloak ng-if="social_login && existing_users.length>0">
     <div class="col-sm-6"><h6> <i ng-class="social_icon"></i> {{::lang.gn_friends_already_here_caps}} <i ng-click="removeSocialLogin()" class="icon-remove"></i></h6></div>
     <div class="col-sm-6"> 
        <div class="search-cmn pull-right">
          <div class="input-group global-search" ng-if="social_login && existing_users.length>0">
              <input type="text" id="searchText" ng-keyup="search_users()" name="" placeholder="Search" class="form-control">
                <div class="input-group-btn">
                  <button ng-click="clear_search();" type="button" class="btn-search"><i class="icon-search-gray"></i></button>
                </div>
              </div>
            </div>  
        </div>
    </div>   
   
   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && existing_users.length>0"> 

       <div class="row">
          <ul class="profiles-listing">
              <li ng-repeat="user in filtered = (existing_users | filter: searchText)" repeat-done="repeatDoneBCard();" ng-cloak class="col-sm-3" repeat-done="initGrid();">
                <div class="listing-content">
                    <div class="listing-desc">
                        <figure>
                          <a href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="loadbusinesscard" entityguid="{{user.UserGUID}}" entitytype="user">
                            <img   err-name="{{user.FirstName+' '+user.LastName}}" ng-if="(user.ProfilePicture != 'user_default.jpg')" class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}""> 

                            <span ng-if="user.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(user.FirstName+' '+user.LastName)"></span></span>
                            
                          </a>
                        </figure>
                        <a href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="name ellipsis" ng-bind="user.FirstName+' '+user.LastName" class="loadbusinesscard" entityguid="{{user.UserGUID}}" entitytype="user"></a>
                        <span class="location ellipsis" ng-if="user.CityName!==null && user.CountryName!==null" ng-bind="user.CityName+', '+user.CountryName"></span>
                        <span class="location ellipsis" ng-if="user.CityName!==null && user.CountryName==null" ng-bind="user.CityName"></span>
                        <span class="location ellipsis" ng-if="user.CityName==null && user.CountryName!==null" ng-bind="user.CountryName"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends>1" ng-bind="user.TotalFriends+' friends'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==1" ng-bind="user.TotalFriends+' friend'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==0">&nbsp;</span>
                        <div class="dropdown-btn">
                          <div ng-cloak ng-if="SettingsData.m10=='1'" class="btn-group">
                              
                              <button ng-if="user.SentRequest==0" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> <span class="text" ng-bind="::lang.gn_add_friend"></span> <i class="caret"></i></button>
                              <button ng-if="user.SentRequest==1" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> <span class="text" ng-bind="::lang.gn_request_sent"></span> <i class="caret"></i></button>
                              <ul class="dropdown-menu" role="menu">
                                <li>
                                  <a ng-if="user.SentRequest==0" ng-click="sendRequest(user.UserGUID,'existing')" ng-bind="::lang.gn_add_friend"></a>
                                  <a ng-if="user.SentRequest==1" ng-click="rejectRequest(user.UserGUID,'existing')" ng-bind="::lang.gn_cancel_request"></a>
                                </li>
                                <li>
                                  <a ng-if="user.IsFollowing==0" ng-click="follow(user.UserGUID)" ng-bind="::lang.gn_follow"></a>
                                  <a ng-if="user.IsFollowing==1" ng-click="follow(user.UserGUID)" ng-bind="::lang.gn_unfollow"></a>
                                </li> 
                              </ul>
                          </div>
                          <div ng-cloak ng-if="SettingsData.m10=='0'" class="btn-group">
                              
                              <button ng-if="user.IsFollowing==0" ng-cloak ng-click="follow(user.UserGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="::lang.gn_follow"></span></button>
                              <button ng-if="user.IsFollowing==1" ng-cloak ng-click="follow(user.UserGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="::lang.gn_unfollow"></span></button>
                          </div>
                        </div>
                    </div> 
                    <div ng-click="get_new_user(user.UserGUID);" class="removeBlock"><i class="ficon-cross"></i></div>
                 </div>
              </li>
          </ul>
      </div>  
   </div>

    <div class="other-friends" ng-cloak ng-if="social_login && (new_users.length>0 || source_type=='facebook' || source_type=='google')">
        <span ng-if="source_type=='google'" ng-bind="::lang.gn_other_google_friends"></span>
        <span ng-if="source_type=='facebook'" ng-bind="::lang.gn_other_fb_friends"></span>
        <span ng-if="source_type=='twitter'" ng-bind="::lang.gn_other_twitter_friends"></span>

    </div>

   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && source_type=='facebook'">
    <!-- <a href="javascript:void(0);" ng-click="invite_friend('facebook')">Invite Friends</a> -->
    <div class="other-invite">
        <a href="javascript:void(0);" ng-click="invite_friend('facebook')" class="btn-social btnfb"><i class="icon-fbs">&nbsp;</i> {{::lang.gn_invite_them_now}}</a>
    </div>
   </div>
   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && source_type=='google'">
    <!-- <button id="sharePostGoogle" ng-init="invite_friend('google')">Invite Friends</button> -->
    <div class="other-invite">
        <a id="sharePostGoogle" href="javascript:void(0);" ng-init="invite_friend('google')" class="btn-social btngoogle"><i class="icon-fbs">&nbsp;</i> {{::lang.gn_invite_them_now}}</a>
    </div>
   </div>

   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && new_users.length>0 && source_type=='twitter'">            
       <div class="row">
           <ul class="profiles-listing">
              <li class="col-sm-3" ng-repeat="user in new_users | filter:searchText">
                <div class="listing-content">
                    <div class="listing-desc">
                       <figure><a ng-href="http://twitter.com/{{user.screen_name}}"><img width="148px;" ng-src="{{user.profile_image_url}}" ></a></figure>
                        <a ng-href="http://twitter.com/{{user.screen_name}}" class="name ellipsis" ng-bind="user.name"></a> 
                        <div class="dropdown-btn">
                          <div class="btn-group">
                              <button ng-if="user.IsInvited=='0'" type="button" class="btn btn-default" ng-click="invite_friend('twitter',user.id)"> <span class="text" ng-bind="::lang.gn_invite"></span> </button> 
                              <span ng-if="user.IsInvited=='1'" class="text" ng-bind="::lang.gn_invited"></span>
                            </div>
                        </div>
                    </div> 
                 </div>
              </li>
            </ul>  
       </div>
   </div>
   
   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && new_users.length>0 && source_type=='yahoo'">            
       <div class="row">
           <ul class="profiles-listing">
              <li class="col-sm-3" ng-repeat="user in new_users | filter:searchText">
                <div class="listing-content">
                    <div class="listing-desc">
                        <figure><a><img width="148px;" ng-src="{{user.profile_image_url}}" ></a></figure>
                        <a  class="name ellipsis" ng-bind="user.name"></a> 
                        <div class="dropdown-btn">
                          <div class="btn-group">
                              <button ng-if="user.IsInvited=='0'" type="button" class="btn btn-default" ng-click="invite_friend_email(user,5)"> <span class="text" ng-bind="::lang.gn_invite"></span> </button> 
                              <span ng-if="user.IsInvited=='1'" class="text" ng-bind="::lang.gn_invited"></span>
                            </div>
                        </div>
                    </div> 
                 </div>
              </li>
            </ul>  
       </div>
   </div>
   
   <div class="profiles-listing-block fadeInDown" ng-cloak ng-if="social_login && new_users.length>0 && source_type=='outlook'">            
       <div class="row">
           <ul class="profiles-listing">
              <li class="col-sm-3" ng-repeat="user in new_users | filter:searchText">
                <div class="listing-content">
                    <div class="listing-desc">
                       <figure><a><img width="148px;" ng-src="{{user.profile_image_url}}" ></a></figure>
                        <a  class="name ellipsis" ng-bind="user.name"></a> 
                        <div class="dropdown-btn">
                          <div class="btn-group">
                              <button ng-if="user.IsInvited=='0'" type="button" class="btn btn-default" ng-click="invite_friend_email(user,6)"> <span class="text" ng-bind="::lang.gn_invite"></span> </button> 
                              <span ng-if="user.IsInvited=='1'" class="text" ng-bind="::lang.gn_invited"></span>
                            </div>
                        </div>
                    </div> 
                 </div>
              </li>
            </ul>  
       </div>
   </div>
   
   <div class="loader" ng-if="(!social_login) ? (is_busy==1 && users_list.length==0 && groups_list.length==0) : (is_busy==1 && new_users.length==0 && existing_users.length==0 && source_type!=='facebook' && source_type!=='google') ;" style="width:60px;height:60px;transform:translate(-50%,-50%)"></div>
   
   <!-- Google Ends -->


   <!-- Native Starts -->
   <div class="row block-title" ng-cloak ng-if="users_list.length>0">
       <div class="col-sm-6">
            <?php if(!$this->settings_model->isDisabled(10)): // Check if friend module is enabled ?>
                <h6 ng-bind="lang.gn_people_you_may_know"></h6>
            <?php else: ?>
                <h6 ng-bind="lang.gn_people_you_may_follow"></h6>
            <?php endif; ?>
       </div> 
  </div>
   
   <?php if(!$this->settings_model->isDisabled(10)): // Check if friend module is enabled ?>
   
   <div class="profiles-listing-block fadeInDown">
       <div class="row">
          <ul class="profiles-listing people-listing gridView" ng-init="grow_user_network();">
              <li ng-repeat="user in users_list" repeat-done="repeatDoneBCard();" ng-cloak class="col-sm-3 fadeInDown" repeat-done="initGrid();" ng-click="get_user_details(user.UserGUID)">
                <div class="listing-content">
                    <div class="listing-desc">
                        <figure>
                          <a href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="loadbusinesscard" entityguid="{{user.UserGUID}}" entitytype="user">
                            
                            <img err-name="{{user.FirstName+' '+user.LastName}}" ng-if="user.ProfilePicture!=='user_default.jpg'" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user.ProfilePicture}}" >
                            <span ng-if="user.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(user.FirstName+' '+user.LastName)"></span></span>
                          </a>
                        </figure>
                        <div class="inner-content">
                        <a href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="name ellipsis loadbusinesscard" ng-bind="user.FirstName+' '+user.LastName" entityguid="{{user.UserGUID}}" entitytype="user"></a>
                        <span class="location ellipsis" ng-if="user.CityName!==null && user.CountryName!==null" ng-bind="user.CityName+', '+user.CountryName"></span>
                        <span class="location ellipsis" ng-if="user.CityName!==null && user.CountryName==null" ng-bind="user.CityName"></span>
                        <span class="location ellipsis" ng-if="user.CityName==null && user.CountryName!==null" ng-bind="user.CountryName"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends>1" ng-bind="user.TotalFriends+' friends'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==1" ng-bind="user.TotalFriends+' friend'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==0">&nbsp;</span>
                        </div>
                        <div class="dropdown-btn">
                          <div ng-cloak ng-if="SettingsData.m10=='1'" class="btn-group">
                              
                              <button ng-if="user.SentRequest==0" ng-click="sendRequest(user.UserGUID)" type="button" class="btn btn-default">
                                <span class="text" ng-bind="lang.gn_add_friend"></span>
                              </button>

                              <button ng-if="user.SentRequest==1" ng-click="rejectRequest(user.UserGUID)" type="button" class="btn btn-default">
                                <span class="text" ng-bind="lang.gn_cancel_request"></span>
                              </button>

                            </div>
                            <div ng-cloak ng-if="SettingsData.m10=='0'" class="btn-group">
                                <button ng-if="user.IsFollowing==0" ng-cloak ng-click="follow(user.UserGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="lang.gn_follow"></span></button>
                                <button ng-if="user.IsFollowing==1" ng-cloak ng-click="follow(user.UserGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="lang.gn_unfollow"></span></button>
                            </div>
                        </div>
                    </div> 
                    <div ng-click="get_new_user(user.UserGUID);" class="removeBlock"><i class="ficon-cross"></i></div>
                 </div>
              </li>
          </ul>
      </div>  
   </div> 
   
   <?php else: ?>
   
    <div class="profiles-listing-block fadeInDown">
                <div class="row">
                    <ul class="profiles-listing people-listing gridView" ng-init="grow_user_network_follow();">
                        <li ng-repeat="user in users_list_follow" repeat-done="repeatDoneBCard();" ng-cloak class="col-sm-3 fadeInDown" repeat-done="initGrid();" ng-click="get_user_details(user.UserGUID)">
                            <div class="listing-content">
                                <div class="listing-desc">
                                    <figure>
                                        <a target="_self" href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="loadbusinesscard" entityguid="{{user.UserGUID}}" entitytype="user">

                                            <img err-name="{{user.FirstName + ' ' + user.LastName}}" ng-if="user.ProfilePicture !== 'user_default.jpg'" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user.ProfilePicture}}" >
                                            <span ng-if="user.ProfilePicture == 'user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(user.FirstName + ' ' + user.LastName)"></span></span>
                                        </a>
                                    </figure>
                                    <div class="inner-content">
                                        <a target="_self" href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="name ellipsis loadbusinesscard" ng-bind="user.FirstName + ' ' + user.LastName" entityguid="{{user.UserGUID}}" entitytype="user"></a>
                                        <span class="location ellipsis" ng-if="user.CityName !== null && user.CountryName !== null" ng-bind="user.CityName + ', ' + user.CountryName"></span>
                                        <span class="location ellipsis" ng-if="user.CityName !== null && user.CountryName == null" ng-bind="user.CityName"></span>
                                        <span class="location ellipsis" ng-if="user.CityName == null && user.CountryName !== null" ng-bind="user.CountryName"></span>
                                        <span class="total-friends ellipsis" ng-if="user.TotalFriends > 1" ng-bind="user.TotalFriends + ' friends'"></span>
                                        <span class="total-friends ellipsis" ng-if="user.TotalFriends == 1" ng-bind="user.TotalFriends + ' friend'"></span>
                                        <span class="total-friends ellipsis" ng-if="user.TotalFriends == 0">&nbsp;</span>
                                    </div>
                                    <div class="dropdown-btn">
                                        <div class="btn-group">

                                            <button ng-if="user.SentRequest" ng-click="follow(user.UserGUID, user, 0)" type="button" class="btn btn-default">
                                                <span class="text" ng-bind="lang.gn_unfollow"></span>
                                            </button>

                                            <button ng-if="!user.SentRequest" ng-click="follow(user.UserGUID, user, 1)" type="button" class="btn btn-default">
                                                <span class="text" ng-bind="lang.gn_follow"></span>
                                            </button>

                                        </div>
                                    </div>
                                </div> 
                                <div ng-click="get_new_user(user.UserGUID);" class="removeBlock"><i class="ficon-cross"></i></div>
                            </div>
                        </li>
                    </ul>
                </div>  
            </div>   
   
    <?php endif; ?>
   
   <!-- Popular Profiles -->
   <div class="row block-title" ng-cloak ng-if="popular_user.length>0">
    <div class="col-sm-6"><h6 ng-bind="lang.popular_profiles"></h6></div> 
  </div>
   <div class="profiles-listing-block fadeInDown">
       <div class="row">
          <ul class="profiles-listing gridView popular-listing" ng-init="get_popular_profile();">
              <li ng-repeat="user in popular_user" repeat-done="repeatDoneBCard();" ng-cloak class="col-sm-3 fadeInDown" repeat-done="initGrid();" ng-click="get_user_details(user.UserGUID)">
                <div class="listing-content">
                    <div class="listing-desc">
                        <figure>
                          <a href="<?php echo site_url() ?>/{{user.ProfileURL}}" class="loadbusinesscard" entityguid="{{user.UserGUID}}" entitytype="user">
                            <img err-name="{{user.FirstName+' '+user.LastName}}" ng-if="user.ProfilePicture!=='user_default.jpg'" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user.ProfilePicture}}" >
                            <span ng-if="user.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(user.FirstName+' '+user.LastName)"></span></span>
                          </a>
                        </figure>
                        <div class="inner-content">
                        <a href="<?php echo site_url() ?>{{user.ProfileURL}}" class="name ellipsis loadbusinesscard" ng-bind="user.FirstName+' '+user.LastName" entityguid="{{user.ModuleEntityGUID}}" entitytype="{{user.EntityType}}"></a>
                        <span class="location ellipsis" ng-if="user.CityName!=='' && user.CountryName!==''" ng-bind="user.CityName+', '+user.CountryName"></span>
                        <span class="location ellipsis" ng-if="user.CityName!=='' && user.CountryName==''" ng-bind="user.CityName"></span>
                        <span class="location ellipsis" ng-if="user.CityName=='' && user.CountryName!==''" ng-bind="user.CountryName"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends>1" ng-bind="user.TotalFriends+' friends'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==1" ng-bind="user.TotalFriends+' friend'"></span>
                        <span class="total-friends ellipsis" ng-if="user.TotalFriends==0">&nbsp;</span>
                        </div>
                        <div class="dropdown-btn">
                          <div class="btn-group">
                              <button ng-if="user.SentRequest==0" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> <span class="text" ng-bind="lang.gn_add_friend"></span> <i class="caret"></i></button>
                              <button ng-if="user.IsFollowing==0" ng-click="follow_entity(user.ModuleID,user.ModuleEntityGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="lang.gn_follow"></span></button>
                              <button ng-if="user.IsFollowing==1" ng-click="follow_entity(user.ModuleID,user.ModuleEntityGUID)" type="button" class="btn btn-default"> <span class="text" ng-bind="lang.gn_unfollow"></span></button>
                            </div>
                        </div>
                    </div>
                 </div>
              </li>
          </ul>
      </div>  
   </div> 
   <!-- Popular Profiles -->

  

</div>

     
   </div>
   
   <div id="listDetail" style="display:none;">
    <div class="about-friends row">
      <div class="col-sm-12">
         <!-- <div class="about-friend-content" ng-style="get_profile_cover(user_details.ProfileCover);"> -->
         <div class="about-friend-content" ng-style="get_profile_cover('');">
           <div class="about-list-detail" ng-class="(user_details.Album.length>0) ? 'col-sm-6' : 'col-sm-12' ;">
              <h2 ng-bind="lang.gn_about_caps"></h2>
              <p ng-cloak ng-if="user_details.About!==''" ng-bind="user_details.About"></p>
              <p ng-cloak ng-if="user_details.About==''" ng-bind="lang.gn_no_desc_found"></p>
              <div class="overflow">
                  <ul class="followers-list">
                      <li ng-repeat="follower in user_details.Followers">
                        <a href="<?php echo site_url() ?>{{follower.ProfileLink}}">
                          <img width="32px" class="img-circle" src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{follower.ProfilePicture}}" >
                        </a>
                      </li>
                      <li ng-if="user_details.TotalFollowers>0"><a ng-click="get_followers_list(user_details.UserGUID)" href="javascript:void(0);"> +{{(user_details.TotalFollowers-3)}} {{::lang.gn_followers}} </a></li>
                  </ul>

                  <!-- <div class="liked-view">                    
                  </div> -->
              </div>
           </div>
           <div class="col-sm-6 about-list-detail" ng-if="user_details.Album.length>0">
              <h2 ng-bind="lang.gn_photos_caps"></h2>
              <div class="frined-photos">
                  <a href="javascript:void(0);" ng-repeat="media in user_details.Album[0].Media" data-placeholder="{{(user_details.Album[0].TotalMedia>7) ? '+'+user_details.Album[0].TotalMedia-7 : '' ;}}">
                    <img ng-click="$emit('showMediaPopupGlobalEmit',media.MediaGUID,'');" ng-if="user_details.Album[0].AlbumName!=='<?php echo DEFAULT_WALL_ALBUM ?>'"   src="<?php echo IMAGE_SERVER_PATH ?>upload/album/220x220/{{getAlbumCover(media.ImageName)}}" />
                    <img ng-click="$emit('showMediaPopupGlobalEmit',media.MediaGUID,'');" ng-if="user_details.Album[0].AlbumName=='<?php echo DEFAULT_WALL_ALBUM ?>'"   src="<?php echo IMAGE_SERVER_PATH ?>upload/wall/220x220/{{getAlbumCover(media.ImageName)}}" />
                  </a>
              </div>
           </div>
         </div>
      </div>
    </div>
  </div>
<div class="modal fade" id="totalFollowers" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
          <h4 class="modal-title" id="myModalLabel">FOLLOWERS (<span ng-bind="TotalFollowers"></span>)</h4>
        </div>
        <div class="modal-body listing-space non-footer">
          <div class="default-scroll scrollbar">
            <ul class="list-group removed-peopleslist">
              <li ng-repeat="ld in followers_list" class="list-group-item">
                <figure>
                  <a ng-href="{{SiteURL+ld.ProfileLink}}">
                    <img  ng-if="ld.ProfilePicture!==''"  class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{ld.ProfilePicture}}" /> 
                    <img  ng-if="ld.ProfilePicture==''"  class="img-circle" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> 
                  </a>
                </figure>
                <div class="description">
                  <a ng-href="{{SiteURL+ld.ProfileLink}}" class="name" ng-bind="ld.FirstName+ ' ' +ld.LastName"></a> 
                  <!-- <span class="location" ng-if="ld.CityName!=='' && ld.CountryName!==''" ng-bind="ld.CityName+', '+ld.CountryName"></span> --> </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>   
  <input type="hidden" id="FollowPageNo" value="0" />
</div>


</div>


<div id="fb-root"></div>