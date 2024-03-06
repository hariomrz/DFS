<div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail();">  
    <?php $this->load->view('profile/profile_banner') ?>
    <div class="container wrapper" id="GroupMemberCtrl" ng-controller="UserListCtrl" ng-cloak>
        <div class="row">
            <div class="col-sm-12" ng-show="MemberView=='Listing'">
                <div class="panel panel-default">
                    <h3 class="panel-title border-bottom">
               <span>
                   {{GroupDetails.TotalMembers}} 
                   <span ng-if="GroupDetails.TotalMembers>1" ng-bind="::lang.members"></span> 
                   <span ng-if="GroupDetails.TotalMembers<2" ng-bind="::lang.member"></span>
               </span>
               <div class="dropdown pull-right" ng-cloak ng-if="GroupDetails.Permission.IsAdmin == true ||  GroupDetails.Permission.IsCreator == true">
                  <button type="button" data-toggle="dropdown" class="btn btn-sm btn-link no-padding">
                  <span class="icon no-margin">
                     <svg height="16px" width="16px" class="svg-icons">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnSetting'}}"></use>
                     </svg>
                  </span>
                  </button>
                  <ul class="dropdown-menu active-with-icon">
                     <li ng-click="ToggleMemberPage('Setting')" >
                         <a target="_self" ng-bind="::lang.member_settings"></a>
                     </li>
                     <li ng-click="ListingType()" ng-class="ActiveSection == 'Permission' ? 'active' :''" >
                         <a target="_self" ng-bind="::lang.group_by_permission"></a>
                     </li>
                  </ul>
               </div>
               </h3>
                    <div class="panel-body">
                        <div class="padding-inner border-bottom" ng-init="showFriendMembers()" ng-cloak ng-if="GroupGUID && LoginSessionKey!==''">
                            <div class="blank-block-loader" ng-if="FrLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label ng-if=":: (Settings.m10 == 1)">                                    
                                    {{::lang.friends}} ({{TotalRecordsFriendMembers}})
                                </label>
                                
                                <label ng-if=":: (Settings.m10 == 0)">
                                    {{::lang.followers}} ({{TotalRecordsFriendMembers}})
                                </label>
                                
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" id="" ng-repeat="list in listObj = ListFriendMembers" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}">
                                                <img err-name="{{list.FirstName+' '+list.LastName}}"   class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + list.ProfilePicture}}">
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name  a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" 
                                                        
                                                        ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);"
                                                        
                                                         ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member"  class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListFriendMembers.length>=TotalRecordsFriendMembers'>
                                    <button type="button" data-ng-click="showFriendMembers()" class="btn  btn-link">
                                        {{::lang.load_more}} <span><i class="caret"></i></span>
                                    </button>
                                </div>
                                <div ng-if='ListFriendMembers.length==0 && FrLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-init="showManagers()" ng-if="GroupGUID">
                            <div class="blank-block-loader" ng-if="MngrLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.admin}}s ({{TotalRecordsManagers}})</label>
                                <ul class="list-group member-listing" ng-cloak>
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" id="" ng-repeat="list in listObj = ListManagers" ng-hide="list.length>0" repeat-done="repeatDoneBCard()">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 

                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description">
                                            <div>
                                                <a target="_self" class="name a-link loadbusinesscard" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-href="{{BaseUrl+list.ProfileURL}}" ng-bind="list.FirstName+'   '+list.LastName"></a>
                                                <span class="location" ng-if='list.ModuleRoleID==4'>Creator</span>
                                                <span class="location" ng-if='list.ModuleRoleID==5'>Admin</span>
                                                <span class="location" ng-bind='list.Location'></span>
                                                <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                    <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                        
                                                    </button>
                                                    <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                        
                                                    </button>
                                                </div>
                                                <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                    <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                        
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if='list.ModuleRoleID==5 && GroupDetails.IsAdmin' ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div ng-if='ListManagers.length==0 && MngrLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-bottom" ng-hide='ListManagers.length>=TotalRecordsManagers'>
                                    <button type="button" data-ng-click="LoadMoreAdmins()" class="btn  btn-link">
                                       {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-if="GroupDetails.Permission.IsAdmin == true && ActiveSection != 'Permission'" ng-init="showPending('init')">
                            <div class="blank-block-loader" ng-if="PenLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.pending_request}} ({{TotalRecordsPendingMembers}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" ng-repeat="list in listObj = ListPendingReq" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" href="{{BaseUrl+list.ProfileURL}}">
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}">
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name  a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="dropdown pages-dropdown">
                                                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-baselink dropdown-toggle" type="button">
                                                    <span ng-cloak ng-if="list.StatusID=='18'" class="text">Request To Join</span>
                                                    <span ng-cloak ng-if="list.StatusID=='1'" class="text">Invited</span> <i class="caret"></i> </button>
                                                <ul role="menu" class="dropdown-menu pull-left">
                                                    <li ng-cloak ng-if="list.StatusID=='18'"><a target="_self" href="javascript:void(0);" ng-click='acceptInvite(GroupGUID ,list.ModuleEntityGUID)'>Accept</a></li>
                                                    <li ng-cloak ng-if="list.StatusID=='18'"><a target="_self" href="javascript:void(0);" ng-click='rejectInvite(GroupGUID ,list.ModuleEntityGUID)'>Deny</a></li>
                                                    <li ng-cloak ng-if="list.StatusID=='1'"><a target="_self" href="javascript:void(0);" ng-click='rejectInvite(GroupGUID,list.ModuleEntityGUID)'>Cancel Invite</a></li>
                                                </ul>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <!--<a target="_self" class="remove" href="javascript:void(0);" ng-if="GroupDetails.IsAdmin"  ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>-->
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListMembers.length>=TotalRecordsMembers'>
                                    <button type="button" data-ng-click="LoadMoreMembers()" class="btn  btn-link">
                                       {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListPendingReq.length==0 && PenLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner" ng-class="ActiveSection == 'Members' ? '' :'border-bottom'" ng-if="GroupGUID && ActiveSection == 'Members'" ng-init="showMembers()">
                            <div class="blank-block-loader" ng-if="MemLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.members}} ({{TotalRecordsMembers}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" ng-repeat="list in listObj = ListMembers" ng-hide="list.length>0">
                                        <figure>

                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 


                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name  a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListMembers.length>=TotalRecordsMembers'>
                                    <button type="button" data-ng-click="LoadMoreMembers()" class="btn  btn-link">
                                       {{::lang.load_more}}  <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListMembers.length==0 && MemLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <h5>{{::lang.no_group_members_heading}}</h5>
                                            <p class="m-t-15">
                                                {{::lang.no_group_members_message}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-cloak ng-if="ActiveSection == 'Permission'">
                            <div class="blank-block-loader" ng-if="CanPostLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.members_can_post}} ({{TotalRecordsCanPost}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" ng-repeat="list in listObj = ListCanPost" ng-hide="list.length>0">
                                        <figure>

                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 

                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                            </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListCanPost.length>=TotalRecordsCanPost'>
                                    <button type="button" data-ng-click="showWhoCanPost()" class="btn  btn-link">
                                        {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListCanPost.length==0 && CanPostLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-cloak ng-if="ActiveSection == 'Permission' && showKB==true">
                            <div class="blank-block-loader" ng-if="knwLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.contributors_to_knowledge_base}} ({{TotalRecordsKnowledgeBase}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" ng-repeat="list in listObj = ListKnowledgeBase" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListKnowledgeBase.length>=TotalRecordsKnowledgeBase'>
                                    <button type="button" data-ng-click="showKnowledgeBase()" class="btn  btn-link">
                                       {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListKnowledgeBase.length==0 && knwLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-cloak ng-if="ActiveSection == 'Permission'">
                            <div class="blank-block-loader" ng-if="cmtLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.members_can_comment}} ({{TotalRecordsCanComment}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" id="" ng-repeat="list in listObj = ListCanComment" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 

                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListCanComment.length>=TotalRecordsCanComment'>
                                    <button type="button" data-ng-click="showWhoCanComment()" class="btn  btn-link">
                                        {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListCanComment.length==0 && cmtLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner border-bottom" ng-cloak ng-if="ActiveSection == 'Permission'">
                            <div class="blank-block-loader" ng-if="ExpertLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.expert}}s ({{TotalRecordsExpert}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" id="" ng-repeat="list in listObj = ListExpert" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ImageServerPath + 'upload/profile/220x220/group-no-img.jpg'}}">
                                                <img   ng-if="( list.ProfilePicture != '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);"  ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListExpert.length>=TotalRecordsExpert'>
                                    <button type="button" data-ng-click="showExpert()" class="btn  btn-link">
                                        {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListExpert.length==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="padding-inner " ng-if="ActiveSection == 'Permission'">
                            <div class="blank-block-loader" ng-if="OtherLoader==1" style="display:block;">
                                <div class="spinner32"></div>
                            </div>
                            <div class="clearfix">
                                <label>
                                    {{::lang.other_members}} ({{TotalRecordsOthers}})</label>
                                <ul class="list-group member-listing">
                                    <li class="col-sm-4 col-md-3 usr{{list.ModuleEntityGUID}}" ng-repeat="list in listObj = ListOthers" ng-hide="list.length>0">
                                        <figure>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 3"  class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   err-name="{{list.FirstName+' '+list.LastName}}" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 

                                            </a>
                                            <a target="_self" entitytype="group" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID == 1" class="loadbusinesscard" href="{{BaseUrl + list.ProfileURL}}"> 
                                                <img   ng-if="( ( list.ProfilePicture == '' ) || ( list.ProfilePicture == 'group-no-img.jpg' ) )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/group-no-img.jpg' }}">
                                                <img   ng-if="( list.ProfilePicture == '' )" class="img-circle" ng-src="{{ ImageServerPath + 'upload/profile/220x220/' +list.ProfilePicture}}"> 
                                            </a>
                                        </figure>
                                        <div class="description" ng-cloak>
                                            <a target="_self" entitytype="{{(list.ModuleID=='3') ? 'user' : 'group' ;}}" entityguid="{{list.ModuleEntityGUID}}" href="{{BaseUrl+list.ProfileURL}}" class="name a-link loadbusinesscard">{{list.FirstName}} {{list.LastName}}
                                 </a>
                                            <span class="location" ng-bind='list.Location'></span>
                                            <div class="button-wrap-sm" ng-if="SettingsData.m10=='1' && list.ShowFriendsBtn==1">
                                                <button ng-click="sendFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '4'" ng-bind="::lang.send_request">
                                                    
                                                </button>
                                                <button ng-click="RejectFriendRequest(list.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="list.FriendStatus == '2'" ng-bind="::lang.cancel_request">
                                                    
                                                </button>
                                            </div>
                                            <div class="button-wrap-sm" ng-if="list.ShowMessageBtn==1 && list.FriendStatus==1">
                                                <button class="btn btn-default btn-xs" ng-click="messageModal(list.FirstName, list.ModuleEntityGUID);" ng-bind="::lang.message">
                                                    
                                                </button>
                                            </div>
                                        </div>
                                        <a target="_self" tooltip data-placement="top" title="Remove Member" class="remove" href="javascript:void(0);" ng-if="list.ModuleRoleID!=4 && GroupDetails.IsAdmin" ng-click='removeGroupMember(GroupDetails.GroupGUID,list.ModuleEntityGUID,list.ModuleID)'><i class="ficon-cross"></i></a>
                                    </li>
                                </ul>
                                <div class="panel-bottom p-b-0" ng-hide='ListOthers.length>=TotalRecordsOthers'>
                                    <button type="button" data-ng-click="showWhoCanComment()" class="btn  btn-link">
                                        {{::lang.load_more}} <span><i class="caret"></i></span></button>
                                </div>
                                <div ng-if='ListOthers.length==0 && OtherLoader==0' class="blank-block group-blank" ng-cloak>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                                            <img ng-src="{{AssetBaseUrl}}img/group-no-img.png"  >
                                            <p class="m-t-15" ng-bind="::lang.no_record">
                                                
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->load->view('groups/group_member_setting');?>
        </div>
    </div>

<input type="hidden" id="ModuleEntityGUID" value="{{GroupGUID}}" />

</div>

