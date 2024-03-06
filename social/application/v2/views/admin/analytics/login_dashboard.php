<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Analytics</a></li>
                    <li>/</li>
                    <li><span>Dashboard</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<section class="main-container">
    <!--Info row-->
<div id="loginDashboardCtrl" ng-controller="loginDashboardCtrl" ng-init="getLoginDashboardAnalytics();" class="container"> 
    <div class="info-row row-flued">
      <h2>Dashboard</h2> 
    </div>
    <!--/Info row-->
    <div class="dashboard-content"> 
      <div class="block-title"><i class="icons-cal">&nbsp;</i><span ng-bind="DateFilterRange"></span></div>
        <div class="block-content">
            <ul class="graph-listing">
                <li>
                    <h2>POSTS</h2>
                    <div class="graph-view" id="PostChart"></div>
                     <div class="label-block">
                        <label ng-bind="TotalPosts"></label> 
                        <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="PostCls"><span ng-bind="PostsPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                      </div>                   
                </li>
                <li>
                    <h2>NEW USER REGISTRATION</h2>
                    <div class="graph-view" id="UserChart"></div>
                     <div class="label-block">
                        <label ng-bind="TotalUsers"></label> 
                        <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="UserCls"><span ng-bind="UsersPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                      </div>                   
                </li>
                <li>
                    <h2>ENGAGEMENT SCORE</h2>
                    <div class="graph-view" id="EngageChart"></div>
                     <div class="label-block">
                        <label ng-bind="TotalEngage"></label> 
                        <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="EngageCls"><span ng-bind="EngagePercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                      </div>                   
                </li>
                
            </ul>
            <div class="events-detail">
                <ul class="graph-listing">
                    <li>
                        <h2>EVENTS</h2>
                        <div class="graph-view" id="EventChart"></div>
                         <div class="label-block">
                            <label ng-bind="TotalEvents"></label> 
                            <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="EventCls"><span ng-bind="EventsPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                          </div>                   
                    </li>
                    <li>
                        <h2>PAGES</h2>
                        <div class="graph-view" id="PageChart"></div>
                         <div class="label-block">
                            <label ng-bind="TotalPages"></label> 
                            <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="PageCls"><span ng-bind="PagesPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                          </div>                   
                    </li>
                    <li>
                        <h2>GROUPS</h2>
                        <div class="graph-view" id="GroupChart"></div>
                         <div class="label-block">
                            <label ng-bind="TotalGroups"></label> 
                            <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="GroupCls"><span ng-bind="GroupsPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                          </div>                   
                    </li>
                    <li>
                        <h2>MEDIA UPLOAD</h2>
                        <div class="graph-view" id="MediaChart"></div>
                         <div class="label-block">
                            <label ng-bind="TotalMedia"></label> 
                            <span ng-if="dateFilterText!=='All'" class="per-view" ng-class="MediaCls"><span ng-bind="MediaPercent"></span> <i class="icons-arrowp">&nbsp;</i></span>
                          </div>                   
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="dashboard-content"> 
      <div class="block-title"><i class="icons-usereng">&nbsp;</i>USER ENGAGEMENT</div>
        <div class="block-content">
            <div class="user-engagment-graph" id="UserEngageChart">
               <!-- <img src="<?php echo site_url() ?>assets/admin/img/graph14.jpg"> -->  
            </div>
            <div class="user-engament-detail">
                <div class="total-view">
                  <span ng-bind="TotalUsersCountEng"></span>
                  <label> TOTAL USERS</label>
                </div>

                <ul class="user-type" ng-cloak>
                    <li><span>Inactive Users</span>{{InActiveUsersEng}}</li>
                    <li class="unique"><span>Logged In Users</span>{{ActiveUsersEng}}</li>
                    <li class="engaged"><span>Engaged Users</span>{{EngageUsers}}</li>
                </ul>

            </div>
        </div>
    </div>

     <div class="dashboard-content" ng-init="getUsageData();"> 
        <div class="usage-block">
           <div class="usage">
             <div class="block-title"><i class="icons-usage">&nbsp;</i>USAGE</div>
             <div class="view-bylisting">
                <h2 ng-if="usageData.Desktop.length>0">DESKTOP</h2>
                <ul ng-if="usageData.Desktop.length>0" class="list-bydevice">
                    <li ng-repeat="desktop in usageData.Desktop">
                      <i ng-class="desktop.Icon"></i> <span ng-bind="desktop.Percent+'%'"></span>
                    </li>
                </ul>

                <h2 ng-if="usageData.Tablet.length>0">TABLET</h2>
                <ul ng-if="usageData.Tablet.length>0" class="list-bydevice">
                    <li ng-repeat="tablet in usageData.Tablet">
                      <i ng-class="tablet.Icon"></i> <span ng-bind="tablet.Percent+'%'"></span>
                    </li>
                </ul>
                <h2 ng-if="usageData.Mobile.length>0">MOBILE</h2>
                <ul ng-if="usageData.Mobile.length>0" class="list-bydevice">
                    <li ng-repeat="mobile in usageData.Mobile">
                      <i ng-class="mobile.Icon"></i> <span ng-bind="mobile.Percent+'%'"></span>
                    </li>
                </ul>
             </div> 

           </div>
           <div class="usage-chart">
             <div class="block-title"><i class="icons-usagechart">&nbsp;</i>CHART</div>
             <div class="pie-chart-block" id="UsageChart"></div>
           </div> 
        </div>        
    </div>

    <div class="dashboard-content"> 
      <div class="block-title"><i class="icons-demographics">&nbsp;</i>DEMOGRAPHICS</div>
        <div class="block-content">
            <div class="map-block" id="CountryChart">
               <img ng-src="{{CountryChart}}">  
            </div>

            <div class="top-countries">
              <h3>TOP FIVE COUNTRIES</h3>
            </div>

            <div class="top-countries-detail"> 
                <ul class="top-countries-view">
                    <li ng-repeat="country in CountryData">
                      <div class="countries-list left">
                        <span ng-class="($index==0) ? 'text-blue' : '' ;" ng-bind="country.CountryName"></span>
                        <span ng-if="$index=='0'">TOP COUNTRY</span>
                        <span ng-if="$index!='0'" ng-bind="'#'+CountryRank($index)"></span>
                      </div>
                      <div class="percentage-view" ng-style="{'background-color':'#'+CountryColors[$index]}" ng-bind="country.Percent+'%'"></div>
                    </li>
                </ul>

            </div>
        </div>
    </div> 


    <div class="clear">&nbsp;</div>
  </div>
  </section>  
 