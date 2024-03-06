<div class="hidden-xs">
    <?php 
    if(!$this->settings_model->isDisabled(10)) {
        
        // Parameter to pervent slick slider break
        $this->disablePeopleYouMayKnowAPI = 1;
        
        $this->load->view('widgets/people-you-may-know'); 
    } else {
        $this->load->view('widgets/people-you-may-follow'); 
    }    
    ?>
    
    <?php if(!$this->settings_model->isDisabled(1)): // Check if group module is enabled ?>
 
    <div ng-cloak class="panel panel-widget" ng-show="suggestedlist.length>0 && !IsMyDeskTab">
        <div class="panel-heading">
            <h3 class="panel-title">
                <a target="_self" ng-click="loadCreateGroup();createGroup();" class="link" ng-bind="lang.create"></a>
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
                                <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>{{list.ProfileURL}}"> <img ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  > </a>
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
                                        <i class="ficon-trending"></i>
                                    </span>
                                </li>
                                <li>
                                    <span class="icon" data-toggle="tooltip" data-original-title="Public" ng-if="list.IsPublic!=='' && list.IsPublic==1">
                                        <i class="ficon-globe" ></i>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li> 
            </ul>
        </div> 
    </div>
    <?php endif; ?>


    <?php 
    if(!$this->settings_model->isDisabled(18)) { // Check if page module is enabled
        $this->load->view('widgets/suggested-pages'); 
    } 
    ?>    
    
</div>

 <?php if(!$this->settings_model->isDisabled(14)): // Check if event module is enabled ?>
<div ng-cloak class="panel panel-widget" id="EventPopupFormCtrl" ng-show="eventNearYou.length>0 && !IsMyDeskTab">
    <div class="panel-heading">
        <h3 class="panel-title"><a target="_self" ng-cloak ng-if="LoginSessionKey" href="<?php echo site_url('events') ?>" class="link" ng-bind="lang.see_all"></a><span class="text" ng-bind="lang.w_event_near_you"></span></h3>
    </div>
    <div class="panel-body no-padding">    
        <div ng-repeat="event in eventNearYou">
            <div class="upcoming-event" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{event.ProfilePicture}})'}">
                <div class="event-desc">
                    <div class="event-inner">

                        <h4><a target="_self" ng-href="<?php echo site_url() ?>{{event.ProfileURL}}" ng-bind="event.Title"></a> </h4>

                        <div ng-bind="'Hosted by '+event.FirstName+' '+event.LastName"></div>
                        <div><span ng-bind="getEventDate(event.StartDate,event.StartTime)"></span> <span ng-bind="getEventTime(event.StartDate,event.StartTime)"></span> - <span ng-bind="getEventDate(event.EndDate,event.EndTime)"></span> <span ng-bind="getEventTime(event.EndDate,event.EndTime)"></span></div>
                        <div ng-bind="'at '+event.Location.FormattedAddress"></div>
                        <div class="button-wrap-sm btn-group">
                            <button class="btn btn-default btn-xs" ng-if="event.EventStatus=='ATTENDING'"><span ng-bind="lang.w_attending"></span></button>
                            <button class="btn btn-default btn-xs" ng-if="event.EventStatus!=='ATTENDING' && event.EventStatus!=='MAY_BE'" ng-click="UpdateUsersPresence('ATTENDING', 'Attending',event.EventGUID); event.EventStatus='Attending'"><span ng-bind="lang.attend_now"></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
