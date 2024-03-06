<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
        <li>
            <span><a target="_self" href="<?php echo base_url('admin/analytics/google_analytics') ?>"><?php echo lang('Analytics'); ?></a></span>
        </li>
        <li>
            <i class="icon-rightarrow">&nbsp;</i>
        </li>
        <li>
            <span><a target="_self" href="<?php echo base_url('admin/analytics/google_analytics') ?>"><?php echo lang('GoogleAnalytics'); ?></a></span>
        </li>
        <li>
            <i class="icon-rightarrow">&nbsp;</i>
        </li>
        <li>
            <span><?php echo lang('GA_Devices'); ?></span>
        </li>
    </ul>
    </div>
    </div>
    </div>
</div>
<!--/Bread crumb-->

<!--Main googleAnalyticsDevicesCtrl angular -->
<section class="main-container">
<div class="container graph-pie-wrap ng-scope" id="googleAnalyticsDevicesCtrl"  ng-controller="googleAnalyticsDevicesCtrl" >

<!--Info row-->
<div class="info-row row-flued">
    <h2><?php echo lang('GA_Devices'); ?></h2>
</div>
<!--/Info row-->

    <section class="count-wrap">
        <div class="pull-right">
            <select chosen data-disable-search="true" name="SelectedValueformetric" id="Selectorformetric" onchange="ChangeGoogleAnalyticDeviceInfo();">
                <option value="newUsers"><?php echo lang('GA_NewUsers'); ?></option>
                <option value="pageviews"><?php echo lang('GA_Pageviews'); ?></option>
                <option value="sessions"><?php echo lang('GA_Sessions'); ?></option>
                <option value="users"><?php echo lang('GA_Users'); ?></option>
                <option value="visits"><?php echo lang('GA_Visits'); ?></option>
            </select>
            <input type="hidden" name="filter_val" id="filter_val" value="newUsers"/>
        </div>
        <!--<div class="text-field select" data-type="focus">
            <select class="selectbox_ele" id="Selectorformetric" name="SelectedValueformetric">
                <option value="newUsers"><?php echo lang('GA_NewUsers'); ?></option>
                <option value="pageviews"><?php echo lang('GA_Pageviews'); ?></option>
                <option value="sessions"><?php echo lang('GA_Sessions'); ?></option>
                <option value="users"><?php echo lang('GA_Users'); ?></option>
                <option value="visits"><?php echo lang('GA_Visits'); ?></option>
            </select>
            <input type="hidden" name="filter_val" id="filter_val" value="newUsers"/>
        </div>-->
    </section>
    <section class="graph-pie-wrap four-child">
        <div class="devicechart_box loaderparentdiv">
            <h5><?php echo lang('GA_OSVersion'); ?></h5>
            <div id="oschartloaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
            <section ng-init="googleAnalyticsOSChart()" id="googleAnalyticOSChart" class="text-center" style="min-height: 350px;"></section>
        </div>
        <div class="devicechart_box loaderparentdiv">
            <h5><?php echo lang('GA_BrowserVersion'); ?></h5>
            <div id="browserchartloaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
            <section ng-init="googleAnalyticsBrowserChart()" id="googleAnalyticsBrowserChart" class="text-center" style="min-height: 350px;"></section>
        </div>
    </section>
    <section class="graph-pie-wrap mTop35 map-wrap" ng-init="googleAnalyticDeviceDataReport()">
        <div class="graphs-left loaderparentdiv devicereportdiv">
            <div id="reportLoaderDiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
            <ul class="graph-list">
                <li>
                    <label><?php echo lang('GA_Visits'); ?></label>
                    <span class="graph-value" id="iidsessions">{{visits}}</span>
                </li>
                <li>
                    <label><?php echo lang('GA_Pageviews'); ?></label>
                    <span class="graph-value" id="idpageviews">{{pageviews}}</span>
                </li>
                <li>
                    <label><?php echo lang('GA_Users'); ?></label>
                    <span class="graph-value" id="idbouncerate">{{users}}</span>
                </li>
                <li>
                    <label><?php echo lang('GA_NewUsers'); ?></label>
                    <span class="graph-value" id="idbouncerate">{{newusers}}</span>
                </li>
                <li>
                    <label><?php echo lang('GA_Sessions'); ?></label>
                    <span class="graph-value" id="idnewsessions">{{sessions}}</span>
                </li>
            </ul>
        </div>
        <div class="devicechart_box loaderparentdiv">
            <h5><?php echo lang('GA_deviceType'); ?></h5>
            <div id="devicetypechartloaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
            <section ng-init="googleAnalyticsDeviceTypeChart()" id="googleAnalyticsDeviceTypeChart" class="text-center" style="min-height: 350px;"></section>
        </div>
    </section>
</div>
</section>