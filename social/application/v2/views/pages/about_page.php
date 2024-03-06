<!-- New -->
<div class="panel panel-widget">
  <div class="panel-heading">
    <h3 class="panel-title">                       
      <span class="text" ng-bind="lang.about"></span>
    </h3>        
  </div>
  <div class="panel-body">
    <p>{{pageDetails.Description|limitTo:DescriptionLimit}} <a ng-if="pageDetails.Description.length > DescriptionLimit" ng-click="showMoreDesc(pageDetails.Description.length)" ng-bind="lang.g_see_more_dots"></a></p>
  </div>
</div>
<!-- New -->