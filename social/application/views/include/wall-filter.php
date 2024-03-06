<div class="filter-sm clearfix">
    <div class="row">
    <div class="col-sm-6">
        <h5 class="pull-left" ng-if="activityData.length>0"> Stories for you </h5>
    </div>
    <div class="col-sm-6">
        <!-- <div class="collapse-feed">
            <i class="icon">
                <svg height="17px" width="17px" class="svg-icons">
                    <use ng-if="config_detail.IsCollapse=='1'" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconExpand'}}"></use>
                    <use ng-if="config_detail.IsCollapse=='0'" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconCollapse'}}"></use>
                </svg>
            </i>
            <span ng-if="config_detail.IsCollapse=='1'" ng-click="toggle_collapse()">Expand All</span>
            <span ng-if="config_detail.IsCollapse=='0'" ng-click="toggle_collapse()">Collapse All</span>
        </div> -->

        <ul class="sort-action">
            <?php if(isset($IsNewsFeed) && $IsNewsFeed=='1'){ ?>
            <li ng-cloak ng-if="!(!config_detail.IsCollapse || ((tr == 0 || (trr == 0 && IsReminder == 1)) && IsSinglePost == 0))">  
                <a class="sort-collapse" ng-click="toggle_collapse()">
                    <span class="icon">
                        <i ng-if="config_detail.IsCollapse=='1'" class="ficon-expand"></i>
                        <i ng-if="config_detail.IsCollapse=='0'" class="ficon-collapse"></i>
                    </span>
                    <span class="text" ng-if="config_detail.IsCollapse=='1'" >Expand All</span>
                    <span class="text" ng-if="config_detail.IsCollapse=='0'" >Collapse All</span>
                </a>
            </li>
            <?php } ?>
            <li ng-cloak ng-hide="(tr == 0 || (trr == 0 && IsReminder == 1)) && IsSinglePost == 0">
                <div class="dropdown-sort">
                    <small class="title">Sort by</small>
                    <div class="dropdown">
                        <a data-toggle="dropdown">
                            <span class="text" ng-if="Filter.sortLabelName" ng-bind="Filter.sortLabelName"></span> 
                            <span class="text" ng-if="!Filter.sortLabelName">Recent Post</span> 
                            <span class="icon"><i class="ficon-arrow-down"></i></span>
                        </a>
                        <ul class="dropdown-menu sort-filter">
                            <li onclick="addActiveClass(this)">
                                <a ng-click="setFilterLabelName('sortLabelName','Recent Post');" onclick="changeFilterSortBy(2,'RecentActivities')">
                                    <span class="label">Recent Post</span>
                                </a>
                            </li>
                            <li onclick="addActiveClass(this)">
                                <a ng-click="setFilterLabelName('sortLabelName','Recent Updated');" onclick="changeFilterSortBy(1,'TopStories')">
                                    <span class="label">Recent Updated</span>
                                </a>
                            </li>
                            <li onclick="addActiveClass(this)">
                                <a ng-click="setFilterLabelName('sortLabelName','Popular');" onclick="changeFilterSortBy(3,'PopularStories')">
                                    <span class="label">Popular</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
</div>