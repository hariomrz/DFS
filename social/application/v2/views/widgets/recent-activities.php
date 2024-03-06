<div class="panel panel-widget" ng-cloak data-ng-controller="UserProfileCtrl">
  <div  ng-init="getRecentActivities()">
    <div ng-if="recentActivitiesCount>0">
      <div class="panel-heading">
        <h3 class="panel-title" ng-bind="lang.recent_activity"></h3>
      </div>
      <div class="panel-body">
        <ul class="list-items-group">
          <li class="items item-activity" ng-repeat="rAct in recentActivities"> <span ng-class="'ra-'+rAct.ActivityGUID" ng-bind-html="rAct.Message"></span> </li>
        </ul>
      </div>
    </div>
  </div>
</div>