<section class="container-fluid sub-heading" ng-controller="DashboardController" ng-init="getUnverifiedEntities('init')">
    <div class="page-heading">
        <div class="row">
            <div class="col-sm-3">
                <h4 class="page-title" > {{::entitySearchPlaceholder}}
                    <span ng-if="(EntityTotalRecord > 0)" ng-bind="'(' + EntityTotalRecord + ')'"></span>
                </h4>
            </div>
            <div class="col-sm-9">
                <div class="page-actions">                           
                    <div class="row">
                        <div class="col-sm-3 col-sm-offset-9">
                            <div class="input-icon right search-group open">
                                <a class="icons search-icon" ng-click="searchUnverifiedEntities();">                                          
                                    <i class="ficon-search" ng-hide="listObj.search"></i>
                                    <i ng-click="listObj.search='';searchUnverifiedEntities()" class="ficon-cross" ng-show="listObj.search"></i>
                                </a>

                                <input type="text" class="form-control" id="searchField" ng-model="listObj.search"  ng-keyup="searchUnverifiedEntities();"  placeholder="{{lang.Search + entitySearchPlaceholder}}">
                            </div>
                        </div>                                    
                    </div>
                </div>
            </div>
        </div>
    </div>  
    <div class="list-thumb-wrap">
        <ul class="list-thumb-grid row">
            <li ng-if="(unverifiedEntitiesListLoader && (pageNo === 1))" class="list-items col-xs-12 text-center">
                <!--Please wait loading...-->
                <span class="loader text-lg" style="display:block;">&nbsp;</span>
            </li>
            <li ng-if="(!unverifiedEntitiesListLoader && (unverifiedEntitiesListCount === 0))" class="list-items col-xs-12 text-center">
                Nothing found...
            </li>
            <li ng-if="((!unverifiedEntitiesListLoader && (pageNo === 1)) || (unverifiedEntitiesListCount > 0))" class="list-items col-xs-1" ng-repeat="( entityIndex, entityList ) in unverifiedEntitiesList | limitToObj: rowDisplayLimit">
                <div class="list-body" >
                    <figure class="figure">
                        <span uib-tooltip="Profile" class="icn circle-icn circle-default info-icn" ng-if="entityList.ModuleID == 3">
                            <i class="ficon-user"></i>
                        </span>
                        <span uib-tooltip="Group" class="icn circle-icn circle-default info-icn" ng-if="entityList.ModuleID == 1">
                            <i class="ficon-group"></i>
                        </span>
                        <span uib-tooltip="Page" class="icn circle-icn circle-default info-icn" ng-if="entityList.ModuleID == 18">
                            <i class="ficon-file-empty"></i>
                        </span> 
                        <span uib-tooltip="Event" class="icn circle-icn circle-default info-icn" ng-if="entityList.ModuleID == 14">
                            <i class="ficon-file-empty"></i>
                        </span> 

                        <a data-container="body" init-entiy-popover entity-list="entityList" data-placement="bottom auto" popover-template-url="{{ partialsUrl + 'profilePopover.html' }}">
                        
                            <!--User Profile Pic-->
                            <img err-Name="{{entityList.Name}}" ng-if="(entityList.ModuleID == 3)" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive"  >
                            
                            <!--Group Profile Pic-->
                            <img err-Name="{{entityList.Name}}" ng-if="(entityList.ModuleID == 1)" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive"  >
                            
                            <!--Page Profile Pic-->
                            <img err-Name="{{entityList.Name}}" ng-if="(entityList.ModuleID == 18)" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive"  >
                            
                        </a>
                    </figure>

                    <div class="content">
                        <h6 class="list-title">                                       
                            <span class="text" style="cursor:pointer;" ng-bind="entityList.Name"></span>
                            <span class="icn" ng-if="entityList.ModuleID == '1'"> 
                                <i class="ficon-close" ng-if="( ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 0 ) ) )"></i>
                                <i class="ficon-globe" ng-if="( ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 1 ) ) )"></i>
                                <i class="ficon-secret" ng-if="( ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 2 ) ) )"></i>
                            </span>
                        </h6>
                        <span class="text-sm-off bold" ng-if="entityList.ModuleID == 3">
                            <span ng-if="(!entityList.CityName && !entityList.CountryName)"> - </span>
                            <span ng-if="(entityList.CityName && !entityList.CountryName)" ng-bind="entityList.CityName"></span>
                            <span ng-if="(entityList.CityName && entityList.CountryName)"  ng-bind="entityList.CityName + ', ' + entityList.CountryName"></span>
                        </span>
                        <span class="text-sm-off bold" ng-if="entityList.ModuleID != 3 && ( entityList.Categories.length > 0 )">
                            <span ng-repeat="category in entityList.Categories" ng-bind="( ( $first ) ? category : (  ', ' + category ) )"></span>
                        </span>
                    </div>   
                </div>  
            </li>
            <li ng-if=" showSeeMore && ((!unverifiedEntitiesListLoader && (pageNo === 1)) || (rowDisplayLimit < EntityTotalRecord))" class="list-items col-xs-1">
                <div class="list-body">
                    <figure class="figure default">
                        <a class="circle" href="<?php echo base_url(); ?>admin/dashboard/detail">
                            <span class="text" ng-if="EntityTotalRecord<1010" ng-bind=" '+' +(EntityTotalRecord-10)"></span>

                            <span class="text" ng-if="EntityTotalRecord>=1010">999+</span>
                        </a>
                    </figure>
                    <div class="content">
                        <h6 ng-cloak ng-if="EntityTotalRecord>11" class="list-title">                                                                                   
                            <a class="read-more" href="<?php echo base_url(); ?>admin/dashboard/detail"><?php echo lang('See_More'); ?></a>
                        </h6>
                    </div>   
                </div>                           
            </li>
        </ul>
    </div>
</section>