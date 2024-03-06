<div ng-cloak ng-if="SettingsData.m28==1" ng-show="( (IsReminder == 1 ) && (IsFilePage != 1))" class="panel panel-default" ng-cloak>
  <div class="panel-body"> 
  		<div class="panel-heading p-heading">
      		<h3 ng-bind="lang.w_reminder_calendar"></h3>
      	</div>
    	<div class="datePicker reminders"> 
          <div class="reminder-calendar">
              <div id="StoredReminderCal"></div>
          </div>
      </div>
  </div>
</div>