<div class="breadcrumb-fluid">
  <ol ng-cloak="" class="breadcrumb bordered container">
    <li class="breadcrumb-item">
        <a target="_self" ng-href="{{::BaseUrl}}"><span class="icon">
          <i class="ficon-home"></i>
        </span>
        </a>
    </li>     
    <li class="breadcrumb-item" ng-repeat="bradcrumbs_detail in bradcrumbs_details">
        <span class="icon">
          <i class="ficon-arrow-right"></i>
        </span>
        <a target="_self" ng-href="{{::(BaseUrl+bradcrumbs_detail.url)}}" ng-bind="bradcrumbs_detail.label">
        </a>
    </li>
  </ol>
</div>

<input type="hidden" id="breadcrumb_forum_all_types" />