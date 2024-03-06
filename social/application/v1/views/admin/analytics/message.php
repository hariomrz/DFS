<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/analytics/message') ?>"><?php echo lang('Analytics'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('message'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div id="MessageAnalyticsCtrl"  ng-controller="MessageAnalyticsCtrl" class="graph-pie-wrap ng-scope loaderparentdiv container" ng-init="messageAnalytics();">

        <!--Info row-->
        <div class="info-row row-flued">
            <h2><?php echo lang('message'); ?></h2>
        </div>
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table1">
                        <tr>  
                            <td style="width:25%">
                                <span class="bold">Total messages exchanged so far:</span>
                            </td>
                            <td>
                                {{TotalMessage}}
                            </td>
                        </tr>

                        <tr>  
                            <td style="width:25%">
                                <span class="bold">Total messages exchanged today:</span>
                            </td>
                            <td>
                                {{TotalTodayMessage}}
                            </td>
                        </tr>
                        
                        <tr>  
                            <td style="width:25%">
                                <span class="bold">Total no of users who used messages so far:</span>
                            </td>
                            <td>
                                {{TotalUser}}
                            </td>
                        </tr>                        
                        <tr>  
                            <td style="width:25%">
                                <span class="bold">Total no of users who used messages today:</span>
                            </td>
                            <td>
                                {{TotalTodayUser}}
                            </td>
                        </tr>
                        

                        <tr>  
                            <td style="width:25%">
                                <span class="bold">Total no of users who sent message today:</span>
                            </td>
                            <td>
                                {{TotalUserSentMessage}}
                            </td>
                        </tr>

                </table>
            </div>
            </div>

            
        </div>


</div>
</section>