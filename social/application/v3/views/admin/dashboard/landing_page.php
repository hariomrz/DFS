<div id="UserListCtrl" ng-controller="UserListCtrl">
    <div ng-controller="DashboardFeedController" ng-init="getActivityList();" id="DashboardFeedController">  
        <div class="new-updates" ng-if="newUpdateCount > 0">
            <a ng-click="prependNewRecords()">{{newUpdateCount}} {{newUpdateCountText}}
                <i class="ficon-arrow-long-up"></i>
            </a>
        </div>
        <?php $this->load->view('admin/dashboard/dashboardFilters'); ?>
        <section class="main-container">
            <div class="container">
                <div class="page-heading">
                    <div class="row">
                        <div class="col-xs-3"> 
                            <h4 class="page-title">{{page_heading}} ({{activityTotalRecord}})</h4>
                            
                        </div>
    <!--                    <div class="col-xs-9">
                            <div class="page-actions">
                                <div class="row ">
                                    <div class="col-xs-4 col-xs-offset-8">
                                        <div class="input-icon right search-group open">
                                            <a class="icons search-icon">
                                                <svg class="svg-icons" id="search" width="14px" height="14px">
                                                <use xlink:href="assets/img/sprite.svg#searchIco"></use>
                                                </svg>
                                            </a>
                                            <input type="text" class="form-control" id="searchField" placeholder="Posts/Comments (5)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>-->
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-7">
                        <?php $this->load->view('admin/dashboard/dashboardFeedList'); ?>
                    </div>
                    <div class="col-xs-5">
                        <?php $this->load->view('admin/dashboard/dashboardFeedTagBox'); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <business-card data="businesscard"></business-card>
</div>

<input type="hidden" id="LoggedInUserGUID" value="<?php echo $this->session->userdata('AdminLoginSessionKey') ?>" />