<div class="main-filter-nav">
    <nav class="navbar navbar-static">
        <ul class="nav navbar-nav filter-nav">
            <li class="dropdown">
                <a target="_self" data-toggle="dropdown" role="button"> 
                    <span ng-bind="lang.c_content"></span>  
                    <abbr ng-if="(keywordLabelName=='All Content' || keywordLabelName=='') && search_tags.length==0" ng-bind="lang.c_all_content"></abbr>
                    <abbr ng-if="(keywordLabelName!=='All Content' && keywordLabelName!=='')" ng-bind="keywordLabelName"></abbr>
                    <abbr ng-if="search_tags.length>0 && (keywordLabelName=='All Content' || keywordLabelName=='')" ng-bind="search_tags[0].Name"></abbr>
                </a>
                <!-- Content Filter Start -->
                <ul class="dropdown-menu dropdown-menu-left filters-dropdown" data-type="stopPropagation">
                    <li>
                        <label ng-bind="lang.c_keyword"></label>
                        <div class="input-search form-control right">
                            <input type="text" id="srch-filters" name="srch-filters" onkeyup="srchFilter(event)" placeholder="Search" class="form-control">
                            <div class="input-group-btn">
                                <button ng-click="searchWallContent();" id="BtnSrch" type="button" class="btn-search"> <i ng-click="searchWallContent()" class="icon-search-gray"></i> </button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <label ng-bind="lang.c_tags"></label>
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
                <a target="_self" data-toggle="dropdown" role="button">
                    <span ng-bind="lang.c_type"></span> 
                    <abbr ng-bind="Filter.typeLabelName"></abbr>
                </a>
                <ul id="typeDropdown" class="active-with-icon dropdown-menu dropdown-menu-left active-with-icon" data-type="stopPropagation">
                    <li onclick="addActiveClass(this); " class="active">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Everything');applyFilterType('0','1');">
                            <span class="label" ng-bind="lang.c_everything"></span>
                        </a>
                    </li>
                    <li onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Archive');applyFilterType('4','1');">
                            <span class="label" ng-bind="lang.c_archived"></span>
                        </a>
                    </li>
                    <li onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Deleted');applyFilterType('7','1');">
                            <span class="label" ng-bind="lang.c_deleted"></span>
                        </a>
                    </li>
                    <li ng-if="!IsMyDeskTab" onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Favourite');applyFilterType('6','1');">
                            <span class="label" ng-bind="lang.c_favourite"></span>
                        </a>
                    </li>
                    <li ng-if="config_detail.IsSuperAdmin" onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Feature');applyFilterType('11','1');">
                            <span class="label" ng-bind="lang.c_featured"></span>
                        </a>
                    </li>
                    <li onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Text');" onclick="applySearchFilter('IsMediaExists','0');">
                            <span class="label" ng-bind="lang.c_text"></span>
                        </a>
                    </li>
                    <li onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Media');" onclick="applySearchFilter('IsMediaExists','1');">
                            <span  class="label" ng-bind="lang.c_media"></span>
                        </a>
                    </li>
                    <li onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Draft');applyFilterType('10','1');">
                            <span class="label" ng-bind="lang.c_drafts"></span>
                        </a>
                    </li>
                    <li ng-if="config_detail.ModuleID == 1 || config_detail.ModuleID == 18" onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Flag');applyFilterType('2','1');">
                            <span class="label" ng-bind="lang.c_flag"></span>
                        </a>
                    </li>
                    <li ng-if="!IsMyDeskTab" onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Mention');applyFilterType('5');">
                            <span ng-bind="lang.c_mentions"></span>
                        </a>
                    </li>
                    <li ng-if="!IsMyDeskTab && config_detail.IsSuperAdmin" onclick="addActiveClass(this); ">
                        <a target="_self" ng-click="setFilterLabelName('typeLabelName','Promoted');applyFilterType('IsPromoted', '1');">
                            <span ng-bind="lang.c_promoted"></span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a target="_self" data-toggle="dropdown" role="button">
                    <span ng-bind="lang.c_ownership"></span> 
                    <abbr ng-cloak ng-if="Filter.ownershipLabelName==''" ng-bind="lang.c_everything"></abbr>
                    <abbr ng-cloak ng-if="Filter.ownershipLabelName!==''" ng-bind="Filter.ownershipLabelName"></abbr>
                </a> 
                <ul class="active-with-icon dropdown-menu dropdown-menu-left" data-type="stopPropagation">
                    <li class="active" ng-click="setFilterLabelName('ownershipLabelName','Anyone');changePostedBy('Anyone')" onclick="addActiveClass(this)"><a ng-bind="lang.c_anyone"></a></li>
                    <li ng-click="setFilterLabelName('ownershipLabelName','You');changePostedBy('You')" onclick="addActiveClass(this)"><a ng-bind="lang.c_you"></a></li>
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
                <a target="_self" data-toggle="dropdown" role="button"> 
                    <span ng-bind="lang.c_time_period"></span> 
                    <abbr ng-cloak ng-if="Filter.timeLabelName==''" ng-bind="lang.c_any_time"></abbr>
                    <abbr ng-cloak ng-if="Filter.timeLabelName!==''" ng-bind="Filter.timeLabelName"></abbr>
                </a>
                <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                    <li>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label" ng-bind="lang.c_from_date"></label>
                                <div data-error="hasError" class="text-field date-field">
                                    <input type="text" ng-model="datepicker" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker" />
                                    <label id="errorFromDate" class="error-block-overlay"></label>
                                    <label class="iconDate" for="datepicker">
                                        <i class="ficon-calc"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label" ng-bind="lang.c_to_date"></label>
                                <div data-error="hasError" class="text-field date-field">
                                    <input type="text" ng-model="datepicker2" placeholder="__ /__ /__" readonly="" ng-change="Filter.IsSetFilter=true" onchange="checkValDatepicker()" id="datepicker2" />
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
            <li ng-if="Filter.IsSetFilter  ">
                <div class="reset-button" ng-click="ResetFilter()">
                   <button class="btn btn-default" ng-bind="lang.c_reset"></button> 
                </div>
            </li>
        </ul>
    </nav>
</div>