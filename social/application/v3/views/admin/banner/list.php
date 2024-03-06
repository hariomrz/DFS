<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Banner</span></li>                    
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">

    <div class="container" ng-controller="AdvertiseCtrl" ng-cloak ng-init="orderByField = 'BlogUniqueID'; PageType = 'List';">
        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Banner Management</h2>
            <div class="info-row-right"> 
                <a class="btn-link" href="<?php echo site_url('admin/banner/add'); ?>">
                    <ins class="buttion-icon"><i class="icon-add">&nbsp;</i></ins>
                    <span>Add</span>
                </a>
            </div>
        </div>
        <!--/Info row-->

        <div class="row-flued" id="ArticleListCtrl" ng-init="orderByField = 'BlogUniqueID';" >
            <div class="panel panel-secondary">
                <div class="panel-body">
                    <table class="table table-hover ">
                        <thead>
                            <tr>

                                <th id="BannerID">
                                    <div class="shortdiv sortedDown">Image<span class="icon-arrowshort ">&nbsp;</span></div>
                                </th>            
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="rowtr" ng-repeat="banner in listData" ng-class="{selected : isSelected(banner)}" ng-init="banner.indexArr = $index">
                                <td>
                                    <img ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/banner/' ?>{{banner.BlogImage}}"  />
                                </td>
                                <td><a href="javascript:void(0);" ng-click="SetCurretBannerData(banner);" class="user-action" onclick="userActiondropdown();"><i class="icon-setting">&nbsp;</i></a></td>
                            </tr>
                        </tbody>

                    </table>
                    <div class="simple-pagination" data-pagination="" total-items="totalRecord" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true"></div>
                </div>
            </div>
            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu userActiondropdown">
                <li id="ActionDelete" data-ng-show="CurretBannerData.Status != 3"><a ng-click="SetStatus(3);" href="javascript:void(0);">Delete</a></li>
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

