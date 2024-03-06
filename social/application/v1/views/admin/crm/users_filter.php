<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <ul class="bread-crumb-nav brd-subnav">
                    <li><h2>Users</h2></li>
                    <li><span class="user-count">{{totalRecord}} total</span></li>
                </ul>
            </div>
            <div class="col-sm-6">
                <div class="pull-right">
                    <ul class="filter-nav">
                        <li class="filter-search">
                            <i class="ficon-search" ng-if="!filter.SearchKey" ng-click="applyFilter(0)"></i>
                            <i class="ficon-cross" ng-if="filter.SearchKey" ng-click="searchFn($event, 1)" ></i>
                            <input type="text" class="form-control" ng-model="filter.SearchKey" ng-keyup="searchFn($event, 0)" >
                        </li>
                        <li>
                            <button class="btn btn-default filter-btn" data-toggle="collapse" data-target="#userFilters">Filter 
                                <i class="ficon-filter-list"></i>
                                <span class="filter-apply" ng-if="isFilterReady()">

                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div> 
        </div> 
    </div>
    <div class="row user-filters collapse" id="userFilters">
        <div class="col-sm-12">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="user-filters-view"> 
                            <div class="row">
                                <div class="col-sm-2">
                                    <!-- <label for="" class="form-label">Gender</label>    -->
                                    <label for="" class="form-label">Ward</label>   
                                </div>

                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-group"> 
                                            <select id="select_ward"  class="chosen-select form-control" data-chosen="" ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_list" data-ng-model="filter.WID" data-disable-search="false">
                                                <option value=""></option>
                                            </select>                                                
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="row gutter-5">
                                                <div class="col-sm-4 text-lrft">
                                                    <label for="" class="form-label" >
                                                        Last Login
                                                    </label>   
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="row gutter-5">
                                                        <div class="col-sm-12">
                                                            <div class="form-group">
                                                                <select class="chosen-select form-control" ng-model="filter.LastLogin">
                                                                    <option selected="" value="0">All</option>
                                                                    <option value="5">5</option>
                                                                    <option value="10">10</option>
                                                                    <option value="15">15</option>
                                                                    <option value="20">20</option>                                                                                                                                                                                                            
                                                                    <option value="25">25</option>
                                                                    <option value="30">30</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- <div class="col-sm-3">
                                            <div class="row gutter-5">
                                                <div class="col-sm-5 text-lrft">
                                                    <label for="" class="form-label" >
                                                        Age Between
                                                    </label>   
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="row gutter-5">
                                                        <div class="col-sm-5">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="AgeStart" ng-model="filter.AgeStart" age-validate />
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2 text-center lh-30">&mdash;</div>
                                                        <div class="col-sm-5">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="AgeEnd" ng-model="filter.AgeEnd" ng-class="{'red-border': (filter.AgeStart >= filter.AgeEnd)}"  age-validate />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->
                                        <div class="col-sm-3">
                                            <div class="row gutter-5">
                                                <div class="col-sm-5 text-lrft">
                                                    <label for="" class="form-label">User Status</label>   
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="form-group"> 

                                                        <select class="chosen-select form-control" ng-model="filter.StatusID"                                                         
                                                                ng-options="userStatusOption.value as userStatusOption.label for userStatusOption in userStatusOptions"       
                                                                >
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="row gutter-5">
                                                <div class="col-sm-5 text-lrft">
                                                    <label for="" class="form-label">Registered On</label>   
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="dropdown dropdown-time" data-dropdown="hide">
                                                        <a class="btn btn-default btn-block" data-toggle="dropdown">
                                                            <span class="icn"><i class="ficon-arrow-down"></i></span>
                                                            <span class="text" ng-bind="getDateFilterLabel()">All</span>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <ul class="dropdown-day">
                                                                <li ng-repeat="dateRangeFilterOption in dateRangeFilterOptions" ng-click="onSelectDateRange(dateRangeFilterOption)">
                                                                    <a ng-bind="dateRangeFilterOption.label"></a>
                                                                </li>                                                                
                                                                
                                                                <li><a class="customDate" data-type="stopPropagation">Custom</a></li>
                                                            </ul>
                                                            <ul class="dropdown-custom">
                                                                <li>
                                                                    <form>
                                                                        <div class="form-group">
                                                                            <div class="input-group date">
                                                                                <input type="text" class="form-control datepicker" id="from" ng-model="filter.StartDate" placeholder="dd-mm-yyyy" on-focus>
                                                                                <label class="input-group-addon" for="from">
                                                                                    <i class="ficon-calender"></i>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <div class="input-group date">
                                                                                <input type="text" class="form-control datepicker" ng-model="filter.EndDate" id="to" placeholder="dd-mm-yyyy" on-focus>
                                                                                <label class="input-group-addon" for="to">
                                                                                    <i class="ficon-calender"></i>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </form> 
                                                                </li>                                
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                                                
                            </div>

                            <div class="row">
                                <div class="col-sm-2">
                                    <!-- <label for="" class="form-label">Gender</label>    -->
                                    <label for="" class="form-label">Android APP Version</label>   
                                </div>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <select chosen data-disable-search="false" 
                                                    ng-model="filter.AndroidAppVersion" 
                                                    ng-options="g.Key as g.Name for g in AndroidAppVersionOptions" 
                                                    title="Select Version" data-placeholder="Select Version" 
                                                    class="form-control">
                                                    <option></option>
                                                </select>       
                                                
                                            </div>                                                        
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="row gutter-5">
                                                <div class="col-sm-5 text-lrft">
                                                    <label for="" class="form-label">iOS APP Version</label>   
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="form-group">
                                                        <select chosen data-disable-search="false" 
                                                            ng-model="filter.IOSAppVersion" 
                                                            ng-options="g.Key as g.Name for g in IOSAppVersionOptions" 
                                                            title="Select Version" data-placeholder="Select Version" 
                                                            class="form-control">
                                                            <option></option>
                                                        </select>  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          <!--    <div class="row">
                                <div class="col-sm-2">
                                    <label for="" class="form-label">Select Location</label>   
                                </div>
                                <div class="col-sm-10">
                                    <div class="form-group"> 
                                        <div class="form-group"> 
                                            <input id="filterLocations" class="form-control" placeholder="Enter Locations" type="text" />
                                            <div class="location-added" id="readonlyinput">
                                                <tags-input readonly="readonly" 
                                                            ng-model="filter.Locations" 
                                                            display-property="City" 
                                                            add-from-autocomplete-only="true" 
                                                            replace-spaces-with-dashes="false"
                                                            >
                                                </tags-input>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
 -->
                          <div class="row">
                                <div class="col-sm-2">
                                    <label for="" class="form-label">Select User Type</label>   
                                </div>
                                <div class="col-sm-10">
                                    <div class="form-group"> 
                                        <tags-input ng-model="filter.TagUserType" 
                                                    placeholder="Enter User type"
                                                    readonly="readonly"

                                                    add-from-autocomplete-only="true" 
                                                    replace-spaces-with-dashes="false"
                                                    > 
                                            <auto-complete source="onTagsGet($query, 0)"  display-property="Name"></auto-complete>
                                        </tags-input>
                                    </div>
                                    <div class="form-group"> 
                                        <div class="radio-list">
                                            <label class="radio radio-inline">
                                                <input type="radio" value="1" checked="" name="MatchTagsuser" class="TagUserSearchType">
                                                <span class="label">Match any tag</span>
                                            </label>
                                            <label class="radio radio-inline">
                                                <input type="radio" value="0"  name="MatchTagsuser" class="TagUserSearchType">
                                                <span class="label">Match all tags</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div> 

                          <!--  <div class="row m-t-sm">
                                <div class="col-sm-2"><label for="" class="form-label">Select User Profession</label></div>
                                <div class="col-sm-10">
                                    <div class="form-group"> 
                                        <tags-input ng-model="filter.TagTagType" 
                                                    placeholder="Enter User Profession"
                                                    readonly="readonly"

                                                    add-from-autocomplete-only="true" 
                                                    replace-spaces-with-dashes="false"
                                                    > 
                                            <auto-complete source="onTagsGet($query, 1)"  display-property="Name"></auto-complete>
                                        </tags-input>
                                    </div>
                                    <div class="form-group"> 
                                        <div class="radio-list">
                                            <label class="radio radio-inline">
                                                <input type="radio" value="1" name="MatchTags" checked="" class="TagTagSearchType">
                                                <span class="label">Match any tag</span>
                                            </label>
                                            <label class="radio radio-inline">
                                                <input type="radio" value="0"  name="MatchTags" class="TagTagSearchType">
                                                <span class="label">Match all tags</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            -->
                           
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="button-group">
                                        <button class="btn btn-primary pull-right outline" ng-disabled="0 && !isFilterReady()" ng-click="applyFilter(0)">Apply</button>
                                        <button class="btn btn-default pull-right btn-link" ng-click="applyFilter(1)">Reset</button>
                                    </div>
                                </div>
                            </div>  
                        </div> 
                    </div>
                </div>
            </div>     
        </div>
    </div>
</div>


<script>

    var userStatusOptions = [];

//userStatusOptions.push({
//        value : 500, label : 'All Users'
//});

<?php if (in_array(getRightsId('registered_user'), getUserRightsData($this->DeviceType))) { ?>
        userStatusOptions.push({
            value: 2, label: '<?php echo lang("User_Index_RegisteredUsers"); ?>'
        });
<?php } ?>

<?php if (in_array(getRightsId('deleted_user'), getUserRightsData($this->DeviceType))) { ?>
        userStatusOptions.push({
            value: 3, label: '<?php echo lang("User_Index_DeletedUsers"); ?>'
        });
<?php } ?>

<?php if (in_array(getRightsId('blocked_user'), getUserRightsData($this->DeviceType))) { ?>
        userStatusOptions.push({
            value: 4, label: '<?php echo lang("User_Index_BlockedUsers"); ?>'
        });
<?php } ?>



</script>
