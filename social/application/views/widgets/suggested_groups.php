<div ng-init="suggestedGroupList(3,0,0)" ng-cloak class="panel panel-widget" ng-show="suggestedlist.length>0">
    <div class="panel-heading">
        <h3 class="panel-title">
            <a target="_self" ng-cloak ng-if="LoginSessionKey!==''" data-toggle="modal" data-target="#createGroup" class="link" ng-click="CreateEditGroup('createGroup'); setGroupPopup(true);" ng-bind="lang.create"></a>
            <a target="_self" ng-cloak ng-if="LoginSessionKey==''" class="link" ng-click="showLoginPopup();" ng-bind="lang.create"></a>
            <span class="text" ng-bind="lang.w_suggested_groups"></span> 
        </h3>
    </div>
    <div class="panel-body no-padding">
        <ul class="list-items-hovered list-items-borderd">
            <li id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = suggestedlist|limitTo:3" ng-cloak>
                <div class="list-items-xmd">
                    <div ng-click="joinPublicGroup(list.GroupGUID,'discover')" class="actions">
                        <button class="btn btn-default btn-sm" ng-bind="lang.w_join_caps"></button>
                    </div>    
                    <div class="list-inner">
                        <figure>
                            <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}"> <img ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  > </a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs"><a target="_self" entitytype="page" entityguid="{{suggestion.PageGUID}}" class="ellipsis loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" ng-bind="list.GroupName"></a></h4>
                            <div>
                                <small ng-if='list.Members.length>0' ng-repeat='member in list.Members | limitTo:3'>
                                    {{member.FirstName}} {{$last ? '' : ($index==list.Members.length-1) ? '' : ', '}}
                                </small> 
                                <small ng-if='list.Members.length>3'> & {{ (list.MemberCount-list.Members.length)>0?list.MemberCount-list.Members.length:'' }} 
                                    <small ng-if="list.MemberCount-list.Members.length>0">
                                        {{ (list.MemberCount-list.Members.length)>1?'Members':'Member' }} 
                                    </small>
                                </small>
                            </div>
                        </div>                        
                        <ul class="list-icons">
                            <li>
                                <span class="icon group-activity-lavel" ng-class="list.Popularity=='High'?'heigh':'moderate'" tooltip data-placement="top" title="Activity Level : {{list.Popularity}}">
                                    <i  class="ficon-trending"></i>
                                </span>
                            </li>
                            <li>
                                <span class="icon" data-toggle="tooltip" data-original-title="Public" ng-if="list.IsPublic!=='' && list.IsPublic==1">
                                    <i  class="ficon-globe"></i>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </li> 
        </ul>
    </div> 
</div>