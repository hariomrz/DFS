<div ng-cloak class="panel panel-default" ng-controller="EventPopupFormCtrl" ng-if="SettingsData.m14==1" ng-init="getUpcomingEvents()" ng-show="upcomingEvents.length>0">
    <div class="panel-heading p-heading">
        <h3>{{::lang.w_upcoming_events}} <a target="_self" href="<?php echo site_url('events') ?>" class="pull-right text-off">See All</a></h3>
    </div>
    <div class="panel-body">
        <ul class="list-group">
            <li ng-repeat="event in upcomingEvents">
                <div class="upcoming-event"  ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{::event.ProfilePicture}})'}">
                    <div class="event-desc">
                        <div class="event-inner">
                            <h4><a ng-href="{{event.ProfileURL}}" ng-bind="event.Title" target="_self"></a> </h4>

                            <div>{{::lang.w_hosted_by+' '+event.FirstName+' '+event.LastName}}</div>
                            <div><span ng-bind="getEventDate(event.StartDate,event.StartTime)"></span> <span ng-bind="getEventTime(event.StartDate,event.StartTime)"></span> - <span ng-bind="getEventDate(event.EndDate,event.EndTime)"></span> <span ng-bind="getEventTime(event.EndDate,event.EndTime)"></span></div>
                            <div>{{::lang.w_at+' '+event.Location.FormattedAddress}}</div>
                            <div class="button-wrap-sm btn-group dropup">
                                <button ng-if="event.EventStatus!==''" class="btn btn-default btn-xs  dropdown-toggle" data-toggle="dropdown">
                                    <!-- <span class="text" ng-if="event.EventStatus=='MAY_BE'">Maybe</span> -->
                                    <span class="text" ng-if="event.EventStatus=='ATTENDING'">Attending</span>
                                    <span class="text" ng-click="UpdateUsersPresence('ATTENDING', 'Attending',event.EventGUID);event.EventStatus=='ATTENDING'" ng-if="event.EventStatus!=='ATTENDING' && event.EventStatus!=='MAY_BE'" ng-bind="event.EventStatus"></span>
                                    <!-- <i class="caret"></i> -->
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>