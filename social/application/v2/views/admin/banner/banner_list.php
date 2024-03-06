<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Ads</span></li>                    
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">

<div class="container" ng-controller="AdvertiseCtrl" ng-cloak ng-init="orderByField = 'BlogUniqueID'; PageType='List';">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2>Ads MANAGEMENT</h2>
        <div class="info-row-right"> 
            <a class="btn-link" href="<?php echo site_url('admin/advertise/add_banner');?>">
                <ins class="buttion-icon"><i class="icon-add">&nbsp;</i></ins>
                <span>Add</span>
            </a>
            
            <a class="btn-link" href="<?php echo site_url('admin/advertise/default_image');?>">
                <span>Set Default Image</span>
            </a>
            
            <div class="text-field search-field" data-type="focus">
                <div class="search-block">
                    <input type="text" ng-model="searchKey" id="bannerSearchField">
                    <div class="search-remove">
                        <i class="icon-close10" id="clearText" ng-click="reset_text_search();">&nbsp;</i>
                    </div>
                </div> 
                <input type="button" id="searchButton" class="icon-search search-btn" ng-click="FilterByText();">
            </div>

            <div class="select" data-type="focus">
                <select chosen
                        data-ng-model="searchBannerStatus" ng-change="FilterBanner();" 
                        ng-options="k as v for (k, v) in BannerStatus"
                        autocomplete="off">
                    <option value="">Select Status</option>
                </select>
            </div>

            <div class="select" data-type="focus">
                <select chosen
                        data-ng-model="searchBannerModule" ng-change="FilterBanner();"
                        ng-options="k as v for (k, v) in BannerModule"
                        autocomplete="off">
                    <option value="">Select Module</option>
                </select>
            </div>

        </div>
    </div>
    <!--/Info row-->

    <div class="row-flued" id="ArticleListCtrl" ng-init="orderByField = 'BlogUniqueID';" >
        <div class="panel panel-secondary">
            <div class="panel-body">
                <table class="table table-hover ">
                <thead>
            <tr>

                <th id="BlogUniqueID" class="ui-sort selected" ng-click="orderByField = 'BlogUniqueID';
                            reverseSort = !reverseSort;
                            sortBannerBY('BlogUniqueID')">
            <div class="shortdiv sortedDown">Module Name<span class="icon-arrowshort ">&nbsp;</span></div>
            </th>
            <th id="BlogTitle" class="ui-sort" ng-click="orderByField = 'BlogTitle';
                        reverseSort = !reverseSort;
                        sortBannerBY('BlogTitle')">
            <div class="shortdiv">Title<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>

            <th id="BannerSize" class="ui-sort " ng-click="orderByField = 'BannerSize';
                        reverseSort = !reverseSort;
                        sortBannerBY('BannerSize')">                           
            <div class="shortdiv ">Size<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>

            <th id="Advertiser" class="ui-sort" ng-click="orderByField = 'Advertiser';
                        reverseSort = !reverseSort;
                        sortBannerBY('Advertiser')">
            <div class="shortdiv">Advertiser<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>
            <th id="StartDate" class="ui-sort" ng-click="orderByField = 'StartDate';
                        reverseSort = !reverseSort;
                        sortBannerBY('StartDate')">
            <div class="shortdiv">Start Date<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>
            <th id="EndDate" class="ui-sort" ng-click="orderByField = 'EndDate';
                        reverseSort = !reverseSort;
                        sortBannerBY('EndDate')">
            <div class="shortdiv">End Date<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>

            <th id="StatusName" class="ui-sort" ng-click="orderByField = 'StatusName';
                        reverseSort = !reverseSort;
                        sortBannerBY('StatusName')">
            <div class="shortdiv">Status<span class="icon-arrowshort hide">&nbsp;</span></div>
            </th>
            <th>Actions</th>
            </tr>
</thead>
                <tbody>
            <tr class="rowtr" ng-repeat="banner in listData" ng-class="{selected : isSelected(banner)}" ng-init="banner.indexArr = $index">
                <td ng-bind="(BannerModule[banner.BlogUniqueID]) ? BannerModule[banner.BlogUniqueID] : banner.BlogUniqueID"></td>
                <td ng-bind="banner.BlogTitle"></td>
                <td ng-bind="banner.BannerSize"></td>
                <td ng-bind="banner.Advertiser"></td>
                <td ng-bind="banner.StartDate"></td>
                <td ng-bind="banner.EndDate"></td>
                <td ng-if="banner.StatusName == 'Expire'" ng-bind="'Expired'"></td>
                <td ng-if="banner.StatusName != 'Expire'" ng-bind="(banner.Status == 2) ? 'Active' : ((banner.Status == 4) ? 'Inactive' : banner.StatusName)"></td>
                <td><a href="javascript:void(0);" ng-click="SetCurretBannerData(banner);" class="user-action" onclick="userActiondropdown();"><i class="icon-setting">&nbsp;</i></a></td>
            </tr>
</tbody>
            
                </table>
                <div class="simple-pagination" data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true"></div>
            </div>
        </div>
        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu userActiondropdown">
            <li><a href="<?php echo site_url('admin/advertise/add_banner'); ?>/{{CurretBannerData.BlogID}}">Edit</a></li>
            <!--li><a href="javascript:void(0);" onClick="openPopDiv('confirmePopup', 'bounceInDown');">Delete</a></li-->  
            <li id="ActionInactive" data-ng-show="CurretBannerData.Status==2 && CurretBannerData.StatusName != 'Expire'"><a ng-click="SetStatus(4);" href="javascript:void(0);">Make Inactive</a></li>
            <li id="ActionActive" data-ng-show="CurretBannerData.Status==4 && CurretBannerData.StatusName != 'Expire'"><a ng-click="SetStatus(2);" href="javascript:void(0);">Make Active</a></li>
            <li id="ActionDelete" data-ng-show="CurretBannerData.Status!=3"><a ng-click="SetStatus(3);" href="javascript:void(0);">Delete</a></li>

        </ul>
        <!--/Actions Dropdown menu--> 

        <span id="result_message" class="result_message">No records found.</span>

        <!-- Confirmation popup START -->
        <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
            <div class="popup-content">
                <p class="text-center">{{confirmationMessage}}</p>
                <div class="communicate-footer text-center">
                    <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                    <button class="button" ng-click="updateBannerStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                </div>
            </div>
        </div>
        <!-- Confirmation popup END -->

    </div>
</div>
</section>

