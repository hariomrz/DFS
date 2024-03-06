<div class="nav-tab-fixed">
    <div class="nav-tab-nav" ng-cloak="">
        <!-- // secondary-nav -->   
        <div class="container"> 
            <div class="nav-tab-filter">     
                <div class="filter-fixed" ng-show="filterFixed">
                    <button class="btn btn-default close-filter" ng-click="filterFixed = false">
                        <span class="icon">
                            <i class="ficon-cross"></i>                
                        </span>
                    </button>
                    <div class="main-filter-nav">
                        <nav class="navbar navbar-static">
                            <div class="navbar-header visible-xs">
                                <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#filterNav">                  
                                    <span class="icon"><i class="ficon-filter"></i></span>
                                </button>
                            </div>
                            <div class="collapse navbar-collapse" id="filterNav">

                                <ul class="nav navbar-nav filter-nav">
                                    <li class="dropdown">
                                        <a data-toggle="dropdown" role="button"> 
                                            <span>By User</span>  
                                            <abbr ng-if="poll_search_term == ''">All User</abbr>
                                            <abbr ng-if="poll_search_term" ng-bind="poll_search_term"></abbr>                                        
                                        </a>                                   
                                        <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                                            <li>
                                                <div class="input-search form-control right">
                                                    <input type="text" id="PostOwnerSearch" class="form-control ui-autocomplete-input" type="text" placeholder="Search by User" name="srch-filters" autocomplete="off" class="form-control" data-ng-model="poll_filter_user">                                            
                                                </div>
                                            </li>
                                        </ul>

                                    </li>

                                    <li class="dropdown">
                                        <a data-toggle="dropdown" role="button"> 
                                            <span>Time Period</span> 
                                            <abbr ng-cloak ng-if="!poll_date_search_term">Any Time</abbr>                                        
                                            <abbr ng-cloak ng-if="poll_date_search_term" ng-bind="poll_date_search_term">Any Time</abbr>                                        
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">
                                            <li>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="control-label">From Date</label>
                                                        <div data-error="hasError" class="text-field date-field">
                                                            <input type="text" ng-model="poll_filter_post_date_start" placeholder="__ /__ /__" readonly=""  onchange="checkValDatepicker()" id="datepicker" />
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
                                                            <input type="text" ng-model="poll_filter_post_date_end" placeholder="__ /__ /__" readonly=""  onchange="checkValDatepicker()" id="datepicker2" />
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
                                        <a data-toggle="dropdown" role="button"> 
                                            <span>By Type</span>  
                                            <abbr >All Type</abbr>
                                                                                   
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-left filters-dropdown mCustomScrollbar filter-height" data-type="stopPropagation">                                                                                         
                                            <li> 
                                                <label class="checkbox">
                                                    <input type="checkbox" value="" id="Anonymous" data-ng-click="enable_filter_view();" data-ng-model="filter_anonymous" class="check-content-filter">
                                                    <span class="label">Anonymous</span>
                                                </label>
                                            </li>

                                            <li>                                            
                                                <label class="checkbox">
                                                    <input type="checkbox" value="" id="Expired" data-ng-click="enable_filter_view();" data-ng-model="filter_expired" class="check-content-filter">
                                                    <span class="label" for="Expired">Expired</span>
                                                </label>
                                            </li>

                                            <li>                                            
                                                <label class="checkbox">
                                                    <input type="checkbox" value="" id="Archive" data-ng-click="enable_filter_view();" data-ng-model="filter_archive" class="check-content-filter">
                                                    <span class="label" for="Archive">Archive</span>
                                                </label>
                                            </li>
                                        </ul>
                                    </li>

                                    <li ng-if="(poll_search_term != '' || poll_date_search_term != '' || filter_anonymous != '' || filter_expired != '' || filter_archive)">
                                        <div class="reset-button" ng-click="clearAllPollFilter()()">
                                            <button class="btn btn-default">Reset</button> 
                                        </div>
                                    </li>
                                </ul>                        
                            </div>
                        </nav>
                    </div>
                </div>            
                <div class="row">
                    <div class="col-sm-10 col-xs-9">
                        <ul class="nav nav-tabs nav-tabs-liner primary nav-tabs-scroll" role="tablist">
                            <li data-ng-class="(filterType == 0 || !filterType) ? 'active' : ''"  data-ng-click="applyPollFilterType(0);" class="clear-filter2"><a>All</a></li>
                            <li data-ng-class="(filterType == 1) ? 'active' : ''" ng-click="applyPollFilterType(1)"><a>My Polls</a></li>
                            <li data-ng-class="(filterType == 2) ? 'active' : ''" ng-click="applyPollFilterType(2)"><a>My Voted</a></li>       

                        </ul> 
                    </div>
                    <div class="col-sm-2 col-xs-3">
                        <div class="filter-actions">
                            <button class="btn btn-default btn-sm btn-filter" ng-click="filterFixed = true">
                                <span class="icon">
                                    <i class="ficon-filter"></i>
                                </span>            
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>