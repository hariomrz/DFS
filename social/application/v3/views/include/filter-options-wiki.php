<ul class="nav navbar-nav filter-nav">
    <?php if(isset($IsNewsFeed) && $IsNewsFeed=='1'){ ?>
        <li class="dropdown">
            <a data-toggle="dropdown" role="button"> 
                <span>Show me</span>  
                <abbr ng-if="!Filter.contentLabelName">All Posts</abbr>
                <abbr ng-if="Filter.contentLabelName" ng-bind="Filter.contentLabelName"></abbr>
            </a>
            <!-- Content Filter Start -->
            <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
<!--                <li>
                    <label class="checkbox" value="0">
                        <input type="checkbox" class="check-content-filter" value="0">
                        <span ng-click="setFilterLabelName('contentLabelName','All posts');filterPostType({'Value':0,'Label':'All Posts'})" class="label">All Posts</span>
                    </label>
                </li> -->
                <li  ng-repeat="posttype in Filter.ShowMe">
                    <label class="checkbox">
                        <input ng-checked="posttype.IsSelect" ng-click="SelectPostType(posttype);" ng-model="posttype.IsSelect" type="checkbox" ng-value="posttype.Value" class="check-content-filter">
                        <span   ng-bind="posttype.Label" class="label"></span>
                    </label>
                </li>
            </ul>
            <!-- Content Filter Ends -->
        </li>
    <?php } ?>
    <li class="dropdown">
        <a data-toggle="dropdown" role="button"> 
            <span>Content</span>  
            <abbr ng-if="keywordLabelName==''">All Content</abbr>
            <abbr ng-if="keywordLabelName!==''" ng-bind="keywordLabelName"></abbr>
        </a>
        <!-- Content Filter Start -->
        <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
            <!-- <li>
                <label>Mention</label>
                <div>
                    <tags-input on-tag-adding="mentionHeight()" on-tag-removing="mentionHeight()" on-tag-added="getFilteredWall(); mentionHeight()" on-tag-removed="getFilteredWall(); mentionHeight()" display-property="Title" key-property="ModuleEntityGUID" ng-model="suggestPage" add-from-autocomplete-only="true" data-placeholder="+ add pages you manage">
                        <auto-complete load-on-empty="true" load-on-focus="true" min-length="0" source="loadPages($query)"></auto-complete>
                    </tags-input> 
                </div>
            </li> -->
            <li>
                <label>Keyword</label>
                <div class="input-search form-control right">
                    <input type="text" id="srch-filters" name="srch-filters" onkeyup="srchFilter(event)" placeholder="Search" class="form-control">
                    <div class="input-group-btn">
                        <button ng-click="searchWallContent();" id="BtnSrch" type="button" class="btn-search"> <i ng-click="searchWallContent()" class="icon-search-gray"></i> </button>
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
        <!-- Content Filter Ends -->
    </li>
    <li class="dropdown">
        <a data-toggle="dropdown" role="button">
            <span>Type</span> 
            <abbr ng-bind="Filter.typeLabelName"></abbr>
        </a>
        <ul class="active-with-icon dropdown-menu dropdown-menu-left active-with-icon" data-type="stopPropagation">
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','All');applyFilterType('0','1');">
                    <span class="label">All</span>
                </a>
            </li>
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Archive');applyFilterType('4','1');">
                    <span class="label">Archived</span>
                </a>
            </li>
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Deleted');applyFilterType('7','1');">
                    <span class="label">Deleted</span>
                </a>
            </li>
            <li ng-if="config_detail.IsAdmin" onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Feature');applyFilterType('11','1');">
                    <span class="label">Featured</span>
                </a>
            </li>
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Text');" onclick="applySearchFilter('IsMediaExists','0');">
                    <span class="label">Text</span>
                </a>
            </li>
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Media');" onclick="applySearchFilter('IsMediaExists','1');">
                    <span  class="label">Media</span>
                </a>
            </li>
            <li onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Draft');applyFilterType('10','1');">
                    <span class="label">Drafts</span>
                </a>
            </li>
            <li ng-if="config_detail.ModuleID == 1 || config_detail.ModuleID == 18" onclick="addActiveClass(this)">
                <a ng-click="setFilterLabelName('typeLabelName','Flag');applyFilterType('2','1');">
                    <span class="label">Flag</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a data-toggle="dropdown" role="button">
            <span>Ownership</span> 
            <abbr ng-cloak ng-if="Filter.ownershipLabelName==''">Everything</abbr>
            <abbr ng-cloak ng-if="Filter.ownershipLabelName!==''" ng-bind="Filter.ownershipLabelName"></abbr>
        </a> 
        <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
            <li ng-click="setFilterLabelName('ownershipLabelName','Anyone');changePostedBy('Anyone')" onclick="addActiveClass(this)"><a>Anyone</a></li>
            <li ng-click="setFilterLabelName('ownershipLabelName','You');changePostedBy('You')" onclick="addActiveClass(this)"><a>You</a></li>
            <li>
                <div class="p-r p-l">
                    <tags-input ng-model="PostedByLookedMore" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="updateOwnership($tag, false);" on-tag-added="updateOwnership($tag, true);">
                        <auto-complete source="loadSearchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10"></auto-complete>
                    </tags-input>
                </div>
            </li>
        </ul>
        <input type="hidden" name="postedby" id="postedby">
    </li>
    <li class="dropdown">
        <a data-toggle="dropdown" role="button"> 
            <span>Time Period</span> 
            <abbr ng-cloak ng-if="Filter.timeLabelName==''">Any Time</abbr>
            <abbr ng-cloak ng-if="Filter.timeLabelName!==''" ng-bind="Filter.timeLabelName"></abbr>
        </a>
        <ul class="active-with-icon dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
            <li>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="control-label">From Date</label>
                        <div data-error="hasError" class="text-field date-field">
                            <input type="text" ng-model="datepicker" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker" />
                            <label id="errorFromDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="createBetweenFrom">
                                <i class="ficon-calc"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="control-label">To Date</label>
                        <div data-error="hasError" class="text-field date-field">
                            <input type="text" ng-model="datepicker2" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker2" />
                            <label id="errorToDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="createBetweenTo">
                                <i class="ficon-calc"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a data-toggle="dropdown" role="button"> 
            <span>Sort By</span> 
            <abbr ng-if="!Filter.sortLabelName">Recent Post</abbr>
            <abbr ng-if="Filter.sortLabelName" ng-bind="Filter.sortLabelName"></abbr>
        </a>
        <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
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
                <a ng-click="setFilterLabelName('sortLabelName','Popular');" onclick="changeFilterSortBy(0,'PopularStories')">
                    <span class="label">Popular</span>
                </a>
            </li>
        </ul>
    </li>
    <li ng-if="Filter.IsSetFilter  ">
        <div class="reset-button" ng-click="ResetFilter()">
           <button class="btn btn-default">Reset</button> 
        </div>
    </li>
</ul>