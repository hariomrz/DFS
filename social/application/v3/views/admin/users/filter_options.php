<nav class="navbar navbar-filter">
    <ul class="nav navbar-nav filter-nav">
        <!-- <li class="dropdown">
            <a class="arrow-right" data-toggle="dropdown" role="button"> 
                <span class="text">Show me</span>  
                <span ng-if="!Filter.contentLabelName" class="text-small">All Posts</span>
                <span ng-if="Filter.contentLabelName" ng-bind="Filter.contentLabelName"  class="text-small"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                <li ng-repeat="posttype in Filter.ShowMe" ng-class="($first)  ? 'checkallcheckbox' : '' ;">
                    <label class="checkbox">
                        <input ng-click="SelectPostType(posttype);" ng-model="posttype.IsSelect" type="checkbox" class="check-content-filter">
                        <span ng-bind="posttype.Label" class="label"></span>
                    </label>
                </li>
            </ul>
        </li> -->
        <li class="dropdown">
            <a class="arrow-right" data-toggle="dropdown" role="button"> 
             <span class="text">Content</span>  
             <span ng-if="search_tags.length>0" class="text-small" ng-bind="search_tags[0].Name"></span>
            <span ng-if="search_tags.length==0 && keywordLabelName==''" class="text-small">All Content</span>
            <span ng-if="search_tags.length==0 && keywordLabelName!==''" ng-bind="keywordLabelName" class="text-small"></span>
        </a>
            <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                <li>
                    <label>Keyword</label>
                    <div class="input-search form-control right">
                        <input type="text" id="srch-filters" name="srch-filters" onkeyup="srchFilter(event)" placeholder="Search" class="form-control">
                        <div class="input-group-btn">
                            <button ng-click="searchWallContent();" id="BtnSrch" type="button" class="btn-search"> <i ng-click="searchWallContent()" class="icon-search"></i> </button>
                        </div>
                    </div>
                </li>
                <li>
                    <label>Tags</label>
                    <div>
                        <tags-input min-length="2" add-from-autocomplete-only="true" ng-model="search_tags" on-tag-added="updateWallPost()" on-tag-removed="updateWallPost()" key-property="TagID" display-property="Name" placeholder="Tags" replace-spaces-with-dashes="false">
                            <auto-complete source="loadSearchTags($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4"></auto-complete>
                        </tags-input>
                    </div>
                </li>
            </ul>
        </li>
        <li class="dropdown">
            <a class="arrow-right" data-toggle="dropdown" role="button">
            <span class="text">Type</span> 
            <span ng-bind="Filter.typeLabelName" class="text-small"></span>
        </a>
            <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','All');applyFilterType('0','1');">
                        <span>All</span>
                    </a>
                </li>
                <!-- <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Archive');applyFilterType('4','1');">
                        <span>Archived</span>
                    </a>
                </li>
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Deleted');applyFilterType('7','1');">
                        <span>Deleted</span>
                    </a>
                </li>
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Favourite');applyFilterType('6','1');">
                        <span>Favourite</span>
                    </a>
                </li>
                <li ng-if="config_detail.IsAdmin" onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Feature');applyFilterType('11','1');">
                        <span>Featured</span>
                    </a>
                </li> -->
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Text');" onclick="applySearchFilter('IsMediaExists','0');">
                        <span>Text</span>
                    </a>
                </li>
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Media');" onclick="applySearchFilter('IsMediaExists','1');">
                        <span>Media</span>
                    </a>
                </li>
                <!-- <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Draft');applyFilterType('10','1');">
                        <span>Drafts</span>
                    </a>
                </li>
                <li ng-if="config_detail.ModuleID == 1 || config_detail.ModuleID == 18" onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('typeLabelName','Flag');applyFilterType('2','1');">
                        <span>Flag</span>
                    </a>
                </li> -->
            </ul>
        </li>
        <li class="dropdown">
            <a class="arrow-right" data-toggle="dropdown" role="button">
            <span class="text">Ownership</span> 
            <span ng-cloak ng-if="Filter.ownershipLabelName==''" class="text-small">Everything</span>
            <span ng-cloak ng-if="Filter.ownershipLabelName!==''" ng-bind="Filter.ownershipLabelName" class="text-small"></span>
        </a>
            <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                <li ng-click="setFilterLabelName('ownershipLabelName','Anyone');changePostedBy('Anyone')" onclick="addActiveClass(this)"><a>Anyone</a></li>
                <li ng-click="setFilterLabelName('ownershipLabelName','You');changePostedBy('You')" onclick="addActiveClass(this)"><a>You</a></li>
                <li class="p-h-sm">
                    <div class="">
                        <tags-input ng-model="PostedByLookedMore" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="updateOwnership($tag, false);" on-tag-added="updateOwnership($tag, true);">
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
            <span ng-cloak ng-if="Filter.timeLabelName==''" class="text-small">Any Time</span>
            <span ng-cloak ng-if="Filter.timeLabelName!==''" ng-bind="Filter.timeLabelName" class="text-small"></span>
        </a>
            <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                <li>
                    <div class="form-group">
                        <label class="control-label">From Date</label>
                        <div data-error="hasError" class="date-field">
                            <input type="text" ng-model="datepicker" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker" class="form-control" />
                            <label id="errorFromDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="datepicker">
                                <i class="ficon-calendar"></i>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">To Date</label>
                        <div data-error="hasError" class="date-field">
                            <input type="text" ng-model="datepicker2" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker2" class="form-control" />
                            <label id="errorToDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="datepicker2">
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
                <span ng-bind="WN" class="text-small"></span>
            </a>
            <ul class="dropdown-menu filters-dropdown" data-type="stopPropagation">
                <li>
                    <div class="form-group" ng-init="get_ward_list();">
                        <select id="select_ward" ng-change="ward_selected()"
                            chosen class="form-control" 
                            ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_lists"
                            data-ng-model="Ward_id">
                            <option></option>
                        </select>
                    </div>
                </li>
            </ul>            
        </li>
        <li class="dropdown">
            <a class="arrow-right" data-toggle="dropdown" role="button"> 
            <span class="text">Sort By</span> 
            <span ng-if="!Filter.sortLabelName" class="text-small">Recent Post</span>
            <span ng-if="Filter.sortLabelName" ng-bind="Filter.sortLabelName" class="text-small"></span>
        </a>
            <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('sortLabelName','Recent Post');" onclick="changeFilterSortBy(2,'RecentActivities')">
                        <span>Recent Post</span>
                    </a>
                </li>
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('sortLabelName','Recent Updated');" onclick="changeFilterSortBy(1,'TopStories')">
                        <span>Recent Updated</span>
                    </a>
                </li>
                <li onclick="addActiveClass(this)">
                    <a ng-click="setFilterLabelName('sortLabelName','Popular');" onclick="changeFilterSortBy(3,'PopularStories')">
                        <span>Popular</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <aside ng-if="Filter.IsSetFilter" class="filter-action">
        <button class="btn btn-default btn-sm" ng-click="ResetFilter()" type="button">RESET</button>
    </aside>
</nav>
<!-- Original Starts -->
