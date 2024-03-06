<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a><a target="_self" href="<?php echo base_url('admin/analytics/signup_analytics') ?>"><?php echo lang('Analytics'); ?></a></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('SignUpAnalytics_SignUpAnalytics'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--/Bread crumb-->
<section class="main-container">
<div class="container"> 

<!--Info row-->
<div class="info-row row-flued clearfix">
    <h2><?php echo lang('SignUpAnalytics_SignUpAnalytics1'); ?></h2>
</div>
<!--/Info row-->
<!--Main SignupCtrl angular -->
<section id="signupAnalyticsCtrl"  ng-controller="signupAnalyticsCtrl">

<section id="signupanalytic_row1" class="panel">
    <div class="panel-body">
        
    <section class="count-wrap">
        <label class="login-counts">
            <span id="signupcount_label"></span>
            <small><?php echo lang('SignUpAnalytics_SIGNUPS'); ?></small>
        </label>
        <div class="pull-right" style="width: 120px;">
            <select chosen data-disable-search="true" name="analytics_filter" id="analytics_filter" onchange="ChangeAnalyticData('signup_analytics');">
                <option value="1"><?php echo lang('Filter_Monthly'); ?></option>
                <option value="2"><?php echo lang('Filter_Weekly'); ?></option>
                <option selected="selected" value="3"><?php echo lang('Filter_Daily'); ?></option>
            </select>
            <input type="hidden" name="filter_val" id="filter_val" value="3"/>
        </div>
        <aside class="login-number">
            <span><?php echo lang('SignUpAnalytics_NoOfSignUps'); ?></span>
        </aside>
    </section>
    <section ng-init="signupAnalyticsChart()" id="signupLineChart" style="width: 1153px; min-height: 225px" class="text-center">
        
    </section>
    </div>
</section>

<section id="signupanalytic_row2" class="graph-pie-wrap four-child mTop35">
    <aside class="text-center">
        <h5><?php echo lang('SignUpAnalytics_SourcesOfSignUps'); ?></h5>
        <section ng-init="signupSourceSignupChart()" id="signupSourceSignupChart" class="loginchart_div">
        
        </section>
    </aside>

    <aside class="text-center">
        <h5><?php echo lang('SignUpAnalytics_Types'); ?></h5>
        <section id="signupTypeChart" class="loginchart_div">
        
        </section>
    </aside>

    <aside class="text-center">
        <h5><?php echo lang('LoginAnalytics_Devices'); ?></h5>
        <section id="signupDeviceChart" class="loginchart_div">
        
        </section>
    </aside>

    <aside class="text-center">
        <h5><?php echo lang('SignUpAnalytics_VisitsVsSignUps'); ?></h5>
        <section id="signupVisitSignupChart" class="loginchart_div">
        
        </section>
    </aside>
</section>

<section id="signupanalytic_row3" class="panel">
    <div class="panel-body">
        <div class="row">
            
            <aside class="text-center col-sm-6">
                <h5><?php echo lang('SignUpAnalytics_TimeTakenToSignUp'); ?></h5>
                <section id="signupTimeChart"></section>
            </aside>
            
            <aside class="text-center col-sm-6">
                <h5><?php echo lang('SignUpAnalytics_PopularDaysSignUp'); ?></h5>
                <section id="signupPopDaysChart"></section>
            </aside>
        </div>
        <div class="row m-t"> 
            <aside class="activity-wrap text-center col-sm-6" id="signupPopTimeChart">
                <h5><?php echo lang('SignUpAnalytics_PopularTimeSignUp'); ?></h5>
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
    </div>
</section>

<section id="signupanalytic_row4" class="panel">
    <div class="panel-body">
        
    <div class="text-center">
        <p class="text-right map-nav">
            <a class="active" href="javascript:void(0)" id="signup_geo"><?php echo lang('SignUpAnalytics_SignUp'); ?></a>|
            <a href="javascript:void(0)" id="visit_geo"><?php echo lang('SignUpAnalytics_Visits'); ?></a>
            <input type="hidden" name="RightFilter" id="RightFilter" value="1"/>
        </p>
        <h5><?php echo lang('SignUpAnalytics_LocationWiseSignUp'); ?></h5>
        <section id="signupGeoChart" class="logingeochart">
       
        </section>
    </div>
    </div>
</section>

</section>
</div>
</section>
<!--End - Main SignupCtrl angular -->