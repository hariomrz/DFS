<div class="modal fade inviteModal" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title">Invite groups and friends</h4>
            </div>
            <div class="modal-body no-padding-h">
                <div class="nav-tabs-default">
                    <ul  class="nav nav-tabs nav-tabs-liner nav-tabs-scroll" role="tablist">
                        <li class="active"><a href="#groups" data-toggle="tab">Groups <span  ng-bind="GITotalRecords"></span></a></li>
                        <li><a href="#friends" data-toggle="tab">Friends <span ng-bind="UITotalRecords"></span></a></li>

                    </ul>
                </div>

                <!-- tab contents begins here-->
                <div class="tab-default-content">
                    <div class="tab-content">
                        <div role="groups" class="tab-pane active" id="groups">
                            <div class="tabcontent-heading">              
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group" ng-show="GroupsForInvite.length > 0">
                                            <label class="checkbox">
                                                <input id="SelectAllGroup" type="checkbox" value="1" >
                                                <span class="label">Select All</span>
                                            </label>                                                                                        
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="tabcontent-right-action">
                                            <ul class="action-list">
                                                <li>
                                                    <div class="input-search form-control right">
                                                        <input ng-keyup="get_groups_for_invite(current_poll_guid, 1);"  type="text" id="group-search" name="srch-filters" placeholder="Quick Search" class="form-control">
                                                        <div class="input-group-btn">
                                                            <button class="btn">
                                                                <i class="ficon-search" ></i>
<!--                                                                <i ng-if="groupInviteSrch" ng-click="clearGroupInvite(current_poll_guid, 1)" class="ficon-cross ng-scope"></i>-->
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>                                   
                                </div>
                            </div>

                            <div class="tabcontent-body">
                                <div class="mCustomScrollbar designer-scroll">
                                    <ul class="row list-items-checkbox list-group-inline">
                                        <li class="items col-sm-6"  ng-repeat="groups in GroupsForInvite">
                                            <label class="checkbox" for="g-{{groups.ModuleEntityGUID}}">
                                                <input class="groupchk" id="g-{{groups.ModuleEntityGUID}}" type="checkbox" value="{{groups.ModuleID + ' - ' + groups.ModuleEntityGUID}}">
                                                <span class="label">&nbsp;</span>
                                            </label>
                                            <div class="list-items-sm">
                                                <div class="list-inner">                                  
                                                    <figure>
                                                        <a href="javascript:void(0);"><img   class="img-circle" err-SRC="{{AssetBaseUrl + 'img/profiles/user_default.jpg' }}" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + groups.GroupImage}}"></a>
                                                    </figure>
                                                    <div class="list-item-body">                                
                                                        <h4 class="list-heading-xs"><a class="ellipsis" ng-bind="groups.GroupName"> </a></h4>
                                                        <div><small>by <a ng-bind="groups.CreatedBy"></a></small></div>                                  
                                                        <ul class="list-activites">                               
                                                            <li>
                                                                <span class="icon">
                                                                    <i class="ficon-friends"></i>
                                                                </span>
                                                                <span class="text" ng-bind="groups.MemberCount + ' Members'"></span>
                                                            </li>                                   
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                        <li ng-if="GroupsForInvite.length == 0">
                                            <div class="list-items-sm">
                                                <div class="list-inner">  
                                                    No group
                                                </div>
                                            </div>
                                        </li>

                                    </ul>
                                </div>                                
                            </div>                            

                            <div class="tabcontent-footer" ng-if="GITotalRecords > GroupsForInvite.length">
                                <a class="loadmore">
                                    <span class="text" ng-click="get_groups_for_invite(current_poll_guid)">Load more</span>
                                    <span  ng-class="(groupInviteLoading) ? 'loader' : ''">&nbsp;</span>
                                </a>
                            </div>

                        </div>


                        <div role="friends" class="tab-pane" id="friends">

                            <div class="tabcontent-heading">              
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group" ng-show="UsersForInvite.length > 0">
                                            <label class="checkbox">
                                                <input id="SelectAllUser" type="checkbox" value="1">
                                                <span class="label">Select All</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="tabcontent-right-action">
                                            <ul class="action-list">
                                                <li>
                                                    <div class="input-search form-control right">
                                                        <input ng-keyup="get_users_for_invite(current_poll_guid, 1);" type="text" id="user-search" name="srch-filters" placeholder="Quick Search" class="form-control">
                                                        <div class="input-group-btn">
                                                            <button class="btn">
                                                                <i class="ficon-search"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>




                            <div class="tabcontent-body">
                                <div class="mCustomScrollbar designer-scroll">
                                    <ul class="row list-items-checkbox list-group-inline">
                                        <li class="items col-sm-6"  ng-repeat="users in UsersForInvite">
                                            <label class="checkbox">
                                                <input class="userchk" id="u-{{users.ModuleEntityGUID}}" type="checkbox" value="{{'3 - ' + users.ModuleEntityGUID}}">
                                                <span class="label">&nbsp;</span>
                                            </label>
                                            <div class="list-items-sm">
                                                <div class="list-inner">                                  
                                                    <figure>
                                                        <a href="javascript:void(0);"><img err-SRC="{{AssetBaseUrl + 'img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + users.ProfilePicture}}"   class="img-circle" /></a>
                                                    </figure>
                                                    <div class="list-item-body">                                
                                                        <h4 class="list-heading-xs"><a class="ellipsis" ng-bind="users.FirstName + ' ' + users.LastName"> </a></h4>
                                                        <div>                                                        
                                                            <small ng-cloak ng-if="users.CityName !== '' && users.CountryName == ''" ng-bind="users.CityName" ></small> 
                                                            <small ng-cloak ng-if="users.CityName == '' && users.CountryName !== ''" ng-bind="users.CountryName" ></small> 
                                                            <small ng-cloak ng-if="users.CityName !== '' && users.CountryName !== ''" ng-bind="users.CityName + ', ' + users.CountryName" ></small> 
                                                        </div>                                  

                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                        <li ng-if="UsersForInvite.length == 0">
                                            <div class="list-items-sm">
                                                <div class="list-inner">  
                                                    No friend
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tabcontent-footer" ng-if="UITotalRecords > UsersForInvite.length">
                                <a class="loadmore">
                                    <span class="text" ng-click="get_users_for_invite(current_poll_guid)">Load more</span>
                                    <span  ng-class="(userInviteLoading) ? 'loader' : ''">&nbsp;</span>
                                </a>
                            </div>



                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- <a class="pull-left m-t-5">4 Selected</a> --> 
                <button type="submit" class="btn btn-primary pull-right" data-dismiss="modal" ng-click="invite_entity_for_polls()">DONE</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="current_poll_guid" value="" />

<input type="hidden" id="UIPageNo" value="1" />
<input type="hidden" id="GIPageNo" value="1" />
