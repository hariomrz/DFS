<div ng-cloak class="panel panel-widget" ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" ng-init="getEventNearYou()" ng-show="eventNearYou.length>0">
    <div class="panel-heading">
        <h3 class="panel-title"><a target="_self" ng-cloak ng-if="LoginSessionKey" href="<?php echo site_url('events') ?>" class="link" ng-bind="lang.see_all"></a> <span class="text" ng-bind="lang.w_event_near_you"></span></h3>
    </div>
    <div class="panel-body">
        <div ng-repeat="event in eventNearYou">
            <div class="upcoming-event" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{event.ProfilePicture}})'}">                
                <div class="event-desc">
                    <div class="event-inner">
                        <h4><a target="_self" ng-href="<?php echo site_url() ?>{{event.ProfileURL}}" ng-bind="event.Title"></a> </h4>
                        <div ng-bind="'Hosted by '+event.FirstName+' '+event.LastName"></div>
                        <div>
                            <span ng-bind="getEventDate(event.StartDate,event.StartTime)"></span> 
                            <span ng-bind="event.DisplayStartTime"></span> - <span ng-bind="getEventDate(event.EndDate,event.EndTime)"></span> 
                            <span ng-bind="event.DisplayEndTime"></span>
                        </div>
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