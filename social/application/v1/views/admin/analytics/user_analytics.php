<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/analytics/user') ?>"><?php echo lang('Analytics'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div id="UserAnalyticsCtrl"  ng-controller="UserAnalyticsCtrl" class="graph-pie-wrap ng-scope loaderparentdiv container" ng-init="userAnalytics();">

        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Hide Post</h2>
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table1">
                                              
                        
                        <tr>  
                            <td style="width:35%">
                                <span class="bold">Total no of users who used Hide Post so far:</span>
                            </td>
                            <td>
                                {{TotalUser}}
                            </td>
                        </tr>                        
                        <tr>  
                            <td style="width:35%">
                                <span class="bold">Total no of users who used Hide Post today:</span>
                            </td>
                            <td>
                                {{TotalTodayUser}}
                            </td>
                        </tr>

                </table>
            </div>
            </div>

            
        </div>

        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Preferred Category</h2>
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table1">                   

                        <tr>  
                            <td style="width:35%">
                                <span class="bold">Total no of users who mentioned their preferred category:</span>
                            </td>
                            <td>
                                {{TotalCategoryUser}}
                            </td>
                        </tr>

                </table>
            </div>
            </div>

            
        </div>

        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Most Popular Categories</h2>
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table1">                   
                    <thead>
                        <tr>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="(key, category) in PopularCategory">
                            <td>
                                {{category.Name}} <span class="bold">({{category.cnt}})</span>
                            </td>
                        </tr>
                    <tbody>
                </table>
            </div>
            </div>

            
        </div>


</div>
</section>