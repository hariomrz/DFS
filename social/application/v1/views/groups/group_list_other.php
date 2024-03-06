<div  data-ng-controller="GroupPageCtrl" id="GroupPageCtrl" ng-init="get_group_categories();UserGUID='<?php echo $ModuleEntityGUID;?>'">
<?php
if (!(isset($IsNewsFeed) && $IsNewsFeed == '1')) {
    $this->load->view('profile/profile_banner');
}
?>
<!--Container-->
<?php $UserGUID =  get_guid_by_id($this->session->userdata('UserID'),3); ?>
<div class="container wrapper">
  <div class="row" ng-cloak> 
    <aside class="col-md-12 col-sm-12 col-xs-12" >
      <div class="panel panel-default page-panel fadeInDown"  ng-init="my_groups('MyGroupAndJoined','All My Groups');" ng-cloak>
        <div class="panel-title border-bottom">
          <div class="row">
              <div class="col-xs-6"><div class="p-t-sm">Groups</div></div>
              <div class="col-xs-6">
                  <div class="pull-right filters-search">
                       <div class="input-group global-search">
                          <input type="text" class="form-control" ng-model="SearchGroupInput" ng-keyup="SearchGroup('');" placeholder="Search" name="srch-filters" id="insearchgrp">
                          <div class="input-group-btn"> 
                            <button class="btn-search" type="button" ng-click="ResetSearch();"> <i class="icon-search-gray"></i> </button>
                          </div>
                      </div>
                  </div> 
              </div>
          </div>
        </div>
        <div class="panel-body" >
          <div class="padding-inner">
          <ul class="list-group thumb-68 member-listing">
            <li class="col-sm-6" id="grp{{list.GroupGUID}}" ng-repeat="list in MyGrouplist" ng-hide="list.length>0" ng-cloak>
              <figure ng-if="list.Type=='FORMAL'"> 
                  <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="{{site_url + list.ProfileURL}}">
                    <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle"  > 
                  </a> 
              </figure>
              <figure ng-if="list.Type=='INFORMAL'" ng-class="(list.MemberCount>2) ? 'group-thumb' : 'group-thumb-two' ;">
                <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="{{site_url+list.ProfileURL}}">
                    <img ng-if="list.Type=='INFORMAL' && list.ProfilePicture!='' && list.ProfilePicture!='group-no-img.jpg'" ng-src="{{ImageServerPath}}upload/profile/220x220/{{list.ProfilePicture}}" class="img-circle"  >
                        <span ng-repeat="recipients in list.EntityMembers" class="ng-scope">
                          <img  ng-src="{{ImageServerPath}}upload/profile/220x220/{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index<=2" class="ng-scope">
                        </span>                                          
                  </a> 
              </figure>
              <div class="description"> 
                  <a target="_self" entitytype="group" ng-if="list.Type=='FORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{site_url+list.ProfileURL}}" >{{list.GroupName}} <span class="group-secure"> <i class="icon-n-global" ng-if="list.IsPublic==1"></i> <i class="icon-n-closed" ng-if="list.IsPublic==0"></i> <i class="icon-n-group-secret" ng-if="list.IsPublic==2"></i> </span> 
                  </a>
                  <a target="_self" entitytype="group" ng-if="list.Type=='INFORMAL'" entityguid="{{list.GroupGUID}}" class="name a-link loadbusinesscard" href="{{site_url+list.ProfileURL}}" >
                  <span ng-repeat="Member in list.EntityMembers"><span ng-bind="Member.FirstName" ng-if="$index<=2"></span><span ng-if="$index<2 && list.EntityMembers.length>=3">,</span><span ng-if="$index<(list.EntityMembers.length-1) && list.EntityMembers.length<3">,</span> </span>
                  <span ng-if="list.EntityMembers.length>3">and {{list.EntityMembers.length-3}} others</span>
                  <span class="group-secure"> <i class="icon-n-global" ng-if="list.IsPublic==1"></i> <i class="icon-n-closed" ng-if="list.IsPublic==0"></i> <i class="icon-n-group-secret" ng-if="list.IsPublic==2"></i> </span> 
                  </a>
                  <ul class="activity-nav cat-sub-nav">
                      <li>
                          <span class="cat-name" ng-bind="list.Category.Name"></span>
                      </li>
                      <li><span class="cat-name" ng-if="list.Category.SubCategory.Name" ng-bind="list.Category.SubCategory.Name"></span></li>
                      <li>
                          <span class="icon group-activity-lavel heigh" ng-if="list.ActivityLevel=='High'" tooltip  data-placement="top" title="Activity Level : High">
                            <svg width="13px" height="9px"  class="svg-icons no-hover">
                              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconGrouppactivity'}}"></use>
                            </svg>
                          </span>
                          <span class="icon group-activity-lavel moderate" ng-if="list.ActivityLevel=='Low' || list.ActivityLevel=='Moderate'" tooltip  data-placement="top" title="Activity Level : Moderate">
                            <svg width="13px" height="9px"  class="svg-icons no-hover">
                              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconGrouppactivity'}}"></use>
                            </svg>
                          </span>
                      </li>
                  </ul>
                  <div>{{list.GroupDescription|limitTo:DescriptionLimit}} <span ng-if="list.GroupDescription.length > DescriptionLimit"> ...</span></div>
                  <span class="location"><span ng-if="list.Members.length>0" ng-repeat="Member in list.Members"><a target="_self" class="darkgray-clr" ng-href="{{site_url}}{{list.ProfileURL}}" ng-bind="Member.FirstName+' '+Member.LastName"></a><span ng-if="($index+1)<list.Members.length">, </span></span> <span ng-if="(list.Members.length)>0">and</span> {{list.MemberCount-list.Members.length}} <span ng-if='(list.MemberCount-list.Members.length)==1'><?php echo lang('member');?></span> <span ng-if='(list.MemberCount-list.Members.length)>1'><?php echo lang('members');?></span></span>
                  <div class="button-wrap-sm" ng-controller="GroupMemberCtrl">
                            <div class="btn-group" ng-cloak ng-if="list.Permission.IsActiveMember == 1 && list.Permission.DirectGroupMember == 1 ">
                                <span>
                                                <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle" type="button"> <span class="text"><?php echo lang('joined'); ?></span> <i class="caret"></i> </button>
                                <ul role="menu" class="dropdown-menu">
                                    <li>
                                        <a target="_self" href="javascript:void(0);" ng-click='groupDropOutAction(list.GroupGUID, "OtherUserProfile")'>
                                            <?php echo lang('leave_group'); ?>
                                        </a>
                                    </li>
                                </ul>
                                </span>
                            </div>
                            <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 ">
                                <span>
                                                    <button aria-expanded="false" class="btn btn-default btn-xs" type="button" ng-click="joinPublicGroup(list.GroupGUID, 'OtherUserProfile');"> <span class="text"><?php echo lang('join_group'); ?></span> </button>
                                </span>
                            </div>
                            <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited == false && list.Permission.IsActiveMember == false && list.IsPublic ==0 ">
                                <span ng-if="list.Permission.IsInviteSent">
                                                <button aria-expanded="false" class="btn btn-default btn-xs" type="button" ng-click="cancelInvite(list.GroupGUID,'OtherUserProfile');"> <span class="text">Cancel Request</span> </button>
                                </span>
                                <span ng-if="!list.Permission.IsInviteSent">
                                                <button aria-expanded="false" class="btn btn-default btn-xs" type="button" ng-click="requestInvite(list.GroupGUID,'OtherUserProfile');"> <span class="text">Request Invite</span> </button>
                                </span>
                            </div>
                            <div class="btn-group" ng-cloak ng-if="list.Permission.IsInvited == 1  ">
                                <span>
                                                    <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle" type="button"> <span class="text"><?php echo lang('accept') ?></span> <i class="caret"></i> </button>
                                <ul role="menu" class="dropdown-menu">
                                    <li>
                                        <a target="_self" ng-click="groupAcceptDenyRequest(list.GroupGUID, '2', 'OtherUserProfile')">
                                            <?php echo lang('accept') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a target="_self" ng-click="groupAcceptDenyRequest(list.GroupGUID, '13', 'OtherUserProfile')">
                                            <?php echo lang('deny') ?>
                                        </a>
                                    </li>
                                </ul>
                                </span>
                            </div>
                        </div>
              </div>
              <!-- <a target="_self" href="javascript:void(0);" ng-if='list.ModuleRoleID=="4"' class="remove" ng-click="groupDelete(list.GroupGUID,'Delete','')" > <i class="ficon-cross"></i></a>
              <a target="_self" href="javascript:void(0);" ng-if='list.ModuleRoleID=="6"' class="remove" ng-click='groupDropOutAction(list.GroupGUID)'><i class="ficon-cross"></i></a> -->
              </li>
          </ul>
        </div>
        </div>
        <div ng-if='searchKey!="" && MyGrouplist.length==0 && TotalRecordsMyGroup!=0' class="blank-block group-blank" ng-cloak>
          <div class="row">
              <div class="col-sm-8 col-xs-10">
                  <img ng-src="{{AssetBaseUrl}}img/group-no-img.jpg"   class="img-circle">
                   <p class="m-t-15"><?php echo lang('no_record'); ?></p>
              </div>
           </div>
        </div>

        <div class="panel-body nodata-panel" ng-show='TotalRecordsMyGroup==0' ng-cloak>
          <div class="nodata-text">
              <span class="nodata-media">
                  <img src="{{AssetBaseUrl}}img/empty-img/empty-other-no-groups-joined.png" >
              </span>
              <p class="text-off">
                <span ng-bind="FirstName"></span> {{lang.others_no_group_message}}
              </p>
          </div>
        </div>

        <div class="panel-bottom" ng-hide='MyGrouplist.length>=TotalRecordsMyGroup'>
          <button type="button" data-ng-click="LoadMoreMyGroups()" class="btn  btn-link">Load More <span><i class="caret"></i></span></button>
        </div>
      </div>
    </aside>
  </div>
</div>
<!--//Container--> 
</div>
<!--Hidden feilds -->
<input type="hidden" id="OffsetMyGroup" value="0">
<input type="hidden" id="OffsetJoin" value="0">
<input type="hidden" id="OffsetInvited" value="0">
<input type="hidden" id="OffsetManage" value="0">
<input type="hidden" id="LimitMyGroup" value="8">
<input type="hidden" id="LimitJoin"  value="8">
<input type="hidden" id="LimitInvited" value="8">
<input type="hidden" id="LimitManage" value="8">
<input type="hidden" id="TotalRecordsMyGroup" value="0">
<input type="hidden" id="TotalRecordsJoined"  value="0">
<input type="hidden" id="TotalRecordsInvited" value="0">
<input type="hidden" id="TotalRecordsSuggested" value="0">
<input type="hidden" id='OrderBy' value="">
<input type="hidden" id="hdnQuery" value="">
<input type="hidden" id="pageType" value="<?php echo $this->session->userdata('CurrentSection'); ?>">
<input type="hidden" id="searchgrp" value="">
<input type="hidden" id="hdncrdtype" value="">
<input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />
<input type="hidden" id="GroupListPageNo" value="1" />
<input type="hidden" id="unique_id" value="" />
<input type="hidden" id="UserGUID" value="<?php echo $UserGUID; ?>" />
<input type="hidden" id="fromList" value="true">