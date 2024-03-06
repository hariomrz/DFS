<section class="filter-default" ng-controller="AcitvityFilterController" init-scroll-fix="scrollFix" id="AcitvityFilterController">
    <div class="container" dropdown-stop-propagation>
        <nav class="navbar navbar-filter">
            <ul class="nav navbar-nav filter-nav">
                
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button">
                        <span class="text">Verify Type</span> 
                        <span ng-bind="activityVerifyTypes[filterOptions.Verified]" class="text-small">
                            
                        </span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-repeat="(activityVerifyVal, activityVerifyTypeKey) in activityVerifyTypes" 
                            ng-click="setActivityType('Verified', activityVerifyVal)" 
                            ng-class="{ 'active' : ( filterOptions.Verified == activityVerifyVal ) }">
                            <a>
                                <span ng-bind="activityVerifyTypeKey"></span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button">
                        <span class="text">Show me</span>  
                        <span ng-bind="activityTypeFilter[filterOptions.ActivityTypeFilter]" class="text-small">
                            
                        </span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-repeat="(activityTypeFilterVal, activityTypeFilterKey) in activityTypeFilter" 
                            ng-click="setActivityType('ActivityTypeFilter', activityTypeFilterVal)" 
                            ng-class="{ 'active' : ( filterOptions.ActivityTypeFilter == activityTypeFilterVal ) }">
                            <a>
                                <span ng-bind="activityTypeFilterKey"></span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!--
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button"> 
                        <span class="text">Show me</span>  
                        <span class="text-small" ng-bind="showMeLabelName">All Posts</span>                        
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                        <li ng-repeat="posttype in ShowMe" ng-class="($first)  ? 'checkallcheckbox' : '' ;">
                            <label class="checkbox">
                                <input ng-if="$first" ng-model="posttype.IsSelect" ng-change="verifyAllCheckedStatus()" type="checkbox" class="check-content-filter">
                                <input ng-if="!$first" ng-model="posttype.IsSelect" ng-checked="( posttype.IsSelect || ShowMe[0].IsSelect )" ng-change="verifyCheckedStatus()" type="checkbox" class="check-content-filter">
                                <span ng-bind="posttype.Label" class="label"></span>
                            </label>
                        </li>
                    </ul>
                </li>
                -->
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button"> 
                        <span class="text">Content</span>  
                        <span ng-if="( searchTags.length > 0 )" class="text-small" ng-bind="searchTags[0].Name"></span>
                        <span ng-if="( ( searchTags.length == 0 ) && ( filterOptions.SearchKey == '' ) )" class="text-small">All Content</span>
                        <span ng-if="( ( searchTags.length == 0 ) && ( filterOptions.SearchKey != '' ) )" ng-bind="filterOptions.SearchKey" class="text-small"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                        <li>
                            <label>Keyword</label>
                            <div class="input-search form-control right">
                                <input type="text" ng-model="filterOptions.SearchKey" ng-keyup="$event.keyCode == 13 ? applyFiltersOptions() : null; setSearchStatus()" placeholder="Search" class="form-control">
                                <div class="input-group-btn">
                                    <button ng-click="applyFiltersOptions();" id="BtnSrch" type="button" class="btn-search"> <i ng-click="searchFilter()" class="icon-search"></i> </button>
                                </div>
                            </div>
                        </li>
                        <li>
                            <label>Tags</label>
                            <div class="suggestion-list-scroll">
                                <tags-input min-length="2" add-from-autocomplete-only="true" ng-model="searchTags" on-tag-added="updateSearchTag('added', $tag.TagID)" on-tag-removed="updateSearchTag('removed', $tag.TagID)" key-property="TagID" display-property="Name" placeholder="Tags" replace-spaces-with-dashes="false">
                                    <auto-complete source="loadSearchTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="25"></auto-complete>
                                </tags-input>
                            </div>
                        </li>
                    </ul>
                </li>
               <!-- <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button">
                        <span class="text">Type</span> 
                        <span ng-bind="activeActivityType" class="text-small"></span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-repeat="(activityTypeKey, activityTypeData) in activityTypes" ng-click="setActivityType(activityTypeKey)" ng-class="{ 'active' : ( activeActivityType == activityTypeKey ) }">
                            <a>
                                <span ng-bind="activityTypeKey"></span>
                            </a>
                        </li>
                    </ul>
                </li>
                
     -->           
                
                <li class="dropdown">
                    <a class="arrow-right" data-toggle="dropdown" role="button">
                        <span class="text">Ownership</span> 
                        <span ng-bind="( ( PostedByLookedMore.length > 0 ) ? PostedByLookedMore[0].Name : 'Anyone' )" class="text-small"></span>
                    </a>
                    <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                        <li ng-click="resetOwnership();"><a>Anyone</a></li>
                        
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
                        <span ng-if="( !filterOptions.StartDate && !filterOptions.EndDate )" class="text-small">Any Time</span>
                        <span ng-if="( filterOptions.StartDate && !filterOptions.EndDate )" ng-bind="filterOptions.StartDate" class="text-small"></span>
                        <span ng-if="( !filterOptions.StartDate && filterOptions.EndDate )" ng-bind="filterOptions.EndDate" class="text-small"></span>
                        <span ng-if="( filterOptions.StartDate && filterOptions.EndDate )" ng-bind="( filterOptions.StartDate + '-' + filterOptions.EndDate )" class="text-small"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                        <li>
                            <div class="form-group">
                                <label class="control-label">From Date</label>
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
                            <div class="form-group">
                                <label class="control-label">To Date</label>
                                <div data-error="hasError" class="date-field">
                                    <input type="text"
                                           ng-model="filterOptions.EndDate"
                                           placeholder="__ /__ /__"
                                           readonly
                                           ng-change="checkValDatepicker()"
                                           init-filter-datepicker
                                            pickerType="to" 
                                            id="adminDashboardFilterDatepicker2" 
                                            fromid="adminDashboardFilterDatepicker" 
                                            toid="adminDashboardFilterDatepicker2"
                                           class="form-control" />
                                    <label id="errorToDate" class="error-block-overlay"></label>
                                    <label class="iconDate" for="adminDashboardFilterDatepicker2">
                                        <i class="ficon-calendar"></i>
                                    </label>
                                </div>
                            </div> 
                        </li>
                    </ul>
                </li>
              <!--     <li class="dropdown">
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
                        <span class="text">Demographics</span>
                        <span class="text-small" ng-if="( demoGraphics.locationLable || demoGraphics.genderLable || demoGraphics.ageGroupLable )" ng-bind="demoGraphics.locationLable + ( ( demoGraphics.genderLable !== '' ) ? ( ( demoGraphics.locationLable === '' ) ? demoGraphics.genderLable : ', ' + demoGraphics.genderLable ) : '' ) + ( ( demoGraphics.ageGroupLable !== '' ) ? ( ( ( demoGraphics.locationLable === '' ) && ( demoGraphics.genderLable === '' ) ) ? demoGraphics.ageGroupLable : ', ' + demoGraphics.ageGroupLable ) : '' )">Mumbai, Male</span>
                        <span class="text-small" ng-if="( !demoGraphics.locationLable && !demoGraphics.genderLable && !demoGraphics.ageGroupLable )">None</span>
                    </a>
                    <ul class="dropdown-menu filters-dropdown" data-type="stopPropagation">
                        <li>
                            <div class="form-group">
                                <label class="label-control bold">From Location</label>
                                <input ng-model="demoGraphics.location" google-place details="demoGraphics.details" ng-change="setLocationFilter()" type="text" class="form-control" placeholder="Enter cities">
                            </div>
                        </li>
                        <li>
                            <div class="form-group">
                                <label class="label-control bold">By Gender</label>
                                <div class="radio-list">
                                    <label class="radio radio-inline">
                                        <input type="radio" ng-model="filterOptions.Gender" ng-change="setGenderValue();" value="1">
                                        <span class="label">Male</span>
                                    </label>
                                    <label class="radio radio-inline">
                                        <input type="radio" ng-model="filterOptions.Gender" ng-change="setGenderValue();" value="2">
                                        <span class="label">Female</span>
                                    </label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="form-group" ng-init="getAgeGroup();">
                                <label class="control-label bold block">By Age Group</label>
                                <div class="form-group">
                                    <select ng-change="ageGroupSelected()"
                                        chosen class="form-control" 
                                        ng-model="selectedAgeGroup" 
                                        ng-options="ageGroup.Name for ageGroup in ageGroupArray">
                                        <option></option>
                                    </select>
                                </div>
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
                </li> -->
            </ul>
            <aside ng-if="isFilterApplied" class="filter-action">
                <button class="btn btn-default btn-sm" ng-click="resetAllAppliedFilterOptions(0)" type="button">RESET</button>
               <!-- <button class="btn btn-default btn-sm" ng-click="addToRulePopup()" type="button">Add To Rule</button> -->
            </aside>

        </nav>
        <!-- Original Starts -->
    </div>
   <!-- <div ng-controller="RulesCtrl" id="RulesCtrl">
        < ?php $this->load->view('admin/rules/create_rules_modal') ?>
    </div>
   -->
</section>
<input type="hidden" id="IsAdminDashboard" value="1" />