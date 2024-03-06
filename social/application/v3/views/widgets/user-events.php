<div ng-cloak class="panel panel-default" ng-controller="EventPopupFormCtrl" ng-if="SettingsData.m14==1" ng-init="getUpcomingEvents()" ng-show="upcomingEvents.length>0">
    <div class="panel-heading p-heading">
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
        <h3>{{::lang.w_upcoming_event}} <a target="_self" href="<?php echo site_url('events') ?>" class="pull-right text-off" ng-bind="lang.see_all"></a></h3>
        <?php } else { ?>
        <h3><span class="capt" ng-bind="FirstName+' is attending'"></span></h3>
        <?php } ?>
    </div>
    <div class="panel-body">
        <ul class="list-group">
            <li ng-repeat="event in upcomingEvents">
                <div class="upcoming-event" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{event.ProfilePicture}})'}">
                    <div class="event-desc">
                        <div class="event-inner">
                            <h4><a target="_self" ng-href="<?php echo site_url('events') ?>/{{event.EventGUID}}/wall" ng-bind="event.Title"></a> </h4>
                            <div ng-bind="'Hosted by '+event.FirstName+' '+event.LastName"></div>
                            <div><span ng-bind="getEventDate(event.StartDate,event.StartTime)"></span> <span ng-bind="getEventTime(event.StartDate,event.StartTime)"></span> - <span ng-bind="getEventDate(event.EndDate,event.EndTime)"></span> <span ng-bind="getEventTime(event.EndDate,event.EndTime)"></span></div>
                            <div ng-bind="'at '+event.Location.FormattedAddress"></div>
                            <div class="button-wrap-sm btn-group">
                                <button class="btn btn-default btn-xs" ng-click="UpdateUsersPresence('ATTENDING', 'Attending',event.EventGUID); event.EventStatus='ATTENDING'" ng-if="event.EventStatus=='' || (event.EventStatus!=='ATTENDING' && event.EventStatus!=='MAY_BE')"><span ng-bind="lang.attend_now"></span></button>
                                <button ng-if="event.EventStatus!==''" class="btn btn-default btn-xs  dropdown-toggle" data-toggle="dropdown">
                                    <!-- <span class="text" ng-if="event.EventStatus=='MAY_BE'">Maybe</span> -->
                                    <span class="text" ng-if="event.EventStatus=='ATTENDING'" ng-bind="lang.w_attending"></span>
                                    <!-- <span class="text" ng-if="event.EventStatus!=='ATTENDING' && event.EventStatus!=='MAY_BE'" ng-bind="event.EventStatus">Attend</span>
                                    <i class="caret"></i> -->
                                </button>
                                <!-- <ul class="dropdown-menu">
                                    <li><a target="_self" ng-click="UpdateUsersPresence('ATTENDING', 'Attending',event.EventGUID); event.EventStatus='ATTENDING'">Attending</a></li>
                                    <li><a target="_self" ng-click="UpdateUsersPresence('MAY_BE', 'May Be',event.EventGUID); event.EventStatus='MAY_BE'">Maybe</a></li> 
                                </ul> -->
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>