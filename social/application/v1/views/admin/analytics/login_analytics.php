<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a><a target="_self" href="<?php echo base_url('admin/analytics/login_analytics') ?>"><?php echo lang('Analytics'); ?></a></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('LoginAnalytics_LoginAnalytics'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--/Bread crumb-->
<section class="main-container">
<div class="container"> 
<!--Info row-->
<div class="info-row row-flued">
    <h2><?php echo lang('LoginAnalytics_LOGINANALYTICS1'); ?></h2>
</div>
<!--/Info row-->

<!--Main LoginCtrl angular -->
<section id="loginAnalyticsCtrl"  ng-controller="loginAnalyticsCtrl" class="xgraph-pie-wrap ng-scope">
    
    <div class="panel">
        <div class="panel-body" id="loginanalytic_row1">
             <section class="count-wrap">
                <label class="login-counts">
                   <span id="logincount_label"></span>
                   <small><?php echo lang('LoginAnalytics_LOGINS'); ?></small>
                </label>
                <div class="pull-right" style="width: 120px;">
                    <select chosen data-disable-search="true" name="analytics_filter" id="analytics_filter" onchange="ChangeAnalyticData('login_analytics');">
                        <option value="1"><?php echo lang('Filter_Monthly'); ?></option>
                        <option value="2"><?php echo lang('Filter_Weekly'); ?></option>
                        <option selected="selected" value="3"><?php echo lang('Filter_Daily'); ?></option>
                    </select>
                    <input type="hidden" name="filter_val" id="filter_val" value="3"/>
                </div>
                <aside class="login-number">
                    <span><?php echo lang('LoginAnalytics_NoOfLogins'); ?></span>
                </aside>
            </section> 
            <section ng-init="loginAnalyticsChart()" id="loginLineChart" style="width: 1153px; min-height: 225px" class="text-center"></section>
        </div>
    </div>
    <section id="loginanalytic_row2" class="graph-pie-wrap four-child mTop35">

        <aside class="text-center">
            <h5><?php echo lang('User_UserProfile_SourcesOfLogins'); ?></h5>
            <section ng-init="loginSourceLoginChart()" id="SourceLoginChart" class="loginchart_div">
            
            </section>
        </aside>

        <aside class="text-center">
            <h5><?php echo lang('LoginAnalytics_Devices'); ?></h5>
            <section id="loginDeviceChart" class="loginchart_div">
            
            </section>
        </aside>

        <aside class="text-center">
            <h5><?php echo lang('LoginAnalytics_UsernameVSEmail'); ?></h5>
            <section id="loginUsernameEmailChart" class="loginchart_div">
            
            </section>
        </aside>

        <aside class="text-center">
            <h5><?php echo lang('LoginAnalytics_FirstTimeLogin'); ?></h5>
            <section id="loginFirstTimeChart" class="loginchart_div">
            
            </section>
        </aside>
    </section>

    <section id="loginanalytic_row3" class="panel">
        <div class="panel-body">
            <div class="row">
                <aside class="text-center col-sm-6">
                    <h5><?php echo lang('LoginAnalytics_PopularDaysLogin'); ?> </h5>
                    <section id="loginPopDaysChart"></section>
                </aside>

                <aside class="text-center col-sm-6" id="loginPopTimeChart">
                    <h5><?php echo lang('LoginAnalytics_PopularTimeLogin'); ?></h5>
                    <Section class="figwrap">
                       <div class="figer" id="dvAMChart" style="display: none;">
                           <span class="text-left">AM</span>
                           <span class="clock-3">3</span>
                           <span class="clock-6">6</span>
                           <span class="clock-9">9</span>
                           <span class="clock-12">12</span>
                           <span class="topL" id="spnAM1">0</span>
                            <span class="topR" id="spnAM2">0</span>
                            <span class="botL" id="spnAM3">0</span>
                            <span class="botR" id="spnAM4">0</span>
                       </div>
                    </Section>
                    <Section class="figwrap marL15">
                       <div class="figer" id="dvPMChart" style="display: none;">
                           <span class="text-left">PM</span>
                           <span class="clock-3">3</span>
                           <span class="clock-6">6</span>
                           <span class="clock-9">9</span>
                           <span class="clock-12">12</span>
                           <span class="topL" id="spnPM1">0</span>
                            <span class="topR" id="spnPM2">0</span>
                            <span class="botL" id="spnPM3">0</span>
                            <span class="botR" id="spnPM4">0</span>
                       </div>
                    </Section>
                    <div id="populartimechart" class="hide"></div>
                    <div id="loaderdiv"></div>
                </aside>
               </div> 
             <div class="row m-t">
                <aside class="text-center col-sm-6">
                    <h5><?php echo lang('LoginAnalytics_AcceptanceFailure'); ?></h5>
                    <section id="loginFailureChart" class="loginchart_div">
                   
                    </section>
                </aside>
        </div>
        </div>
    </section>

    <section id="loginanalytic_row4" class="panel">
    <div class="panel-body">
        
        <div class="text-center">
            <h5><?php echo lang('LoginAnalytics_LocationWiseLoginDetails'); ?></h5>
            
            <section id="loginGeoChart" class="logingeochart"></section>
            
        </div>
        </div>
    </section>

</section>
</div>
</section>
<!--End - Main LoginCtrl angular -->