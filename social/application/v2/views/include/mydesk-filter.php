<div class="feed-tab clear-fix"  data-scrollfix="scrollFix">
   	<ul class="tab-menu">
      	<li ng-class="myDeskTabFilter.All=='1' ? 'active' : ''">
            <a ng-click="verifyMyDeskFiltersStatus('All');">All</a>
        </li>
      	<li ng-class="(myDeskTabFilter.All=='0' && myDeskTabFilter.NotifyMe=='1') ? 'active' : ''">
            <a ng-click="verifyMyDeskFiltersStatus('NotifyMe');">Notify</a>
        </li>
      	<li ng-class="(myDeskTabFilter.All=='0' && myDeskTabFilter.Mention=='1') ? 'active' : ''">
            <a ng-click="verifyMyDeskFiltersStatus('Mention');">Mentioned</a>
        </li>
      	<li ng-class="(myDeskTabFilter.All=='0' && myDeskTabFilter.WatchList=='1') ? 'active' : ''">
            <a ng-click="verifyMyDeskFiltersStatus('WatchList');">Watchlist</a>
        </li>
      	<li ng-cloak ng-if="SettingsData.m28==1" ng-class="(myDeskTabFilter.All=='0' && myDeskTabFilter.Reminder=='1') ? 'active' : ''">
            <a ng-click="verifyMyDeskFiltersStatus('Reminder');">Reminder</a>
        </li>
   	</ul>
</div>