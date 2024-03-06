<section class="filter-default" ng-controller="AcitvityFilterController" init-scroll-fix="scrollFix" id="AcitvityFilterController">
    <div class="container" dropdown-stop-propagation ng-init="filterOptions.FilterType=2">
        <nav class="navbar navbar-filter">
            <ul class="nav navbar-nav filter-nav">
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button">
                        <span class="text">Ownership</span> 
                        <span ng-bind="( ( PostedByLookedMore.length > 0 ) ? PostedByLookedMore[0].Name : 'Anyone' )" class="text-small"></span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-click="resetOwnership();"><a>Anyone</a></li>
                        <!--<li ng-click="setFilterLabelName('ownershipLabelName','You');changePostedBy('You')" onclick="addActiveClass(this)"><a>You</a></li>-->
                        <li class="p-h-sm">
                            <div class="">
                                <tags-input ng-model="PostedByLookedMore" display-property="Name" key-property="ModuleEntityID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="removeOwnershipInfoById($tag.ModuleEntityID);" on-tag-added="addOwnershipInfoById($tag.ModuleEntityID);">
                                    <auto-complete source="loadSearchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="false" max-results-to-show="10"></auto-complete>
                                </tags-input>
                            </div>
                        </li>
                    </ul>
                    <input type="hidden" name="postedby" id="postedby">
                </li>
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button"> 
                        <span class="text">Time Period</span> 
                        <span ng-if="( !filterOptions.StartDate )" class="text-small">Any Time</span>
                        <span ng-if="( filterOptions.StartDate )" ng-bind="filterOptions.StartDate" class="text-small"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                        <li>
                            <div class="form-group">
                                <label class="control-label">Date</label>
                                <div data-error="hasError" class="date-field">
                                    <input type="text"
                                           ng-model="filterOptions.StartDate"
                                           placeholder="__ /__ /__"
                                           readonly
                                           ng-change="checkValDatepicker()"
                                           id="adminDashboardFilterDatepicker"
                                           init-filter-datepicker
                                            pickerType="from"
                                            fromid="adminDashboardFilterDatepicker"
                                            toid="adminDashboardFilterDatepicker2"
                                           class="form-control" />
                                    <label id="errorFromDate" class="error-block-overlay"></label>
                                    <label class="iconDate" for="adminDashboardFilterDatepicker">
                                        <i class="ficon-calendar"></i>
                                    </label>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                        <a class="arrow-right" data-toggle="dropdown" role="button"> 
                            <span class="text">Ward</span> 
                            <span ng-bind="filterOptions.WN" class="text-small"></span>
                        </a>
                        <ul class="dropdown-menu filters-dropdown" data-type="stopPropagation">
                            <li>
                                <div class="form-group" ng-init="getWardList();"> 
                                    <select id="select_ward" ng-change="wardSelected()"
                                        chosen class="form-control" 
                                        ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_list"
                                        data-ng-model="WID">
                                        <option></option>
                                    </select>
                                </div>
                            </li>
                        </ul>
                </li>

                <li class="dropdown">
                        <a class="arrow-right" data-toggle="dropdown" role="button"> 
                            <span class="text">Team Member</span> 
                            <span ng-bind="filterOptions.TName" class="text-small"></span>
                        </a>
                        <ul class="dropdown-menu filters-dropdown" data-type="stopPropagation">
                            <li>
                                <div class="form-group"> 
                                    <select id="select_team" ng-change="teamMemberSelected()"
                                    title="Select Team Member" data-placeholder="Select Team Member"
                                        chosen class="form-control" 
                                        ng-options="users.UserID as users.Name for users in team_member_list"
                                        data-ng-model="TID">
                                        <option></option>
                                    </select>
                                </div>
                            </li>
                        </ul>
                </li>


                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button"> 
                        <span class="text">Sort By</span>
                        <span ng-bind="activeSortBy" class="text-small"></span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-repeat="(sortByOptionKey, sortByOption) in sortByOptions" ng-click="setSortByOption(sortByOptionKey)" ng-class="{ 'active' : ( activeSortBy == sortByOption.Label ) }">
                            <a>
                                <span ng-bind="sortByOption.Label"></span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <aside ng-if="isFilterApplied" class="filter-action">
                <button class="btn btn-default btn-sm" ng-click="resetAllAppliedFilterOptions(2)" type="button">RESET</button>
               
            </aside>

        </nav>
        
    </div>
   </section>
<input type="hidden" id="IsAdminDashboard" value="1" />
