<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/analytics/google_analytics') ?>"><?php echo lang('Analytics'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('GoogleAnalytics'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div id="googleAnalyticsCtrl"  ng-controller="googleAnalyticsCtrl" class="graph-pie-wrap ng-scope loaderparentdiv container">

<!--Info row-->
<div class="info-row row-flued">
    <h2><?php echo lang('GoogleAnalytics'); ?></h2>
</div>
<!--/Info row-->

<!--Main googleAnalyticsCtrl angular -->
    <section class="user-detial"  ng-init="googleAnalyticDataReport()">
        <ul class="ganalytics-total-list">
            <li class="blue total-visits total-height">
                <label>{{visits}}</label>
                <span><?php echo lang('GA_TotalVisits'); ?></span>
            </li>
            <li class="red unique-visitors total-height">
                <label>{{visitors}}</label>
                <span><?php echo lang('GA_UniqueVisitors'); ?></span>
            </li>
            <li class="green page-views total-height">
                <label>{{pageviews}}</label>
                <span><?php echo lang('GA_PageViews'); ?></span>
            </li>
            <li class="yellow bounce-rate total-height">
                <label>{{bounceRate}}%</label>
                <span><?php echo lang('GA_BounceRate'); ?></span>
            </li>
            <li class="purple new-sessions total-height">
                <label>{{percentNewSessions}}%</label>
                <span><?php echo lang('GA_NewSessions'); ?></span>
            </li>
            <li class="purple new-sessions total-height" ng-init="googleAnalyticsRegisteredUsers();">
                <label ng-bind="registeredNumberOfUsers"></label>
                <span>Number of registered Users</span>
            </li>
            
         </ul>
    </section>

    <section class="panel">
        <div class="panel-body">
        <section class="count-wrap">
            <div class="pull-right" style="width: 120px;">
                <select chosen data-disable-search="true" name="SelectedValueformetric" id="Selectorformetric" onchange="ChangeGoogleAnalytic();">
                    <option value="newUsers"><?php echo lang('GA_NewUsers'); ?></option>
                    <option value="pageviews"><?php echo lang('GA_Pageviews'); ?></option>
                    <option value="sessions"><?php echo lang('GA_Sessions'); ?></option>
                    <option value="users"><?php echo lang('GA_Users'); ?></option>
                    <option value="visits"><?php echo lang('GA_Visits'); ?></option>
                </select>
                <input type="hidden" name="filter_val" id="filter_val" value="newUsers"/>
            </div>
            <div class="tab-analytics">
                <a id="month" class="active" href="javascript:void(0);" ng-click="ChangeLineChart('month');"><?php echo lang('Filter_Monthly'); ?></a>
                <a id="week" href="javascript:void(0);" ng-click="ChangeLineChart('week');"><?php echo lang('Filter_Weekly'); ?></a>
                <a id="day" href="javascript:void(0);" ng-click="ChangeLineChart('day');"><?php echo lang('Filter_Daily'); ?></a>
                <a id="hour" href="javascript:void(0);" ng-click="ChangeLineChart('hour');"><?php echo lang('Filter_Hourly'); ?></a>
            </div>
        </section>
        <div id="linechartloaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
        <section ng-init="googleAnalyticsLineChart()" id="googleLineChart" style="width: 100%; height: 225px" class="text-center"></section>    
        </div>
    </section>

 <div class="row m-t">
        <aside class="col-sm-4 text-center">
            <section class="panel">
                <div class="panel-body">
                    <h5><?php echo lang('GA_OSVersion'); ?></h5>
                    <div id="oschartloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
                    <section ng-init="googleAnalyticsOSChart()" id="googleAnalyticOSChart"></section>
                    <div id="os_pie_chart_legend" class="legend_div"></div>
                    <div class="clear"></div>
                </div>
            </section>        
        </aside>
        <aside class="col-sm-4 text-center">
            <section class="panel">
                <div class="panel-body">
                    <h5><?php echo lang('GA_BrowserVersion'); ?></h5>
                    <div id="browserchartloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
                    <section ng-init="googleAnalyticsBrowserChart()" id="googleAnalyticsBrowserChart" class="xgachartdiv"></section>
                    <div id="browser_chart_legend" class="legend_div"></div>
                    <div class="clear"></div>
                </div>
            </section>
        </aside>
        <aside class="col-sm-4 text-center">
            <section class="panel">
                <div class="panel-body">
                    <h5><?php echo lang('GA_deviceType'); ?></h5>
                    <div id="devicetypechartloaderdiv" class="xanalyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
                    <section ng-init="googleAnalyticsDeviceTypeChart()" id="googleAnalyticsDeviceTypeChart" class="xgachartdiv"></section>
                    <div id="device_chart_legend" class="legend_div"></div>
                    <div class="clear"></div>
                </div>
            </section>
        </aside>
 </div> 
   
   <div class="container">
    <section class="panel">
        <div class="panel-body">
            <div class="text-center">
                <h5><?php echo lang('GA_LocationInformation'); ?></h5>
                <div id="geoChartLoaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
                <section id="googleAnalyticsGeoChart" class="googlegeochart"></section>
            </div>
        </div>
    </section>
    </div>
</div>
</section>