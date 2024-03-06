<div class="panel panel-widget" ng-hide="popular_tags.length==0" ng-cloak>
  <div class="panel-heading">
    <h3 class="panel-title"> 
      <span class="text" ng-bind="lang.w_popular_tags"></span>
    </h3>        
  </div>
  <div class="panel-body">
    <ul class="tag-info">
      <li class="item-tag" ng-repeat="tag in popular_tags">
        <a target="_self" ng-if="LoginSessionKey==''" target="_self" class="tag-text default-cursor" ng-bind="tag.Name"></a>
        <a target="_self" ng-if="LoginSessionKey!=''" target="_self" class="tag-text" ng-bind="tag.Name" ng-click="filterByPopularTags(tag);setFilterFixed(true);"></a>
      </li>
    </ul>
  </div>
</div>