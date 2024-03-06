<div class="secondary-fixed-nav">
    <div class="secondary-nav">
        <div class="container">
            <div class="row nav-row">
                <div class="col-sm-12 main-filter-nav"> 
                    <nav class="navbar navbar-default navbar-static">
                        <div class="navbar-header visible-xs">
                             <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#filterNav"> 
                                    <span class="icon"><i class="ficon-filter"></i></span>
                                </button>
                        </div>
                        <div class="collapse navbar-collapse" id="filterNav">
                            <ul class="nav navbar-nav filter-nav">
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Location 
                                        <span>
                                            <span ng-if="city_list_checked.length==0">Any City</span>
                                            <span ng-if="city_list_checked.length>0" ng-bind="city_list_checked[0].Name"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                        <li>
                                            <label class="checkbox">
                                                <input type="checkbox" checked="checked" value="0">
                                                <span class="label">Any City</span>
                                            </label>
                                        </li>
                                        <li ng-repeat="city in city_list_checked" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="remove_from_city(city.CityID); callPageList();" type="checkbox" checked="checked" class="search-city" ng-value="city.CityID" value="0">
                                                <span class="label" ng-bind="city.Name"></span>
                                            </label>  
                                        </li>
                                        <li ng-repeat="city in city_list" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="add_to_city(city); callPageList();" type="checkbox" class="search-city" ng-value="city.CityID" value="0">
                                                <span class="label" ng-bind="city.Name"></span>
                                            </label>  
                                        </li>
                                        </ul></li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input ng-keyup="get_cities(city)" ng-model="city" type="text" name="srch-filters" placeholder="Look for more city" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>

                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button">Interest 
                                        <span>
                                            <span ng-cloak ng-if="interest_list_checked.length==0">All Categories</span>
                                            <span ng-cloak ng-if="interest_list_checked.length>0" ng-bind="interest_list_checked[0].Name"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                        <li>
                                            <label class="checkbox">
                                                <input type="checkbox" ng-checked="interest_list_checked.length==0" ng-click="emptyArr('interest_list_checked','interest_list'); callPageList();" value="0">
                                                <span class="label">Any Interest</span>
                                            </label>
                                        </li>
                                        <li ng-repeat="interest in interest_list_checked">
                                            <label class="checkbox">
                                                <input ng-click="remove_from_interest(interest,interest.CategoryID); callPageList();" ng-checked="interest.IsChecked" class="interest-check" type="checkbox" ng-value="interest.CategoryID" >
                                                <span class="label" ng-bind="interest.Name"></span>
                                            </label>
                                            <ul class="sub-categories">
                                                <li ng-repeat="subinterest in interest.Subcategory">
                                                    <label class="checkbox">
                                                        <input ng-checked="subinterest.IsChecked" ng-click="callPageList();" type="checkbox" ng-value="subinterest.CategoryID" class="interest-check">
                                                        <span class="label" ng-bind="subinterest.Name"></span>
                                                    </label>
                                                </li>
                                            </ul>
                                        </li>
                                        <li ng-repeat="interest in interest_list">
                                            <label class="checkbox">
                                                <input ng-click="add_to_interest(interest,interest.CategoryID); callPageList();" class="interest-check" type="checkbox" ng-value="interest.CategoryID" >
                                                <span class="label" ng-bind="interest.Name"></span>
                                            </label>
                                            <ul class="sub-categories">
                                                <li ng-repeat="subinterest in interest.Subcategory">
                                                    <label class="checkbox">
                                                        <input type="checkbox" ng-click="add_to_interest(interest,subinterest.CategoryID); callPageList();" ng-value="subinterest.CategoryID" class="interest-check">
                                                        <span class="label" ng-bind="subinterest.Name"></span>
                                                    </label>
                                                </li>
                                            </ul>
                                        </li>
                                        </ul></li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input type="text" ng-keyup="get_interest(interest)" ng-model="interest" name="srch-filters" placeholder="Look for more Interests" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Sort By 
                                    <span>
                                        <span ng-cloak ng-if="sort_by_label==''">Network</span>
                                        <span ng-cloak ng-if="sort_by_label!==''" ng-bind="sort_by_label"></span>
                                    &nbsp;
                                    </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="active-with-icon dropdown-menu dropdown-menu-left">
                                        <li ng-if="sort_by_label2!=='NameAsc'" ng-class="(sort_by_label2 == 'NameDesc') ? 'active' : ''"><a ng-click="changeSortBy('NameAsc');">By Name</a></li>
                                        <li ng-if="sort_by_label2=='NameAsc'" ng-class="(sort_by_label2 == 'NameAsc') ? 'active' : ''"><a ng-click="changeSortBy('NameDesc');">By Name</a></li>
                                        <li ng-class="(sort_by_label2 == 'Members') ? 'active' : ''"><a ng-click="changeSortBy('Members')">By No. of Members</a></li>
                                        <li ng-class="(sort_by_label2 == 'ActivityLevel') ? 'active' : ''"><a ng-click="changeSortBy('ActivityLevel')">By Activity Level</a></li>
                                        <li ng-class="(sort_by_label2 == 'Recent Updated') ? 'active' : ''"><a ng-click="changeSortBy('Recent Updated');">Recent Updated</a></li>
                                        <li ng-class="(sort_by_label2 == 'Network' || sort_by_label2 == '') ? 'active' : ''"><a ng-click="changeSortBy('');">Network</a></li>
                                        <!-- <li><a>By Ratings</a></li> -->
                                    </ul>
                                </li>
                                
                                <li  ng-cloak="" ng-if="!isDefaultFilterPageSearch()">
                                    <div class="reset-button" >
                                        <button class="btn btn-default" ng-click="ResetFilterPageSearch()">Reset</button>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                    </nav> 

                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="sortby" value="" />
<input type="hidden" id="CurrentPage" value="Page" />