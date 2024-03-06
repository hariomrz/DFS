<!--Bread crumb-->
<div class="bread-crumb">
    <ul>
        <li>
            <a target="_self" href="<?php echo base_url('admin/analytics/email_analytics') ?>"><?php echo lang('Analytics'); ?></a>
        </li>
        <li>
            <i class="icon-rightarrow">&nbsp;</i>
        </li>
        <li>
            <a href="javascript:void(0);" class="selected"><?php echo lang('EmailAnalytics'); ?></a>
        </li>
    </ul>
</div>
<!--/Bread crumb-->
<div class="clearfix">&nbsp;</div>

<!--Info row-->
<div class="info-row row-flued">
    <h2><?php echo lang('EmailAnalytics'); ?></h2>
</div>
<!--/Info row-->

<aside class="text-center">
    <section class="graph-pie-wrap" ng-controller="emailAnalyticsCtrlOld" ng-init="emailAnalyticsChart()" id="emailAnalyticsCtrlOld">
        <div id="emailAnalyticsChart" class="email_analytics_chart_old"></div>
    </section>
</aside>