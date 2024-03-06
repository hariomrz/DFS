<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <ul class="bread-crumb-nav brd-subnav">
                    <li><h2>Newsletter Subscribers</h2></li>
                    <li ng-if="(totalRecordR + totalRecordShowing)" >
                        <span class="user-count">
                            (                                               
                            <span  ng-if="totalRecordR">{{totalRecordR}} registered user(s) out of </span>                        
                            <span  ng-if="totalRecordShowing">{{totalRecordShowing}} total</span>                                                
                            )
                        </span>
                    </li>
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
                                    <label for="" class="form-label">Gender</label>   
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group"> 
                                        <select class="chosen-select form-control" ng-model="filter.Gender">
                                            <option selected="" value="0">Any</option>
                                            <option value="1">Male</option>
                                            <option value="2">Female</option>
                                            <option value="3">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-3 text-right">
                                            <label for="" class="form-label">Age Between</label>   
                                        </div>
                                        <div class="col-sm-9">
                                            <div class="col-sm-5">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="AgeStart" ng-model="filter.AgeStart" age-validate />
                                                </div>
                                            </div>
                                            <div class="col-sm-2 text-center lh-30">—</div>
                                            <div class="col-sm-5">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="AgeEnd" ng-model="filter.AgeEnd" ng-class="{'red-border': (filter.AgeStart >= filter.AgeEnd)}"  age-validate />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                               

                            </div>




                            <div class="row">
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

                            <div class="row m-t-sm">
                                <div class="col-sm-2"><label for="" class="form-label">Select Tags</label></div>
                                <div class="col-sm-10">
                                    <div class="form-group"> 
                                        <tags-input ng-model="filter.TagTagType" 
                                                    placeholder="Add more tags"
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


                            <div class="row">
                                <div class="col-sm-2"><label for="" class="form-label">Inactive Users</label></div>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="form-group-day clearfix">  
                                                <label class="checkbox">                                                
                                                    <input type="checkbox" value="2" name="IU" ng-model="filter.InactiveProfile" ng-change="($event.target.checked) ? '' : filter.InactiveProfileDays = '';" />
                                                    <span class="label">&nbsp; From last</span>      
                                                </label>
                                                <label class="control-label">Days</label>
                                                <div class="form-group">                                                 
                                                    <input type="text" class="form-control" placeholder="X" ng-disabled="!filter.InactiveProfile" ng-model="filter.InactiveProfileDays" age-validate >                                                 
                                                </div> 
                                            </div>               
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-sm-2"><label for="" class="form-label">Incomplete Registration</label></div>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="form-group-day clearfix">  
                                                <label class="checkbox">
                                                    <input type="checkbox" value="2" name="IU" ng-model="filter.IncompleteProfile" ng-change="($event.target.checked) ? '' : filter.IncompleteProfileDays = '';" />
                                                    <span class="label">&nbsp; In last</span>
                                                </label>
                                                <label class="control-label">Days</label>
                                                <div class="form-group"> 
                                                    <input type="text" class="form-control" placeholder="X" ng-disabled="!filter.IncompleteProfile" ng-model="filter.IncompleteProfileDays" age-validate>
                                                </div>      
                                            </div>          
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2"><label for="" class="form-label">List Type</label></div>
                                <div class="col-sm-4">
                                    <div class="form-group">                                        
                                        <select class="chosen-select form-control" 
                                                ng-model="filter.UserType" 
                                                ng-options="userTypeOption.val as userTypeOption.label for userTypeOption in userTypeOptions"
                                                >                                                                                                                
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="button-group">
                                        <button class="btn btn-primary pull-right outline" ng-disabled="!isFilterReady()" ng-click="applyFilter(0)">Apply</button>
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

    userStatusOptions.push({
        value: 500, label: 'All Users'
    });

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
