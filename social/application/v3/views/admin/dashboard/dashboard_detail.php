<div ng-controller="DashboardDetailController" ng-init="getUnverifiedEntities(listObj.entityType);">
    <section class="filter-default">
        <div class="container">
            <nav class="navbar navbar-tabs">
                <ul class="nav navbar-nav">
                    <li ng-if="!(Settings.m18 == 0 && Settings.m1 == 0)" ng-class="{ 'disabled' : ( ( activeTab !== 'All' ) && unverifiedEntitiesListLoader  ), 'active': ( activeTab === 'All' ) }"><a href="javascript:void(0);" ng-click="changeTab($event, 'ALL', 'All');" data-toggle="tab">All</a></li>
                    <li ng-class="{ 'disabled' : ( ( activeTab !== 'Profiles' ) && unverifiedEntitiesListLoader  ), 'active': ( activeTab === 'Profiles' ) }"><a href="javascript:void(0);" ng-click="changeTab($event, 'USERS', 'Profiles');" data-toggle="tab"><i class="ficon-user"></i> Profiles</a></li>
                    <li ng-if="Settings.m18 == 1" ng-class="{ 'disabled' : ( ( activeTab !== 'Pages' ) && unverifiedEntitiesListLoader  ), 'active': ( activeTab === 'Pages' ) }"><a href="javascript:void(0);" ng-click="changeTab($event, 'PAGES', 'Pages');" data-toggle="tab"><i class="ficon-file-empty"></i> Pages</a></li>
                    <li ng-if="Settings.m1 == 1" ng-class="{ 'disabled' : ( ( activeTab !== 'Groups' ) && unverifiedEntitiesListLoader  ), 'active': ( activeTab === 'Groups' ) }"><a href="javascript:void(0);" ng-click="changeTab($event, 'GROUPS', 'Groups');" data-toggle="tab"><i class="ficon-group"></i> Groups</a></li>
                </ul>
            </nav>
        </div>
    </section>
    <section class="main-container" 
             infinite-scroll="getUnverifiedEntities(listObj.entityType)" 
             infinite-scroll-use-document-bottom="true"
             infinite-scroll-immediate-check="false"
             infinite-scroll-disabled="unverifiedEntitiesListLoader"
    >
        <div class="container">
            <div class="page-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <h4 class="page-title" ng-bind="activeTab + ' (' + EntityTotalRecord + ')'"></h4>
                    </div>
                    <div class="col-xs-9">
                        <div class="page-actions">
                            <div class="row ">
                                <div class="col-xs-4 col-xs-offset-8">
                                    <div class="input-icon right search-group open">
                                        <a class="icons search-icon">
                                             <i class="ficon-search" ng-hide="listObj.search"></i>
                                             <i ng-click="listObj.search='';searchUnverifiedEntities()" class="ficon-cross" ng-show="listObj.search"></i>
                                        </a>

                                        <input type="text" class="form-control" id="searchField" ng-model="listObj.search"  ng-keyup="searchUnverifiedEntities();"  placeholder="<?php echo lang('Search'); ?> {{ (activeTab=='All') ? entitySearchPlaceholder : activeTab ; }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" >
                <div class="tab-content">                    
                    <div class="tab-pane fade active in" id="allcontent">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-sm-3" class="col-sm-3" ng-repeat="( entityIndex, entityList ) in unverifiedEntitiesList">
                                    <div ng-if="entityList.ModuleID=='1'" class="content-listing">
                                        <span class="card-type">GROUP</span>
                                        <div class="popover-body">                                                
                                            <ul class="card-listing">
                                                <li class="list-items"> 
                                                    <figure ng-click="$emit('openGroupDetailModalPopup', { ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });" class="user-thumbnail cursor-pointer">
                                                        <img ng-if="( entityList.ProfilePicture && ( entityList.ModuleID == 1 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive" alt="No image" title="{{entityList.Name}}">
                                                        <img ng-if="( !entityList.ProfilePicture && ( entityList.ModuleID == 1 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/user_default.jpg'}}" class="img-circle img-responsive" alt="No image" title="{{entityList.Name}}">
                                                    </figure>
                                                    <div class="list-body">
                                                        <div class="content">
                                                            <h6 ng-click="$emit('openGroupDetailModalPopup', { ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });" class="list-title ellipsis cursor-pointer"><span class="text" ng-bind="entityList.Name"></span></h6> 
                                                            <ul class="list-info">  
                                                                <li class="group-type">
                                                                    <span class="group-name ellipsis">
                                                                        <span class="text" ng-repeat="category in entityList.Categories" ng-bind="( ( $first ) ? category : (  ', ' + category ) )"></span>
                                                                    </span>
                                                                    <i tooltip-append-to-body="true" uib-tooltip="Close" class="ficon-close-group" ng-if="( ( entityList.ModuleID == 1 ) && ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 0 ) ) )"></i>
                                                                    <i tooltip-append-to-body="true" uib-tooltip="Public" class="ficon-globe" ng-if="( ( entityList.ModuleID == 1 ) && ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 1 ) ) )"></i>
                                                                    <i tooltip-append-to-body="true" uib-tooltip="Secret" class="ficon-secret" ng-if="( ( entityList.ModuleID == 1 ) && ( ( entityList.IsPublic != '' ) && ( entityList.IsPublic == 2 ) ) )"></i>
                                                                    <i tooltip-append-to-body="true" uib-tooltip="{{entityList.Popularity}}" class="ficon-trending"></i>
                                                                </li>
                                                            </ul>
                                                            <ul class="activity-detail clearfix"> 
                                                                <li ng-if="( ( entityList.MemberCount != '' ) && ( entityList.MemberCount > 0 ) )" ng-bind="( ( entityList.MemberCount > 1 ) ? entityList.MemberCount + ' Members' : entityList.MemberCount + ' Member' )"></li>
                                                                <li ng-if="( ( entityList.PostCount != '' ) && ( entityList.PostCount > 0 ) )" ng-bind="( ( entityList.PostCount > 1 ) ? entityList.PostCount + ' Posts' : entityList.PostCount + ' Post' )"></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li> 
                                            </ul>
                                        </div>
                                        <div class="popover-footer">
                                            <div class="btn-toolbar btn-toolbar-center">
                                                <div class="pull-left">
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'delete', entityIndex);">
                                                        <span class="icn"><i class="ficon-bin"></i></span>
                                                    </a>
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="$emit('openMsgModalPopup', { Name: entityList.Name, ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });">
                                                        <span class="icn"><i class="ficon-envelope"></i></span>
                                                    </a>
                                                </div>
                                                <div class="pull-right">
                                                    <button class="btn btn-xs btn-default fe-button" ng-click="updateEntityTags(entityList, entityIndex);" ng-class="{'unfeatured': entityList.Featured_TagID != 0 }" ng-click="Featured = true"> 
                                                        <span class="icn" ng-if="entityList.Featured_TagID != 0" ng-cloak><i class="ficon-check"></i></span>
                                                        <span ng-if="entityList.Featured_TagID == 0" class="text fe-feature">Feature</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text fe-feature">Featured</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text un-feature">Unfeature</span>
                                                    </button>
                                                    <button ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'verify', entityIndex);" class="btn btn-xs btn-default verifyed" ng-disabled="entityList.Verified=='1'" ng-class="{'active': entityList.Verified=='1' }">
                                                        <span class="icn" ng-if="entityList.Verified=='1'" ng-cloak><i class="ficon-check"></i></span>
                                                        <span class="text">Verify</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div ng-if="entityList.ModuleID=='3'" class="content-listing">
                                        <div class="popover-body">  
                                            <ul class="card-listing">
                                                <li class="list-items">
                                                    <figure class="user-thumbnail">
                                                        <img ng-if="( entityList.ProfilePicture && ( entityList.ModuleID == 3 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive"  >
                                                        <img ng-if="( !entityList.ProfilePicture && ( entityList.ModuleID == 3 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/user_default.jpg'}}" class="img-circle img-responsive"  >
                                                    </figure>
                                                    <div class="list-body">
                                                        <div class="content">

                                                            <h6 class="list-title ellipsis">
                                                                <span ng-click="showUserPersona(entityList.ModuleEntityID,entityList.UserGUID,entityList.Name)" style="cursor:pointer;" class="text" ng-cloak ng-if="entityList.Name" ng-bind="entityList.Name"></span>
                                                                <span ng-cloak ng-if="entityList.Name==''" class="blank-view">&nbsp;</span>

                                                            </h6>
                                                            <span class="text-sm-off bold">
                                                                <span ng-if="( entityList.CityName && !entityList.CountryName )" ng-bind="entityList.CityName"></span>
                                                                <span ng-if="( entityList.CityName && entityList.CountryName )" ng-bind="entityList.CityName + ', ' + entityList.CountryName"></span>
                                                                <span ng-if="entityList.CityName=='' && entityList.CountryName==''" class="blank-view">&nbsp;</span>
                                                            </span>
                                                            <ul class="list-info">
                                                                <li>
                                                                    <span class="text" ng-bind="entityList.Email"></span>
                                                                    <span ng-cloak ng-if="entityList.SourceID=='3' || entityList.SourceID=='2' || entityList.SourceID=='7'" class="icn circle-icn circle-default">
                                                                        <i ng-cloak ng-if="entityList.SourceID=='2'" class="ficon-facebook"></i>
                                                                        <i ng-cloak ng-if="entityList.SourceID=='7'" class="ficon-linkedin"></i>
                                                                        <i ng-cloak ng-if="entityList.SourceID=='3'" class="ficon-twitter"></i>
                                                                    </span>
                                                                </li>
                                                                <li><span ng-if="( entityList.DOB && ( entityList.DOB != '0000-00-00' ) )" class="text" ng-bind="entityList.DOB | date: 'MMM d, y, '"></span>
                                                                <span ng-if="( entityList.Gender != '' )" class="text" ng-bind=" ( ( entityList.Gender == 0 ) ? 'O' : ( ( entityList.Gender == 1 ) ? 'M' : 'F' ) ) "></span> </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li> 
                                            </ul>
                                        </div>
                                        <div class="popover-footer">
                                            <div class="btn-toolbar btn-toolbar-center">
                                                <div class="pull-left">
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'delete', entityIndex);">
                                                        <span class="icn"><i class="ficon-bin"></i></span>
                                                    </a>
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="$emit('openMsgModalPopup', { Name: entityList.Name, ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });">
                                                        <span class="icn"><i class="ficon-envelope"></i></span>
                                                    </a>
                                                </div>
                                                <div class="pull-right">
                                                    <button class="btn btn-xs btn-default fe-button" ng-click="updateEntityTags(entityList, entityIndex);" ng-class="{'unfeatured': entityList.Featured_TagID != 0 }" ng-click="Featured = true"> 
                                                        <span class="icn" ng-if="entityList.Featured_TagID != 0" ng-cloak><i class="ficon-check"></i></span>
                                                        <span ng-if="entityList.Featured_TagID == 0" class="text fe-feature">Feature</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text fe-feature">Featured</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text un-feature">Unfeature</span>
                                                    </button>
                                                    <button ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'verify', entityIndex);" class="btn btn-xs btn-default verifyed" ng-disabled="entityList.Verified=='1'" ng-class="{'active': entityList.Verified=='1' }">
                                                        <span class="icn" ng-if="entityList.Verified=='1'" ng-cloak><i class="ficon-check"></i></span>
                                                        <span class="text">Verify</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div ng-if="entityList.ModuleID=='18'" class="content-listing">
                                        <span class="card-type page">PAGE</span>
                                         <div class="popover-body">   
                                            <ul class="card-listing">
                                                <li class="list-items"> 
                                                    <figure ng-click="$emit('openGroupDetailModalPopup', { ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });" class="user-thumbnail cursor-pointer">
                                                        <img ng-if="( entityList.ProfilePicture && ( entityList.ModuleID == 18 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/' + entityList.ProfilePicture}}" class="img-circle img-responsive" alt="No image" title="{{entityList.Name}}">
                                                        <img ng-if="( !entityList.ProfilePicture && ( entityList.ModuleID == 18 ) )" ng-src="<?php echo IMAGE_SERVER_PATH; ?>{{'upload/profile/user_default.jpg'}}" class="img-circle img-responsive" alt="No image" title="{{entityList.Name}}">
                                                    </figure>
                                                    <div class="list-body">
                                                        <div class="content">
                                                            <h6 ng-click="$emit('openGroupDetailModalPopup', { ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });" class="list-title ellipsis cursor-pointer"><span class="text" ng-bind="entityList.Name"></span></h6> 
                                                           <ul class="list-info">  
                                                                <li class="group-type">
                                                                    <span class="group-name ellipsis" ng-repeat="category in entityList.Categories" ng-bind="( ( $first ) ? category : (  ', ' + category ) )"></span> 
                                                                    <i uib-tooltip="{{entityList.Popularity}}" class="ficon-trending"></i>
                                                                </li>
                                                            </ul> 
                                                            <ul class="activity-detail clearfix"> 
                                                                <li ng-if="( ( entityList.MemberCount != '' ) && ( entityList.MemberCount > 0 ) )" ng-bind="( ( entityList.MemberCount > 1 ) ? entityList.MemberCount + ' Followers' : entityList.MemberCount + ' Follower' )"></li>
                                                                <li ng-if="( ( entityList.PostCount != '' ) && ( entityList.PostCount > 0 ) )" ng-bind="( ( entityList.PostCount > 1 ) ? entityList.PostCount + ' Posts' : entityList.PostCount + ' Post' )"></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li> 
                                            </ul>
                                        </div>
                                        <div class="popover-footer">
                                            <div class="btn-toolbar btn-toolbar-center">
                                                <div class="pull-left">
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'delete', entityIndex);">
                                                        <span class="icn"><i class="ficon-bin"></i></span>
                                                    </a>
                                                    <a class="btn btn-xs btn-icn btn-default" ng-click="$emit('openMsgModalPopup', { Name: entityList.Name, ModuleID: entityList.ModuleID, ModuleEntityID: entityList.ModuleEntityID });">
                                                        <span class="icn"><i class="ficon-envelope"></i></span>
                                                    </a>
                                                </div>
                                                <div class="pull-right">
                                                    <button class="btn btn-xs btn-default fe-button" ng-click="updateEntityTags(entityList, entityIndex);" ng-class="{'unfeatured': entityList.Featured_TagID != 0 }" ng-click="Featured = true"> 
                                                        <span class="icn" ng-if="entityList.Featured_TagID != 0" ng-cloak><i class="ficon-check"></i></span>
                                                        <span ng-if="entityList.Featured_TagID == 0" class="text fe-feature">Feature</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text fe-feature">Featured</span>
                                                        <span ng-if="entityList.Featured_TagID != 0" class="text un-feature">Unfeature</span>
                                                    </button>
                                                    <button ng-click="updateEntity(entityList.ModuleID, entityList.ModuleEntityID, 'verify', entityIndex);" class="btn btn-xs btn-default verifyed" ng-disabled="entityList.Verified=='1'" ng-class="{'active': entityList.Verified=='1' }">
                                                        <span class="icn" ng-if="entityList.Verified=='1'" ng-cloak><i class="ficon-check"></i></span>
                                                        <span class="text">Verify</span>
                                                    </button>
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
        </div>
    </section>
</div>
<div id="UserListCtrl" ng-controller="UserListCtrl">
    <?php $this->load->view('admin/users/persona/user_persona');?>
</div>
