<aside class="col-md-3 col-sm-3 col-xs-12 pull-right sidebar" ng-cloak>
<?php  if(isset($pname) && $pname=='wall'){  ?>
<div class="panel panel-default personalise">
  <div class="panel-heading">
      <?php if($this->session->userdata('UserID') == $UserID){ ?>
      <h3 class="panel-title">
            <svg height="16px" width="16px" class="svg-icons icnGobal">
                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnGobal"></use>
            </svg> 
            <svg ng-click="redirectUrl('<?php echo site_url('myaccount') ?>')" height="25px" width="25px" class="svg-icons pull-right m-t-5">
                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#iconEdit"></use>
            </svg> 
          Intro
      </h3>
      <?php } else { ?>
        <h3 class="panel-title" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <svg height="16px" width="16px" class="svg-icons icnGobal">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnGobal"></use>
        </svg> 
            Intro
            <i class="icon-arrow-ac"></i>
        </h3>
      <?php } ?>
  </div>
  <div class="panel-body collapse in" id="collapseExample">
      <div class="intro-content">
          <p ng-cloak ng-bind="introduction.aboutme"></p>
          <ul class="user-detail-listing">
              <li ng-cloak ng-if="introduction.gender!=='' || introduction.age>0">
                  <i>
                      <svg height="14px" width="14px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnPerson"></use>
                      </svg>
                  </i>
                  <span ng-cloak ng-if="introduction.gender!=='' && introduction.age>0" ng-bind="introduction.gender+', '+introduction.age"></span>
                  <span ng-cloak ng-if="introduction.gender=='' && introduction.age>0" ng-bind="introduction.age"></span>
                  <span ng-cloak ng-if="introduction.gender!=='' && introduction.age==0" ng-bind="introduction.gender"></span>
              </li>
              <li ng-cloak ng-repeat="cc in introduction.current_companies">
                  <i>
                      <svg height="14px" width="14px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnBriefcase"></use>
                      </svg>
                  </i>
                  <span>Works at <a ng-bind="cc.OrganizationName"></a></span>
              </li>
              <li ng-cloak ng-repeat="pc in introduction.previous_companies">
                  <i>
                      <svg height="14px" width="14px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnBriefcase"></use>
                      </svg>
                  </i>
                  <span>Worked at <a ng-bind="pc.OrganizationName"></a></span>
              </li>
              <li ng-cloak ng-repeat="ce in introduction.current_educations">
                  <i>
                      <svg height="17px" width="17px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnSchool"></use>
                      </svg>
                  </i>
                  <span>Studied at <a ng-bind="ce.University"></a></span>
              </li>
              <li ng-cloak ng-repeat="pe in introduction.previous_educations">
                  <i>
                      <svg height="17px" width="17px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnSchool"></use>
                      </svg>
                  </i>
                  <span>Went to <a ng-bind="pe.University"></a></span>
              </li>
              <li ng-cloak ng-if="introduction.Location!==''">
                  <i>
                      <svg height="17px" width="17px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnMapMarke"></use>
                      </svg>
                  </i>
                  <span>From 
                    <span ng-cloak ng-if="introduction.Location" ng-bind="introduction.Location"></span>
                  </span>
              </li>
              <li ng-if="introduction.showRelation">
                  <i>
                      <svg height="16px" width="16px" class="svg-icons">
                          <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL ?>img/sprite.svg#icnFavoriteOutline"></use>
                      </svg>
                  </i>
                  <span ng-cloak><span ng-cloak ng-bind="introduction.MartialStatusTxt"></span> <span ng-cloak ng-if="introduction.RelationWithName!==''"><span ng-if="introduction.MartialStatusTxt=='Married'">to</span><span ng-if="introduction.MartialStatusTxt!=='Married'">with</span></span> <a ng-cloak ng-if="introduction.RelationWithURL!==''" ng-bind="introduction.RelationWithName"></a> <a ng-cloak ng-if="introduction.RelationWithURL==''" ng-bind="introduction.RelationWithName"></a></span>
              </li>
          </ul>
      </div>
      <div class="intro-footer">
          <div ng-cloak class="social-icons" ng-if="introduction.FacebookUrl!=='' || introduction.TwitterUrl!=='' || introduction.LinkedinUrl!=='' || introduction.GplusUrl!==''">
              <ul>
                <li ng-cloak ng-if="introduction.FacebookUrl!==''">
                  <div class="social-buttons" ng-click="redirectTo(introduction.FacebookUrl)">
                    <button ng-click="redirectUrl(introduction.FacebookUrl,0,1)" class="btn btn-primary btn-facebook btn-sm no-rounded-corner" type="button"><i class="icon-facebook"></i></button>
                  </div>
                </li>
                <li ng-cloak ng-if="introduction.TwitterUrl!==''">
                  <div class="social-buttons" ng-click="redirectTo(introduction.TwitterUrl)">
                    <button ng-click="redirectUrl(introduction.TwitterUrl,0,1)" class="btn btn-primary btn-twitter btn-sm no-rounded-corner" type="button"><i class="icon-twitter"></i></button>
                  </div>
                </li>
                <li ng-cloak ng-if="introduction.LinkedinUrl!==''">
                  <div class="social-buttons" ng-click="redirectTo(introduction.LinkedinUrl)">
                    <button ng-click="redirectUrl(introduction.LinkedinUrl,0,1)" class="btn btn-primary btn-linkedin btn-sm no-rounded-corner" type="button"><i class="icon-linkedin"></i></button>
                  </div>
                </li>
                <li ng-cloak ng-if="introduction.GplusUrl!==''">
                  <div class="social-buttons" ng-click="redirectTo(introduction.GplusUrl)">
                    <button ng-click="redirectUrl(introduction.GplusUrl,0,1)" class="btn btn-primary btn-gplus btn-sm no-rounded-corner" type="button"><i class="icon-gplus"></i></button>
                  </div>
                </li>
              </ul>
          </div>
      </div>
  </div>
</div>
<?php }  ?> 


<?php if(isset($IsNewsFeed)){ ?>
    <div  data-ng-init="getNewMebers(5,0,0); "  ng-cloak ng-show="newMember.length>0" class="hidden-xs panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">
        <svg height="15px" width="15px" class="svg-icons icnGobal">
            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnPerson'}}"></use>
        </svg>
         New Member  
      </h3>
    </div>
    <div class="panel-body">
      <div style="display:none;" class="new-member-loader">
        <div class="spinner32"></div>
      </div>
      <ul class="list-group removed-peopleslist middle-listings">
        <li ng-repeat="Member in newMember" repeat-done="triggerTooltip()" class="list-group-item">
          <figure>
              <a entitytype="user" entityguid="{{Member.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{Member.ProfileURL}}" target="_self"> 
                  <img ng-if="Member.ProfilePicture!==''" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{Member.ProfilePicture}}" class="img-circle"   err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> <img ng-if="Member.ProfilePicture==''" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  /> 
              </a>
          </figure>
          <div class="description"> 
              <a entitytype="user" entityguid="{{Member.UserGUID}}" class="a-link name loadbusinesscard" ng-href="<?php echo site_url() ?>{{Member.ProfileURL}}" ng-bind="Member.FirstName+' '+Member.LastName" target="_self"></a> 
               <div ng-cloak class="location ellipsis" style="width:155px;" ng-if="Member.CityName !== '' && Member.CountryName !== '' ">
                 <i>
                    <svg height="16px" width="16px" class="svg-icons">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                    </svg>
                </i>   
                <span ng-bind="Member.CityName+', '+Member.CountryName"></span>
               </div> 
               <div ng-cloak class="location ellipsis" style="width:155px;" ng-if="Member.CityName !== '' && Member.CountryName == '' ">
                 <i>
                    <svg height="16px" width="16px" class="svg-icons">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                    </svg>
                </i>  
                <span ng-bind="Member.CityName"></span>
               </div> <a ng-cloak class="request-status" ng-click="getMutualFriends(Member.UserGUID);" ng-if="Member.MutualFriends>1" ng-bind="Member.MutualFriends+' mutual friends'"></a> <a ng-cloak class="request-status" ng-click="getMutualFriends(Member.UserGUID);" ng-if="Member.MutualFriends==1" ng-bind="Member.MutualFriends+' mutual friend'"></a>
              <div ng-cloak ng-if="Member.ShowFollowBtn=='1'" class="button-wrap-sm">
                  <button ng-click="toggleFollowUser(Member.UserGUID)" ng-bind="Member.FollowStatus" class="btn btn-default btn-xs"></button>
              </div>
               </div>
              <ul class="subnav-btn positon-ab">
                <li ng-cloak ng-if="Member.ShowFriendsBtn=='1' && Member.FriendStatus=='4'" ng-click="sendRequest(Member.UserGUID,'peopleyoumayknow')"  data-toggle="tooltip" data-original-title="Add Friend"><i class="icon-n-memeber"></i></li>
                <li ng-cloak ng-if="Member.ShowFriendsBtn=='1' && Member.FriendStatus=='2'" ng-click="rejectRequest(Member.UserGUID,'peopleyoumayknow')"  data-toggle="tooltip" data-original-title="Request sent"><i class="icon-n-rq-sent"></i></li>
               
              </ul>
            <div class="m-t-10" ng-bind="Member.Introduction"></div>
                                <a class="remove"><i class="icon-remove" ng-click="nextMember()"></i></a>
        </li>
      </ul>
    </div> 
  </div>


  <!-- Recent conversation ends -->
<div ng-show="( (IsReminder == 1 ) && (IsFilePage != 1))" class="panel panel-default" ng-cloak>
      <div class="panel-body">
        <h3 class="panel-title border-bottom" ng-bind="lang.w_reminder_calendar"></h3>
        <div class="datePicker reminders">
              <div class="reminder-calendar">
                  <div id="StoredReminderCal"></div>
              </div>
          </div>
      </div>
  </div>
  <!-- Recent Conversation New -->

  <!-- New Layout Starts -->
  <div class="panel panel-default">
      <div class="recent-block">
          <h3 class="panel-title">Upcoming Events <a class="pull-right">See All</a></h3>
          <div class="panel-body">
              <ul class="list-group">
                  <li>
                      <div class="upcoming-event">
                          <a class="remove">
                              <i class="ficon-cross"></i>
                          </a>
                          <img src="../img/event-img.jpg" >
                          <div class="event-desc">
                              <div class="event-inner">
                                  <h4><a>Indore Youth Leadership Conclave</a> </h4>
                                  <div>Hosted by Indore YLC</div>
                                  <div>July 22 - July 24 2016, 9:03 AM</div>
                                  <div>at Indore, India</div>
                                  <div class="button-wrap-sm">
                                      <button class="btn btn-default btn-xs">Attend</button>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </li>
              </ul>
          </div>
      </div>
      <div class="recent-block" data-ng-init="getPeopleYouMayKnow(2,0,0);" ng-cloak>
          <h3 class="panel-title" ng-bind="lang.people_you_know"></h3>
          <div class="panel-body">
              <div class="list-vertical row">
                <div class="list-item col-xs-6" ng-repeat="peopleYouKnow in peopleYouMayKnow" repeat-done="triggerTooltip()">
                      <figure>
                          <a entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="loadbusinesscard" ng-href="<?php echo site_url() ?>{{peopleYouKnow.ProfileURL}}" target="_self"> 
                              <img ng-if="peopleYouKnow.ProfilePicture!==''" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{peopleYouKnow.ProfilePicture}}" class="img-circle"   err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> <img ng-if="peopleYouKnow.ProfilePicture==''" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  /> 
                          </a>
                      </figure> 
                          <a entitytype="user" entityguid="{{peopleYouKnow.UserGUID}}" class="name loadbusinesscard" ng-href="<?php echo site_url() ?>{{peopleYouKnow.ProfileURL}}" ng-bind="peopleYouKnow.FirstName+' '+peopleYouKnow.LastName" target="_self"></a>
                          <span class="location" ng-if="peopleYouKnow.MutualFriends=='1'" ng-bind="peopleYouKnow.MutualFriends+' Mutual Friend'"></span>
                          <span class="location" ng-if="peopleYouKnow.MutualFriends>'1'" ng-bind="peopleYouKnow.MutualFriends+' Mutual Friends'"></span>
                          <div class="button-wrap-sm">
                              <button ng-cloak ng-if="peopleYouKnow.ShowFriendsBtn=='1' && peopleYouKnow.FriendStatus=='2'" ng-click="rejectRequest(peopleYouKnow.UserGUID,'peopleyoumayknow')" class="btn btn-default btn-xs">Cancel Request</button>
                              <button ng-cloak ng-if="peopleYouKnow.ShowFriendsBtn=='1' && peopleYouKnow.FriendStatus=='4'" ng-click="sendRequest(peopleYouKnow.UserGUID,'peopleyoumayknow')" class="btn btn-default btn-xs">Add As Friend</button>
                          </div> 
                      <a ng-click="hideSuggestedPeople(peopleYouKnow.UserGUID)" class="remove"><i class="ficon-cross"></i></a>
                  </div>
              </div>
              <div class="footer-link">
                  <a class="pull-right">See All</a>
              </div>
          </div>
      </div>
      
      <div class="recent-block" ng-controller="PageCtrl" id="PageCtrl" ng-init="PageSuggestion(5,'0',0);">
          <h3 class="panel-title">Pages <a class="pull-right">Create</a></h3>
          <div class="panel-body padding">
              <ul class="list-items-hovered list-items-borderd">
                  <li ng-repeat="suggestion in SuggestionObj = pageSuggestions | limitTo: 5">
                      <figure>
                          <a entitytype="page" entityguid="{{suggestion.PageGUID}}" class="loadbusinesscard" href="page/{{suggestion.PageURL}}"><img ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/';?>{{suggestion.ProfilePicture}}" class="img-circle"  ></a>
                      </figure>
                      <div class="description">
                          <a entitytype="page" entityguid="{{suggestion.PageGUID}}" class="a-link name loadbusinesscard" href="<?php echo base_url();?>page/{{suggestion.PageURL}}" ng-bind="suggestion.Title"></a>
                          <span class="location" ng-if='suggestion.NoOfFollowers == 1' ng-bind="suggestion.NoOfFollowers+' Follwer'"></span>
                          <span class="location" ng-if='suggestion.NoOfFollowers > 1'  ng-bind="suggestion.NoOfFollowers+' Follwers'"></span>
                          <div class="button-wrap-sm">
                              <button class="btn btn-default btn-xs" ng-click='toggleFollow(suggestion.PageID,"UserList",suggestion.PageGUID);'>Follow</button>
                          </div>
                      </div>
                  </li>
              </ul>
          </div>
      </div>
      <div class="recent-block" ng-if="SettingsData.m30=='1'" ng-controller="PollCtrl" ng-init="get_polls_about_to_close()" ng-show="polls_about_to_close.length>0">
          <h3 class="panel-title">Polls<a class="pull-right">Create</a></h3>
          <div class="panel-body" class="pollSlide">
              <ul class="list-group thumb-30">
                  <li ng-repeat="polls in polls_about_to_close">
                      <div class="description">
                          <a class="a-link name slideAction" href="javascript:void(0);" data-ng-click="show_poll_option_sidebar(polls);" ng-bind-html="textToLink(polls.PostContent);"></a>
                                <span class="text-secondary">by</span>
                                <a ng-if="polls.PostAsEntityOwner!=='1'" ng-cloak class="a-link loadbusinesscard" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url();?>{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>
                                <a ng-if="polls.PostAsEntityOwner=='1'" ng-cloak class="a-link loadbusinesscard" entitytype="{{polls.EntityModuleType}}" entityguid="{{polls.EntityGUID}}" href="<?php echo base_url();?>page/{{polls.EntityProfileURL}}" ng-bind="polls.PollData[0].CreatedBy.Name"></a>
                          <span class="location">11 Dec at 9:03 AM By <a class="a-link">Pankaj..</a></span>
                          <div class="button-wrap-sm">
                              <button class="btn btn-default btn-xs">Vote</button>
                          </div>
                      </div>
                  </li>
              </ul>
          </div>
      </div>
  </div>
  <!-- New Layout Ends -->

    <div ng-hide="( ( (IsReminder == 1 ) || (IsFilePage == 1) ) || ( ( rcsearch == '' ) && ( recentConversations.length == 0 ) ) )" class="panel panel-default hidden-xs" ng-cloak ng-hide="rcsearch=='' && recentConversations.length==0" ng-cloak>
        <div class="panel-heading">
            <h3 ng-cloak ng-show="recentConversations.length>0" class="panel-title">Recent Conversations </h3>
            <h3 ng-cloak ng-show="recentConversations.length==0" class="panel-title">No Conversations </h3>
        </div>
        <div class="panel-body">
            <div class="quick-search">
                <div class="form-group">
                    <input ng-keyup="getRecentCoversation();" ng-model="rcsearch" id="rcsearch" type="text" class="form-control" placeholder="Quick search">
                </div>
            </div> 
            <ul class="list-group removed-peopleslist quick-search-list">
                <li ng-repeat="rconv in recentConversations" repeat-done="triggerTooltip()" class="list-group-item draggable" draggable="rconv" draggable-target='.sortable'>
                    <figure>
                        <a ng-href="{{SiteURL+rconv.ProfileURL}}">
                        <img ng-if="rconv.ProfilePicture!=='' && (rconv.ProfilePicture!=='group-no-img.jpg' || rconv.Type=='FORMAL')" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{rconv.ProfilePicture}}" class="img-circle"   err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                        <img ng-if="rconv.ProfilePicture==''" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  />
                        <div ng-if="rconv.Type=='INFORMAL' && rconv.ProfilePicture=='group-no-img.jpg'" ng-class="(rconv.Members>2) ? 'group-thumb' : 'group-thumb-two' ;" class="m-user-thmb group-thumb">
                          <span ng-repeat="r in rconv.Members">

                            <img  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{r.ProfilePicture}}" entitytype="user" ng-if="$index<=2">

                          </span>                                          
                        </div> 
                        </a>
                    </figure>
                    <div class="description">
                        <a ng-href="{{SiteURL+rconv.ProfileURL}}" ng-if="rconv.EntityType=='User' || (rconv.EntityType=='Group' && rconv.MembersCount>2) || rconv.Type=='FORMAL'" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="name ellipsis-lg loadbusinesscard" ng-bind="rconv.FirstName+' '+rconv.LastName" target="_self"></a>
                        <a ng-href="{{SiteURL+rconv.ProfileURL}}" ng-if="rconv.EntityType=='Group' && rconv.MembersCount==2 && rconv.Type=='INFORMAL'" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="name loadbusinesscard" ng-bind="rconv.FirstName+' '+rconv.LastName+' ...'" target="_self"></a>

                        <span class="location" ng-if="rconv.MembersCount>0 && rconv.IsPublic!==''"> 
                        <i class="icon-n-global" ng-if="rconv.IsPublic==1"></i> 
                        <i class="icon-n-group-secret" ng-if="rconv.IsPublic==2"></i>
                        <i class="icon-n-closed" ng-if="rconv.IsPublic==0"></i> 
                        <span ng-cloak ng-if="rconv.EntityType=='User' && rconv.MembersCount=='1'" ng-bind="rconv.MembersCount+' Friend'"></span>
                        <span ng-cloak ng-if="rconv.EntityType=='User' && rconv.MembersCount>'1'" ng-bind="rconv.MembersCount+' Friends'"></span>
                        <span ng-cloak ng-if="rconv.EntityType=='Group' && rconv.MembersCount=='1'" ng-bind="rconv.MembersCount+' Member'"></span>
                        <span ng-cloak ng-if="rconv.EntityType=='Group' && rconv.MembersCount>'1'" ng-bind="rconv.MembersCount+' Members'"></span>
                        </span>
                    </div>
                    <span ng-if="rconv.EntityType=='Group' && rconv.IsAdmin== true " onclick="createEditGroup('EditGroup',this)" data-groupguid="{{rconv.ModuleEntityGUID}}" data-toggle="modal" data-target="#createGroup">
                      <i class="icon-n-group-edit"  data-toggle="tooltip" data-original-title="Edit Group"></i>
                    </span>
                </li>
            </ul>
        </div>
    </div>

  <!-- Recent Converstation New -->

<!-- Messages Start -->
  <div ng-hide=" (IsReminder == 1 ) || (IsFilePage == 1) " ng-controller="messageSectionCtrl" id="messageSectionCtrl" ng-init="" class="hidden-xs panel panel-default" ng-cloak>
  <!--<div ng-show="IsReminder!=1" ng-controller="messageSectionCtrl" id="messageSectionCtrl" ng-init="" class="hidden-xs panel panel-default" ng-cloak>-->
    <div class="panel-heading">
        <h3 ng-cloak ng-show="thread_list.length>0" class="panel-title" ng-class="(newsFeedSetting.rm!=='1') ? 'border-None' : '' ;">
            Recent Messages
            <button type="button" ng-click="$scope.compmsg(1);" class="btn btn-default btn-sm" data-toggle="modal" data-target="#newMsg"> <i class="icon-n-edit"></i> </button>
            <div class="toggle-checkbox pull-right">
                <input class="toggle" type="checkbox" ng-true-value="1" ng-false-value="0" ng-click="saveFeedSetting('rm'); showHideMsgSection();" ng-checked="settingEnabled(newsFeedSetting.rm)">
                <label for=""></label>
            </div>
        </h3>
        <h3 ng-cloak ng-show="thread_list.length==0">No Messages</h3>
    </div>
    <div class="panel-body msz-listing" ng-show="newsFeedSetting.rm=='1'" id="MsgPanelBody" ng-init="get_threads();">
        <ul class="m-user-listing">
            <li ng-cloak ng-repeat="thread in thread_list" repeat-done="layoutDone()" id="thread-{{thread.ThreadGUID}}" ng-class="(thread.InboxNewMessageCount>0) ? 'unread' : '' ;">
                <div data-toggle="modal" data-target="#newMsg" ng-click="$scope.compmsg(0); get_new_thread_details(thread.ThreadGUID)" ng-if="thread.ThreadImageName==''" class="m-user-thmb" ng-class="(thread.Recipients.length>2) ? 'group-thumb' : 'group-thumb-two' ;">
                    <span ng-repeat="recipients in thread.Recipients">
                      <img class="loadbusinesscard" entityguid="{{thread.Recipients[0].UserGUID}}"  ng-if="thread.Recipients.length==1" entitytype="user" ng-src="{{ImageServerPath+'upload/profile/220x220/'+recipients.ProfilePicture}}" >
                      <img ng-if="thread.Recipients.length>1" entitytype="user" ng-src="{{ImageServerPath+'upload/profile/220x220/'+recipients.ProfilePicture}}" >
                    </span>                                          
                </div>

                <div data-toggle="modal" data-target="#newMsg" ng-click="$scope.compmsg(0);get_new_thread_details(thread.ThreadGUID)" ng-if="thread.ThreadImageName!==''" class="m-user-thmb">
                    <span>
                      <img ng-if="thread.EditableThread=='1'" width="50" ng-src="{{ImageServerPath+'upload/messages/220x220/'+thread.ThreadImageName}}" >
                      <img class="loadbusinesscard" entityguid="{{thread.Recipients[0].UserGUID}}" entitytype="user"  ng-if="thread.EditableThread=='0' && thread.Recipients.length==1" width="50" ng-src="{{ImageServerPath+'upload/profile/220x220/'+thread.ThreadImageName}}" >
                      <img ng-if="thread.EditableThread=='0' && thread.Recipients.length>1" width="50" ng-src="{{ImageServerPath+'upload/profile/220x220/'+thread.ThreadImageName}}" >
                    </span>                                      
                </div>

                <div class="m-msz-detail-right">
                    <span class="m-msz-time" ng-bind="date_format((thread.InboxUpdated),1)"></span>
                    <a ng-if="thread.InboxNewMessageCount>0" ng-bind="thread.InboxNewMessageCount+' New'" class="m-new-msz"></a>
                    <div class="m-msz-action"> 
                       <i ng-click="change_thread_status(thread.ThreadGUID,'DELETED');" class="icon-msz-remove" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</i>
                       <i ng-if="Filter!=='ARCHIVED'" ng-click="change_thread_status(thread.ThreadGUID,'ARCHIVED');" class="icon-msz-archive" data-toggle="tooltip" data-placement="top" title="Archive">&nbsp;</i>
                       <i ng-if="Filter=='ARCHIVED'" ng-click="change_thread_status(thread.ThreadGUID,'UN_ARCHIVE');" class="icon-msz-archive" data-toggle="tooltip" data-placement="top" title="UnArchive">&nbsp;</i>
                       <i ng-click="change_thread_status(thread.ThreadGUID,'UN_READ');" ng-if="thread.InboxNewMessageCount==0" class="icon-msz-read" data-toggle="tooltip" data-placement="top" title="Mark as unread">&nbsp;</i>
                       <i ng-click="change_thread_status(thread.ThreadGUID,'READ');" ng-if="thread.InboxNewMessageCount>0" class="icon-msz-read" data-toggle="tooltip" data-placement="top" title="Mark as read">&nbsp;</i> 
                    </div>
                </div>
                <div class="m-msz-indetail">
                     <span data-toggle="modal" ng-if="thread.Recipients.length==1" data-target="#newMsg" ng-click="$scope.compmsg(0); get_new_thread_details(thread.ThreadGUID)" entityguid="{{thread.Recipients[0].UserGUID}}" entitytype="user" class="m-ellipsis loadbusinesscard" ng-bind="thread.ThreadSubject"></span>
                     <span data-toggle="modal" ng-if="thread.Recipients.length>1" data-target="#newMsg" ng-click="$scope.compmsg(0); get_new_thread_details(thread.ThreadGUID)" class="m-ellipsis" ng-bind="thread.ThreadSubject"></span>
                     
                     <div ng-if="thread.Body!==''" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind-html="getMsgBodyHTML(thread.Body,1)"></div>
                     <div ng-if="thread.Body=='' && thread.AttachmentCount==1" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind="thread.AttachmentCount+' file attached'"></div>
                     <div ng-if="thread.Body=='' && thread.AttachmentCount>1" ng-click="get_new_thread_details(thread.ThreadGUID)" class="m-msz-short m-ellipsis" ng-bind="thread.AttachmentCount+' files attached'"></div>
                     
                </div>
            </li>
        </ul>
    </div>
    <div ng-show="newsFeedSetting.rm=='1' && thread_list.length>0" id="seeAlllink" class="seeAlllink"> <a href="<?php echo site_url('messages') ?>">See all Messages <i class="icon-n-arrow-f">&nbsp;</i></a></div>
    <!--Recent Popup Starts-->
    <div class="modal fade" id="newMsg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
                    <h4 class="modal-title" ng-if="ComposingMessage==1" id="myModalLabel">Quick compose a message</h4>
                    <h4 class="modal-title" ng-if="ComposingMessage==0" id="myModalLabel">Quick Message</h4>
                </div>
                <div class="quick-compose">
                    <div class="m-conversation">
                                <div style="display:none;" class="m-conversation-loader loader" style="height:200px;width:200px;margin:-100px 0px 0px -100px;"></div>
                                <aside ng cloak class="m-new-message" ng-if="ComposingMessage=='1'">
                                    <span class="m-message-to">To:  </span>
                                    <div class="m-autosuggest">
                                      <div id="sendMszto">
                                        <tags-input ng-model="tags" display-property="name" add-from-autocomplete-only="true" on-tag-added="getPlaceHolder()" on-tag-removed="getPlaceHolder()" key-property="UserGUID" placeholder="Name" replace-spaces-with-dashes="false">
                                            <auto-complete source="loadFriends($query)"
                                                       min-length="0"
                                                       load-on-focus="true"
                                                       load-on-empty="true"
                                                       max-results-to-show="4"
                                                       template="userDropdownTemplate"></auto-complete>
                                          </tags-input>
                                      </div>                                            
                                      <script type="text/ng-template" id="userDropdownTemplate">
                                          <a href="javascript:void(0);" class="m-conv-list-thmb">
                                            <img ng-src="{{data.thumb}}" >
                                          </a>
                                          <a href="javascript:void(0);" class="m-u-list-name"  ng-bind-html="$highlight($getDisplayText())"></a>
                                      </script>  

                                    </div>
                                </aside>

                      <div class="m-conversation-block mCustomScrollbar-right">
                          <div class="m-conversation-content">
                              <!-- <abbr class="conv-started">Conversation started 10 Nov 2014</abbr>  -->
                              <ul class="m-conversation-list">
                                  <li ng-repeat="msg in MessageList" ng-class="{'m-group-activity':(msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'),'conversation-date':(msg.NewDate!=='' && msg.ActionName!=='THREAD_CREATED' && $index>0)}" repeat-done="layoutDone()" id="msg-{{msg.MessageGUID}}">
                                      <div class="m-date-seprator"  ng-if="msg.NewDate!=='' && msg.ActionName!=='THREAD_CREATED' && $index>0">
                                        <span class="conv-date" ng-bind="msg.NewDate"></span>
                                      </div>
                                        <i ng-if="msg.Type=='MANUAL'" ng-click="removeMessage(msg.MessageGUID)" class="icon-remove"></i>
                                        <a ng-if="msg.Type=='MANUAL'" ng-href="{{'<?php echo site_url() ?>'+msg.ProfileURL}}" class="m-conv-list-thmb">
                                          <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+msg.ProfilePicture}}" >
                                        </a>
                                        <div ng-if="msg.Type=='MANUAL'" class="overflow m-conv-msz">
                                          <a ng-href="{{'<?php echo site_url() ?>'+msg.ProfileURL}}" ng-bind="msg.FirstName+' '+msg.LastName"></a>
                                          <span class="m-msz-time" ng-bind="getFormattedTime(msg.CreatedDate,'h:mm a')"></span>
                                          <div class="m-msz-text" ng-bind-html="getMsgBodyHTML(msg.Body)"></div>
                                          <div ng-repeat="files in msg.Media" ng-cloak ng-if="files.MediaType=='Documents'" class="m-attached-file">
                                            <i class="icon-m-attached"></i>
                                            <span class="filename" ng-bind="files.OriginalName"></span>
                                            <a ng-href="{{'<?php echo site_url() ?>home/download/'+files.MediaGUID}}"><?php echo lang('msg_download') ?></a>
                                          </div>
                                          <ul class="m-msz-attached" id="lg-{{msg.MessageGUID}}">
                                            <li ng-repeat="images in msg.Media" ng-init="callLightGallery(msg.MessageGUID)" ng-data-thumb="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName}}" ng-data-src="{{ImageServerPath+'upload/messages/'+images.ImageName}}" ng-if="images.MediaType=='Image'" class="attached-list">
                                                <img  ng-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName}}" />
                                            </li>
                                            <li ng-repeat="images in msg.Media" ng-init="(images.ConversionStatus=='Finished') ? callLightGallery(msg.MessageGUID) : '' ;" ng-data-html="{{'#m-'+images.MediaGUID}}" ng-if="images.MediaType=='Video'" ng-class="{'videoprocess':images.ConversionStatus!='Finished','attached-video':images.MediaType=='Video'}" ng-data-thumb="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" class="attached-list">
                                                <img ng-if="images.ConversionStatus=='Finished'" ng-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" ng-cloak  ng-data-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" />
                                                <i class="icon-wall-video" ng-if="images.MediaType=='Video' && images.ConversionStatus=='Finished'"></i>
                                                <div style="display:none;" id="m-{{images.MediaGUID}}">
                                                <img ng-cloak  ng-data-src="{{ImageServerPath+'upload/messages/220x220/'+images.ImageName+'jpg'}}" />
                                                <video width="100%" controls="" class="object">
                                                    <source type="video/mp4" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'mp4'}}"></source>
                                                    <source type="video/ogg" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'ogg'}}"></source>
                                                    <source type="video/webm" src="" dynamic-url dynamic-url-src="{{ImageServerPath+'upload/messages/'+images.ImageName+'webm'}}"></source>
                                                     Your browser does not support HTML5 video.
                                                </video>
                                            </div>
                                            </li>
                                          </ul>
                                        </div>
                                      <abbr ng-if="msg.Type=='AUTO' && msg.ActionName=='THREAD_CREATED'" class="conv-started" ng-bind-html="to_trusted(msg.Body)"></abbr>
                                      <span ng-if="msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'" class="m-msz-time pull-right" ng-bind="getFormattedTime(msg.CreatedDate,'h:mm a')"></span>                                            
                                      <div ng-if="msg.Type=='AUTO' && msg.ActionName!=='THREAD_CREATED' && msg.ActionName!=='CONVERSATION_DATE'" ng-bind-html="to_trusted(msg.Body)"></div> 
                                  </li>
                              </ul> 
                           
                          </div>
                      </div>
                    </div>
                    <!--  Write a Reply  -->
                    <div class="clear"></div>
                    <div class="m-write-reply">
                      <div class="m-write-reply-inner">
                        <div class="m-write-msz">
                          <textarea ng-cloak ng-if="ComposingMessage=='0'" class="msgbody" ng-model="MsgBody" name="" placeholder="Write a reply"></textarea>
                          <textarea ng-cloak ng-if="ComposingMessage=='1'" class="msgbody" ng-model="MsgBody" name="" placeholder="Write a message"></textarea>
                        </div>
                        <div class="m-attachment-view" style="display:none;">
                            <ul class="m-file-attached-list m-file-attached-wrapper" style="display:none;">
                              
                            </ul>
                            <div class="m-media-attached-list" style="display:none;">
                                <ul class="attachedList"></ul>
                            </div>
                        </div>
                        <div class="m-attachment-block">
                           <ul class="m-attachment-button">
                              <li id="addFile" onclick="checkAttachmentView();">
                                <i class="icon-addfile"></i>
                                <?php echo lang('msg_add_file') ?>
                              </li>
                              <li id="addMessageMedia" onclick="checkAttachmentView();">
                                <i class="icon-photo"></i>
                               <?php echo lang('msg_add_photos') ?>
                              </li>
                            </ul> 
                            <button ng-if="ComposingMessage=='0'" type="button" class="send-btn-msg btn btn-primary btn-small pull-right" ng-click="reply();"> <?php echo lang('msg_send') ?> </button>
                            <button ng-if="ComposingMessage=='1'" type="button" class="send-btn-msg btn btn-primary btn-small pull-right" ng-click="compose();"> <?php echo lang('msg_send') ?> </button>
                        </div>
                         
                      </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Message Popup Ends -->

  </div>
  <!-- Messages End -->

  <!-- Message Popup open via business card Start -->
<div ng-if="LoginSessionKey" ng-controller="messageSectionCtrl" class="modal fade" id="MsgFromCard" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  
    <div class="modal-dialog"  ng-init="getUserDetails();">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="icon-close"></i></span>
          </button>
          <h4 class="modal-title" id="myModalLabel">New Message</h4>
        </div>
        <form id="newmsgform" ng-submit="submitMessageViaCard();">
        <div class="modal-body">
          
          <div class="no-scrollbar">
              <div class="form-group">
                <label>To</label>
                <div class="text-field">
                  <div data-error="hasError" class="text-field">
                    <input type="text" ng-value="" value="" id="toAddressCard" readonly="readonly" uix-input="">
                    <label id="errorTofield" class="error-block-overlay"></label>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Message</label>
                <div data-error="hasError" class="textarea-field">
                  <textarea maxcount="200" maxlength="200" data-req-maxlen="200" data-req-minlen="200" ng-model="MessageTxtViaCard" id="textareaIDCardMsg" uix-textarea placeholder="Write something..." class="msg-textarea"></textarea>
                </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
        <input type="hidden" id="ToMssgFrmCardGUID" value="">
          <button type="submit" class="btn btn-primary pull-right" onclick="return checkstatus('newmsgform');">SEND</button>
        </div>
        </form>
      </div>
    </div>
   
</div>
<!--  Message Popup open via business card Ends -->


<?php } ?>
  
  <?php if(isset($ShowRecentActivity) && $ShowRecentActivity==1){ ?>
  <div ng-cloak data-ng-controller="UserProfileCtrl">
    <div  ng-init="getRecentActivities()">     
      <div class="panel panel-widget" ng-if="recentActivitiesCount>0">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo lang('recent_activity');?></h3>
        </div>
        <div class="panel-body">
          <ul class="list-items-group">
            <li class="items item-activity" ng-repeat="rAct in recentActivities"> <span ng-class="'ra-'+rAct.ActivityGUID" ng-bind-html="rAct.Message"></span> </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
  
  
</aside>

<?php if(isset($IsNewsFeed)){ ?>
<?php $this->load->view('include/live-feed') ?>

 
<!-- Create Introduction Modal -->
<div ng-if="LoginSessionKey" class="modal fade" id="Introduction" tabindex="-1" ng-if="ShowIntroPopup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >  
  	<div class="modal-dialog modal-lg">
   	 <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
        <h4 class="modal-title" id="myModalLabel">INTRODUCTION</h4>
      </div>
      <div class="modal-body">
        <div class="no-scrollbarddd">
          <form id="formIntro" >             
            <div class="form-group">
              <label>Whether you’re new to the site or have been a long standing member of the community, step forward and introduce yourself.</label>
              <div data-error="hasError" class="textarea-field">
                <textarea maxcount="140" rows="5" maxlength="140" uix-textarea data-mandatory="true" class="form-control" data-controltype="generalTextArea" 
                id="UserIntro" data-msglocation="errorUserIntro" name="Introduction" placeholder="Hello, my name is {{FirstName + ' ' + LastName}}, I like to paint with pixels share my thought in 140 characters or less, pretend to be a photographer..." tabindex="1" data-requiredmessage="Required" data-ng-model="UserIntro"></textarea>
                <label class="error-block-overlay" id="errorUserIntro"></label>
              </div>
            </div>            
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary pull-right" ng-click="SaveIntro()" >Save</button>
      </div>
    </div>
   
 </div>
</div>
<?php } ?>
