<div ng-init="loadPopularTags()" ng-cloak="">
    <div class="panel panel-striped" ng-if="popularTags && popularTags.length" >
        <div class="panel-heading">
            <h3 class="panel-title"><span class="text" ng-bind="lang.w_popular_tags"></span></h3>
        </div>
        <div class="panel-body">

            <ul class="tag-info">
                <li class="item-tag" ng-repeat="(tagKey, tagVal) in popularTags">
                    <a target="_self" class="tag-text" ng-bind="tagVal.Name" ng-click="filterByPopularTags(tagVal);"></a>
                </li>
            </ul>
        </div>
    </div>
</div>