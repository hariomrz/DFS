<div ng-controller="GroupCtrl" id="GroupCtrl">
  <span ng-view=""></span>
  <!--Container-->
  <div class="nav-tab-fixed">
    <div class="nav-tab-nav">
      <!-- // secondary-nav -->
      <div class="container">
        <div class="nav-tab-filter">
          <div class="row">
            <div class="col-sm-12 col-xs-9">
              <ul class="nav nav-tabs nav-tabs-liner primary nav-tabs-scroll" role="tablist">
                <li ng-if="LoginSessionKey!==''" class="group-tab" id="TabMyGroup" role="presentation" ng-class="(currentPage=='mygroup') ? 'active' : '' ;"><a target="_self" ng-href="{{SiteURL+'group'}}" ng-bind="lang.g_my_groups" ng-click="redirectToUrl('group'); loadMyGroups()" data-target="#myGroups" role="tab" data-toggle="tab"></a></li>
                <li class="group-tab" id="TabDiscover" role="presentation" ng-class="(currentPage=='discover') ? 'active' : '' ;"><a target="_self" ng-href="{{SiteURL+'group/discover'}}" ng-bind="lang.g_discover" ng-click="redirectToUrl('group/discover'); loadDiscover()" data-target="#groupDiscover" role="tab" data-toggle="tab"></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container wrapper">
    <div class="tab-content">
      <div ng-if="LoginSessionKey!==''" role="tabpanel" class="tab-pane" ng-class="(currentPage=='mygroup') ? 'active' : '' ;" id="myGroups">
        <div ng-include="my_group"></div>
      </div>
      <div  role="tabpanel" class="tab-pane" ng-class="(currentPage=='discover') ? 'active' : '' ;" id="groupDiscover">        
        <div ng-include="discover"></div>
      </div>
    </div>
  </div>
  <!--//Container-->
</div>