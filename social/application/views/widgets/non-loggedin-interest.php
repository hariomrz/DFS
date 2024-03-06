<div ng-cloak class="panel panel-transparent" ng-if="SettingsData.m31==1" ng-init="get_popular_interest('');">     
    <div class="panel-heading">
        <h3 class="panel-title text-sm"><span class="text">{{lang.non_loggedin_widget_title}}</span></h3>
    </div>
    <div class="panel-body transparent">
        <div class="form-group">
            <label class="label-control">{{lang.non_loggedin_widget}}</label>
            <div class="checkbox-vartical">          
                <label class="checkbox checkbox-block" ng-repeat="interest in non_loggedin_interest_checked" ng-cloak>
                    <input checked="checked" ng-click="updateNewsFeed(); addToNonChecked(interest.CategoryID)" type="checkbox" class="interest-check" ng-value="interest.CategoryID">
                    <span class="label">{{interest.Name}}</span>
                </label>                     
                <label class="checkbox checkbox-block" ng-repeat="interest in non_loggedin_interest" ng-cloak>
                    <input ng-click="updateNewsFeed(); addToChecked(interest.CategoryID)" type="checkbox" class="interest-check" ng-value="interest.CategoryID">
                    <span class="label">{{interest.Name}}</span>
                </label> 
            </div>
        </div>
        <div class="form-group no-margin">
            <div class="input-search right quick-search">
                <input ng-keyup="get_popular_interest(searchinterest)" ng-model="searchinterest" type="text" name="srch-filters" placeholder="Type to search" class="form-control">
                <div class="input-group-btn">
                    <button class="btn"><i class="ficon-search"></i></button>
              </div>
            </div>
        </div>           
    </div>     
</div>