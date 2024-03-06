<div class="panel panel-default">
    <div class="panel-heading panel-desk">
        <div class="panel-title">
            <span>
              <svg height="14px" width="14px" class="svg-icons">
                <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMyDesk'}}"></use>
              </svg>
            </span>  
            {{::lang.w_my_desk}}
            <ul class="feed-nav">
                <li class="dropdown" ng-class="{ 'open' : IsMyDeskTab }">
                    <a target="_self" ng-if="IsMyDeskTab" data-toggle="dropdown" aria-expanded="false" ng-click="setFilterValues();">
                        <!--applyMyDeskFilter();-->
                        <svg height="18px" width="18px" class="svg-icons">
                            <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnSetting'}}"></use>
                        </svg>
                        <span ng-if="( checkedMyDeskFiltersCount > 0 )" class="badge-xs" ng-bind="checkedMyDeskFiltersCount"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-fixed filters-dropdown" data-type="stopPropagation">
                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-model="myDeskTabFilter.All" ng-true-value="1" ng-false-value="0" ng-change="IsFirstMyDesk = true; verifyAllMyDeskFiltersStatus();">
                                <span class="label" ng-bind="lang.w_all"></span>
                            </label>
                        </li>
                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-model="myDeskTabFilter.NotifyMe" ng-true-value="1" ng-false-value="0" ng-change="IsFirstMyDesk = true; verifyMyDeskFiltersStatus();" ng-checked="( ( myDeskTabFilter.NotifyMe === 1 ) || ( myDeskTabFilter.All === 1 ) )">
                                <span class="label" ng-bind="lang.w_notified"></span>
                            </label>
                        </li>
                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-model="myDeskTabFilter.Mention" ng-true-value="1" ng-false-value="0" ng-change="IsFirstMyDesk = true; verifyMyDeskFiltersStatus();" ng-checked="( ( myDeskTabFilter.Mention === 1 ) || ( myDeskTabFilter.All === 1 ) )">
                                <span class="label" ng-bind="lang.w_mentioned"></span>
                            </label>
                        </li>
                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-model="myDeskTabFilter.WatchList" ng-true-value="1" ng-false-value="0" ng-change="IsFirstMyDesk = true; verifyMyDeskFiltersStatus();" ng-checked="( ( myDeskTabFilter.WatchList === 1 ) || ( myDeskTabFilter.All === 1 ) )">
                                <span class="label" ng-bind="lang.w_watch_list"></span>
                            </label>
                        </li>
                        <li ng-if="SettingsData.m28==1" ng-cloak>
                            <label class="checkbox">
                                <input type="checkbox" ng-model="myDeskTabFilter.Reminder" ng-true-value="1" ng-false-value="0" ng-change="IsFirstMyDesk = true; verifyMyDeskFiltersStatus();" ng-checked="( ( myDeskTabFilter.Reminder === 1 ) || ( myDeskTabFilter.All === 1 ) )">
                                <span class="label" ng-bind="lang.w_reminders"></span>
                            </label>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="toggle-checkbox pull-right">
                <input class="toggle" type="checkbox" ng-model="IsMyDeskTab" ng-change="resetFilterValues();">
                <label for=""></label>
            </div>
        </div>
    </div>
</div>