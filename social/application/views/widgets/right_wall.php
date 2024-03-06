<?php 
    if(!$this->settings_model->isDisabled(14)) : // Check if Event module is enabled  
        $this->load->model('events/event_model');
?>
<div ng-cloak class="panel panel-widget" ng-show="upcomingEvents.length>0">
    <div class="panel-heading">
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>

        <h3 class="panel-title"><a target="_self" href="<?php echo site_url('events') ?>" class="link">See All</a><span class="text" ng-bind="lang.w_upcoming_events"></span></h3>

        <?php } else { ?>
        <h3 class="panel-title"><span class="text" ng-bind="FirstName+' is attending'"></span></h3>
        <?php } ?>
    </div>
    <div class="panel-body">        
        <div ng-repeat="event in upcomingEvents">
            <div class="upcoming-event" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{event.ProfilePicture}})'}">
                <div class="event-desc">
                    <div class="event-inner">
                        <h4><a target="_self" ng-href="{{event.ProfileURL}}" ng-bind="event.Title"></a> </h4>

                        <div ng-bind="'Hosted by '+event.FirstName+' '+event.LastName"></div>
                        <div><span ng-bind="getEventDate(event.StartDate,event.StartTime)"></span> <span ng-bind="getEventTime(event.StartDate,event.StartTime)"></span> - <span ng-bind="getEventDate(event.EndDate,event.EndTime)"></span> <span ng-bind="getEventTime(event.EndDate,event.EndTime)"></span></div>
                        <div ng-bind="'at '+event.Location.FormattedAddress"></div>
                        <div class="button-wrap-sm btn-group">
                            <button class="btn btn-default btn-xs" ng-click="UpdateUsersPresence('ATTENDING', 'Attending',event.EventGUID); event.EventStatus='ATTENDING'" ng-if="event.EventStatus=='' || (event.EventStatus!=='ATTENDING' && event.EventStatus!=='MAY_BE')"><span ng-bind="lang.attend_now"></span></button>
                            <button ng-if="event.EventStatus!==''" class="btn btn-default btn-xs  dropdown-toggle" data-toggle="dropdown">                                
                                <span class="text" ng-if="event.EventStatus=='ATTENDING'" ng-bind="lang.w_attending"></span>
                                
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(!$this->settings_model->isDisabled(10)) : // Check if Friend module is enabled  ?>
<div ng-cloak class="panel panel-widget" ng-show="userConnection.TotalRecords>0">
    <div class="panel-heading">
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
            <h3 class="panel-title" ng-cloak>MY CONNECTIONS ({{userConnection.TotalRecords}})</h3>
        <?php } else { ?>
            <h3 class="panel-title" ng-cloak>
                <span ng-bind="userConnection.TotalRecords+' Connections'"></span> 
                <span ng-if="userConnection.MutualFriendCount>=1" ng-bind="'('+userConnection.MutualFriendCount+' Mutual)'"></span>
            </h3>
        <?php } ?>
    </div>
    <div class="panel-body">
        <div class="list-vertical row">
            <div class="list-item col-xs-6" ng-repeat="friends in userConnection.Members">
                <figure>
                    <a target="_self" ng-href="{{'<?php echo site_url() ?>'+friends.ProfileLink}}">
                        <img ng-if="friends.ProfilePicture!==''"   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+friends.ProfilePicture}}">
                        <img ng-if="friends.ProfilePicture==''"   class="img-circle" err-Name="{{friends.FirstName+' '+friends.LastName}}">
                    </a>
                </figure>
                <a target="_self" ng-href="{{'<?php echo site_url() ?>'+friends.ProfileLink}}" class="a-link name" ng-bind="friends.FirstName+' '+friends.LastName"></a> 
                <div class="button-wrap-sm" ng-cloak ng-if="friends.MySelf!==1 && (friends.FriendStatus=='2' || friends.FriendStatus=='4')">
                    <button ng-cloak ng-if="friends.FriendStatus=='2'" ng-click="rejectRequest(friends.UserGUID,'connectionwidget')" class="btn btn-default btn-xs">Cancel Request</button>
                    <button ng-cloak ng-if="friends.FriendStatus=='4'" ng-click="sendRequest(friends.UserGUID,'connectionwidget')" class="btn btn-default btn-xs">Add As Friend</button>
                </div>
            </div>
            
        </div>
    </div>
    <div class="panel-footer">        
        <a target="_self" ng-href="{{'<?php echo site_url() ?>'+Username+'/connections'}}" class="view-link">See All</a>
    </div>
</div>
<?php endif; ?>


<?php if(!$this->settings_model->isDisabled(1)) : // Check if group module is enabled  ?>
<div ng-cloak class="panel panel-widget" id="FormCtrl" ng-cloak ng-show="TopGroup.length>0">
    <div ng-controller="GroupMemberCtrl" ng-cloak="">
        <div class="hidden-xs" id="GroupPageCtrlID" ng-controller="GroupPageCtrl" ng-cloak="">
            <div class="panel-heading">
                <h3 class="panel-title"> 
                      <?php  if ($this->session->userdata('UserID') != $UserID) { ?>
                      <a target="_self" class="link" ng-href="{{'<?php echo site_url() ?>'+ProfileURL+'/groups'}}">See All</a>
                      <?php } ?>
                      <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                      <a target="_self" data-toggle="modal" data-target="#createGroup" class="link" ng-click="CreateEditGroup('createGroup'); setGroupPopup(true);">Create</a>
                      <span class="text">MY GROUPS</span>
                      <?php }else { ?>
                      <span ng-cloak="" class="text" ng-bind="FirstName +'\'s Groups' "></span>
                      <?php } ?>
                </h3>
                <div class="nodata-panel nodata-default" ng-cloak ng-if="TopGroup.length==0">
                    <div class="nodata-text">
                        <span class="nodat-circle sm shadow">
                            <i class="ficon-smiley"></i>
                        </span>
                        <p class="no-margin">No groups created <br>by you</p>
                    </div>
                </div>
            </div>
            <div class="panel-body no-padding" ng-if="TopGroup.length > 0">
                <div style="display:none;" class="people-suggestion-loader">
                    <div class="spinner32"></div>
                </div>
                <ul class="list-items-group list-items-hovered list-items-borderd">
                    <li class="" id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = TopGroup|limitTo:3">
                        <div class="list-items-xmd">
                            <div class="list-inner">
                                <figure ng-if="list.Type=='FORMAL'">
                                    <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>{{list.ProfileURL}}">
                                        <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                                    </a>
                                </figure>
                                <figure ng-if="list.Type=='INFORMAL'" ng-class="(list.MemberCount>2) ? 'group-thumb' : 'group-thumb-two' ;">
                                    <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>{{list.ProfileURL}}">
                                        <img ng-if="list.Type=='INFORMAL' && list.ProfilePicture!='' && list.ProfilePicture!='group-no-img.jpg'" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                                        <span ng-repeat="recipients in list.EntityMembers" class="ng-scope">
                                        <img  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index<=2" class="ng-scope">
                                      </span>
                                    </a>
                                </figure>

                                <div class="list-item-body">
                                    <h4 class="list-heading-xs"><a target="_self" class="ellipsis conv-name ng-binding" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" ng-bind="list.GroupName"></a></h4>
                                    <div><small ng-bind="(list.MemberCount==1) ? list.MemberCount+' Member' : list.MemberCount+' Members' ;"></small></div>
                                </div>
                                <button ng-if="!list.Permission.IsInvited && !list.Permission.IsActiveMember && list.IsPublic == 1 && LoginSessionKey!==''" class="btn btn-default follow-btn" ng-click="joinPublicGroup(list.GroupGUID, 'fromUserWall');"><i class="ficon-plus"></i> Join</button>
                                <button ng-if="!list.Permission.IsInvited && !list.Permission.IsActiveMember && list.IsPublic == 1 && LoginSessionKey==''" class="btn btn-default follow-btn" ng-click="likeEmit('', 'ACTIVITY', '');"><i class="ficon-plus"></i> Join</button>  
                            </div>
                        </div>
                    </li>
                </ul>
                
            </div>
            <div class="panel-footer">                
                <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                    <a target="_self" class="view-link" href="<?php echo site_url('group') ?>">See All</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php  if(!$this->settings_model->isDisabled(18)) : // Check if page module is enabled   ?>
<!-- New -->
<div class="panel transparent" ng-cloak ng-show="!IsMyDeskTab && top_user_pages.length>0">
    <div class="panel-heading p-heading">
        <h3>
            <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                MY PAGES
                <a target="_self" class="pull-right gray-clr" href="<?php echo site_url('pages/types') ?>">Create</a>
                <?php }else{ ?>
                <span ng-cloak="" class="capt" ng-bind="FirstName +'\'s Pages' "></span>
            <?php } ?>
        </h3>
    </div>
    <div class="panel-body padding">
        <div class="nodata-panel nodata-default" ng-cloak ng-if="top_user_pages.length==0">
            <div class="nodata-text">
                <span class="nodat-circle sm shadow">
                    <i class="ficon-smiley"></i>
                </span>
                <p class="no-margin">No pages created <br>by you</p>
            </div>
        </div>
        <ul class="listing-group suggest-list">
            <li ng-repeat="user_pages in top_user_pages|limitTo:3">
                <div class="list-items-xmd">
                    <div class="list-inner">
                        <figure>
                            <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard" href="page/{{user_pages.PageURL}}"><img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"  ></a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs"><a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="ellipsis conv-name loadbusinesscard" href="<?php echo base_url();?>page/{{user_pages.PageURL}}">{{::user_pages.Title}} </a></h4>
                            <div>
                                <small class="location" ng-if='user_pages.NoOfFollowers == 1'>{{::user_pages.NoOfFollowers+' Follower'}}</small>
                                <small class="location" ng-if='user_pages.NoOfFollowers > 1'>{{::user_pages.NoOfFollowers+' Followers'}}</small>
                            </div>
                        </div>
                        <button class="btn btn-default follow-btn" ng-click='toggleFollowPage(user_pages.PageID);'><i class="ficon-plus"></i> <span ng-bind="(user_pages.FollowStatus==0) ? 'Follow' : 'Unfollow' ;"></span></button>
                    </div>
                </div>
            </li> 
        </ul>
    </div> 
</div>
<!-- New -->


<?php  if ($this->session->userdata('UserID') == $UserID) { ?>
<div ng-cloak class="panel panel-widget" ng-show="entities_i_follow.length>0">
    <div class="panel-heading">
        <h3 class="panel-title">I FOLLOW</h3>
    </div>
   <div class="panel-body no-padding">
            <ul class="list-items-hovered list-items-borderd">
            <li ng-repeat="user_pages in entities_i_follow|limitTo:5">
                <div class="list-items-xmd">
                    <div class="list-inner">
                        <figure>
                            <a><img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="{{ImageServerPath}}upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"   /></a>
                            
                        </figure>
                         <div class="list-item-body">
                            <h4 class="list-heading-xs ellipsis"><a target="_self" ng-if="user_pages.ModuleID=='3'" ng-href="<?php echo site_url() ?>{{user_pages.ProfileUrl}}"  ng-bind="user_pages.FirstName+' '+user_pages.LastName"></a></h4>
                            <h4 class="list-heading-xs ellipsis"><a target="_self" ng-if="user_pages.ModuleID=='18'" ng-href="<?php echo site_url('page') ?>/{{user_pages.ProfileUrl}}" ng-bind="user_pages.FirstName+' '+user_pages.LastName"></a></h4> 
                            <div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
<?php } ?>

<?php endif; ?>



<div class="panel panel-widget">
  <div>
    <div ng-if="recentActivitiesCount>0">
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