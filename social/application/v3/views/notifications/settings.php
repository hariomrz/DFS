  <!--Container-->
  <div class="container wrapper">
    <div class="row">
    <!-- Right Wall-->
<?php $this->load->view('settings/sidebar') ?>
<!-- //Right Wall-->
      <!-- Left Wall-->
      <aside class="col-md-8 col-sm-8 col-xs-12" ng-controller="NotificationCtrl">
     
 <div class="panel panel-default fadeInDown" ng-cloak>
    <div class="panel-heading notification-header  border-bottom">
      <h3 class="panel-title">Notification Settings</h3>
      <button class="btn btn-primary btn-sm pull-right" type="button" ng-click="setNotificationSettings();">SAVE</button>
      <button onclick="history.go(-1);" class="btn btn-primary pull-right btn-link" type="button">Cancel</button>
    </div>

    <div class="panel-body" ng-init="getNotificationSettings();">

      <div class="p-settings-header">
        <div class="row">
            <div class="col-xs-6">
              <div class="toggle-checkbox">

                  <input class='toggle' type="checkbox" ng-change="confirmAllNotificationOff()" ng-model="all_notifications" />
                  <label for="">All notifications</label>
              </div>
            </div> 
            <div class="col-xs-3">
              <div class="toggle-checkbox">
                  <input class='toggle' type="checkbox" ng-model="email_notifications" ng-disabled="!all_notifications" />
                  <label for="">Email</label>
              </div>
            </div> 
            <div class="col-xs-3">
              <div class="toggle-checkbox">
                  <input class='toggle' type="checkbox" ng-model="mobile_notifications" ng-disabled="!all_notifications" />
                  <label for="">Mobile</label>
              </div>
            </div>  
        </div>  
      </div>  

      <div class="select-all-header">
        <div class="row">
          <div class="col-xs-6">
              <label>Select all</label>
          </div>
          <div class="col-xs-3"> 
              <div class="checkbox">
                <input id="Email" type="checkbox" ng-model="emailCheckAll" ng-click="checkAllCheckbox('email')" ng-disabled="!email_notifications || !all_notifications" />
                <label for="Email"></label>
              </div>
          </div>
          <div class="col-xs-3">
              <div class="checkbox">
                <input id="Mobile" type="checkbox" ng-model="mobileCheckAll" ng-click="checkAllCheckbox('mobile')" ng-disabled="!mobile_notifications || !all_notifications" />
                <label for="Mobile"></label>
              </div>
          </div>
        </div>
      </div>  
        
        
      <div class="setting-block notifications" ng-repeat="modules in module_settings|orderBy:'DisplayOrder'"  ng-if="modules.ModuleIDEnabled">
        <div class="row " >
          <div class="col-xs-12">
            <div class="toggle-checkbox">
              <input class='toggle' type="checkbox" ng-model="modules.Value" ng-disabled="!all_notifications" />
              <label for="" ng-bind="modules.ModuleName"></label>
            </div>
          </div>
        </div>

        <div ng-cloak class="row" ng-repeat="notification in notification_settings|orderBy:'DisplayOrder'" ng-if="notification.ModuleID==modules.ModuleID && modules.ModuleID!=='29'">
          <div class="col-xs-6" ng-bind="notification.NotificationTypeName"></div>
          <div class="col-xs-3"> 
              <div class="checkbox">
                  <input id="CommentedE-{{notification.NotificationTypeKey}}" class="email-checkbox" ng-disabled="!modules.Value || !email_notifications || !all_notifications" type="checkbox" ng-model="notification.Email">
                  <label for="CommentedE-{{notification.NotificationTypeKey}}"></label>
              </div>
          </div>
          <div class="col-xs-3"> 
               <div class="checkbox">
                  <input id="CommentedM-{{notification.NotificationTypeKey}}" class="mobile-checkbox" ng-disabled="!modules.Value || !mobile_notifications || !all_notifications" type="checkbox" ng-model="notification.Mobile">
                  <label for="CommentedM-{{notification.NotificationTypeKey}}"></label>
              </div>
          </div>
        </div>
      </div>
      
       
      
        
        
     </div>


    <div class="panel-footer privacy-footer">
        <button class="btn btn-primary btn-sm pull-right" type="button" ng-click="setNotificationSettings();">SAVE</button>
        <button class="btn btn-primary   pull-right btn-link"  onclick="history.go(-1);" type="button">Cancel</button>
    </div> 
 </div> 


</aside>
<!-- //Left Wall-->


</div>
</div>
<!--//Container-->