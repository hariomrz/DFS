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
                                            <span ng-if="city_list_checked.length == 0">Any City</span>
                                            <span ng-if="city_list_checked.length > 0" ng-bind="city_list_checked[0].Name"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                                <li>
                                                    <label class="checkbox">
                                                        <input type="checkbox" ng-checked="city_list_checked.length == 0" ng-click="emptyArr('city_list_checked', 'city_list'); callEventList();" value="0">
                                                        <span class="label">Any City</span>
                                                    </label>
                                                </li>
                                                <li ng-repeat="city in city_list_checked" ng-cloak>
                                                    <label class="checkbox">
                                                        <input ng-click="remove_from_city(city.CityID); callEventList();" type="checkbox" checked="checked" class="search-city" ng-value="city.CityID" value="0">
                                                        <span class="label" ng-bind="city.Name"></span>
                                                    </label>  
                                                </li>
                                                <li ng-repeat="city in city_list" ng-cloak>
                                                    <label class="checkbox">
                                                        <input ng-click="add_to_city(city); callEventList();" type="checkbox" class="search-city" ng-value="city.CityID" value="0">
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
                                    <a class="" data-toggle="dropdown" role="button"> Date Range 
                                        <span ng-cloak ng-if="sdate == '' && edate == ''">Any</span>
                                        <span ng-cloak ng-if="sdate !== '' && edate == ''" ng-bind="sdate"></span>
                                        <span ng-cloak ng-if="sdate == '' && edate !== ''" ng-bind="edate"></span>
                                        <span ng-cloak ng-if="sdate !== '' && edate !== ''" ng-bind="sdate + ' to ' + edate"></span>
                                    </a>


                                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                                        <li>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label class="control-label">From Date</label>
                                                    <div data-error="hasError" class="text-field date-field">
                                                        <input id="datepicker9" type="text" placeholder="dd/mm/yy" uix-input="" class="datepicker">
                                                        <label id="errorFromDate" class="error-block-overlay"></label>
                                                        <label class="iconDate" for="datepicker">
                                                            <i class="ficon-calc"></i>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label class="control-label">To Date</label>
                                                    <div data-error="hasError" class="text-field date-field">
                                                        <input id="datepicker10" type="text" placeholder="dd/mm/yy" uix-input="" class="datepicker">
                                                        <label id="errorToDate" class="error-block-overlay"></label>
                                                        <label class="iconDate" for="datepicker2">
                                                            <i class="ficon-calc"></i>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Posted By
                                        <span>
                                            <span ng-cloak ng-if="posted_by_label == ''">Anyone</span>
                                            <span ng-cloak ng-if="posted_by_label !== ''" ng-bind="posted_by_label">
                                                &nbsp;
                                            </span> 
                                    </a>
                                    <ul data-type="stopPropagation" class="active-with-icon dropdown-menu dropdown-menu-left">
                                        <li ng-class="(posted_by_label == 'Anyone' || posted_by_label == '') ? 'active' : ''"><a ng-click="changePostedBy('Anyone');" >Anyone</a></li>
                                        <li ng-class="(posted_by_label == 'Friends') ? 'active' : ''"><a ng-click="changePostedBy('Friends');" >Friends</a></li>
                                        <li ng-class="(posted_by_label == 'My Follows') ? 'active' : ''"><a ng-click="changePostedBy('My Follows');" >My Follows</a></li>
                                        <div class="add-morefilter">
                                            <div class="input-search form-control left">
                                                <tags-input class="form-control" ng-model="PostedByUsers" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="callEventList();" on-tag-added="callEventList();">
                                                    <auto-complete source="searchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="searchUserDropdownTemplate"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="searchUserDropdownTemplate">
                                                    <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                                </script>
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- <li><a>By Ratings</a></li> -->
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Sort By 
                                        <span>
                                            <span ng-cloak ng-if="sort_by_label == ''">Network</span>
                                            <span ng-cloak ng-if="sort_by_label !== ''" ng-bind="sort_by_label"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="active-with-icon dropdown-menu dropdown-menu-left">
                                        <li ng-if="sort_by_label2 !== 'NameAsc'" ng-class="(sort_by_label2 == 'NameDesc') ? 'active' : ''"><a ng-click="changeSortBy('NameAsc');">By Name</a></li>
                                        <li ng-if="sort_by_label2 == 'NameAsc'" ng-class="(sort_by_label2 == 'NameAsc') ? 'active' : ''"><a ng-click="changeSortBy('NameDesc');">By Name</a></li>
                                        <li ng-class="(sort_by_label2 == 'Friends') ? 'active' : ''"><a ng-click="changeSortBy('Friends');">Friends</a></li>
                                        <li ng-class="(sort_by_label2 == 'Most Members') ? 'active' : ''"><a ng-click="changeSortBy('Most Members');">Most Members</a></li>
                                        <li ng-class="(sort_by_label2 == 'Event Date') ? 'active' : ''"><a ng-click="changeSortBy('Event Date');">Event Date</a></li>
                                        <li ng-class="(sort_by_label2 == 'Recent Updated') ? 'active' : ''"><a ng-click="changeSortBy('Recent Updated');">Recent Updated</a></li>
                                        <li ng-class="(sort_by_label2 == 'Network' || sort_by_label2 == '') ? 'active' : ''"><a ng-click="changeSortBy('');">Network</a></li>
                                        <!-- <li><a ng-click="changeSortBy('ActivityLevel');">By Activity Level</a></li> -->
                                        <!-- <li><a>By Ratings</a></li> -->
                                    </ul>
                                </li>

                                <li  ng-cloak="" ng-if="!isDefaultFilterEventSearch()">
                                    <div class="reset-button" >
                                        <button class="btn btn-default" ng-click="ResetFilterEventSearch()">Reset</button>
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
<input type="hidden" id="postedby" value="Anyone" />
<input type="hidden" id="CurrentPage" value="Event" />