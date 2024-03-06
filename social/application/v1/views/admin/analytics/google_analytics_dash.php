<section class="main-container container-fluid">
    <?php 
       if (isset($global_settings['date_format']))
       {
          $startDate1 = date($global_settings['date_format'], strtotime("03/01/2019"));
          $endDate1 = date($global_settings['date_format']);
          
       }
       if($this->session->userdata('startDate') && $this->session->userdata('endDate'))
        {
            $startDate1 = date($global_settings['date_format'], strtotime($this->session->userdata('startDateCompare')));
            $endDate1 = date($global_settings['date_format'], strtotime($this->session->userdata('endDateCompare')));
        }
    ?>
    
    <input type="hidden" id="SpnFromCompare" value="<?php echo $startDate1;?>"/>
    <input type="hidden" id="SpnToCompare" value="<?php echo $endDate1;?>"/>

    <input type="hidden" name="filter_val" id="filter_val" value="visits"/>
    <div id="googleAnalyticsCtrl"  ng-controller="googleAnalyticsCtrl" class="graph-pie-wrap ng-scope loaderparentdiv col-sm-10 col-sm-offset-1">
 
      <div class="modal fade" tabindex="-1" role="dialog" id="graphdetails" >
          <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                  <div class="card-header">
                    <h3 class="card-title">{{graphtitle}}</h3>
                  </div>
                  <div class="modal-body graphdraw"> 

                        <div id="linechartloaderdiv" class="analyticloaderdiv"><p><img src="<?php echo base_url() ?>assets/admin/img/loader.gif"></p></div>
                        <!----graph data ------------>
                          <!-- solid sales graph -->
                          <div class="card bg-info-gradient graphdraw" ng-class="(graphName== 'users' || graphName== 'visitors' ? 'col-md-8' : 'col-md-12')">
                            <div class="card-body">
                              <div class="chart" id="line-chartA"></div>
                            </div>
                            <!-- /.card-body -->
                            
                            <!-- /.card-footer -->
                          </div>
                          <div class="graphdraw" ng-class="(graphName== 'users' || graphName== 'visitors' ? 'col-md-4' : 'col-md-4 hidden')">
                                  <div class="col-12">
                                   <canvas id="pieChart-Sources">No Data Available</canvas>
                                   </div>
                                  <div class="col-12">
                                   <ul class="chart-legend clearfix more-details-ul">
                                    <li ng-repeat="paicolor in paicolors"><i class="fa fa-circle-o"  style="color:  {{paicolor.colorcode}}"></i> {{paicolor.colorText}} </li>
                                  </ul>
                                  </div>
                                   
                          </div>
                          <!-- /.card -->
                        <!----graph data ------------>
                    </div>
              </div>
              <!-- /.modal-content -->
          </div>
      </div>
    <!-- /.modal-dialog -->
        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Analytics Dashboard</h2>  
            <div class="col-sm-7 float-right">  <h2 class="float-right"> Alexa Rank : {{summaryData.alexarank}}</h2> 
            </div>
        </div>

        <!---AdminLTE---->
        <div class="row" ng-init="googleAnalyticsRegisteredUsers();topInfluencers();getSummary();topContributors();googleAnalyticDataReport();">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{registeredNumberOfUsers}}</h3>  
                <div ng-if="registeredNumberOfUsersCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>{{registeredNumberOfUsersCompare}} %</div>
                <div ng-if="registeredNumberOfUsersCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{registeredNumberOfUsersCompare}} %</div>
                <p>New Registrations</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div> <!-- ng-init="$scope.getUserGraphData();$scope.getVisitorsGraphData();$scope.getActiveusersGraphData();
            $scope.getNewpostsGraphData();$scope.getNewcommentsGraphData();$scope.getNewlikesGraphData();" -->
              <a href="#" class="small-box-footer" ng-click="openGraph('users');" >More info <i class="fa fa-arrow-circle-right"></i> 
                
              </a> 
                     
              </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{TotalVisiters}}<!-- <sup style="font-size: 20px">%</sup> --></h3>
                 <div ng-if="TotalVisitersCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>{{TotalVisitersCompare}} %</div>
                <div ng-if="TotalVisitersCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{TotalVisitersCompare}} %</div>

                <p>Visitors</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer" ng-click="openGraph('visitors');">More info <i class="fa fa-arrow-circle-right"></i>

              </a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{TotalActiveUsers}}</h3>
                <div ng-if="TotalActiveUsersCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>{{TotalActiveUsersCompare}} %</div>
                <div ng-if="TotalActiveUsersCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{TotalActiveUsersCompare}} %</div>

                <p>Active users</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer" ng-click="openGraph('activeusers');">More info <i class="fa fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>0</h3>  
                <p>App Installs</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i> 
                
              </a> 
                     
              </div>
          </div>
          <!-- ./col -->
        </div>
        <!---second row --->
        <div class="row">
            <div class="col-lg-3 col-6" ng-init="googleAnalyticsGeoChart1()">
                <!-- small box -->
                <div class="small-box bg-warning">
                  <div class="inner">
                    <h3>&nbsp;{{TotalPosts}}</h3>
                     <div ng-if="TotalPostsCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>{{TotalPostsCompare}} %</div>
                    <div ng-if="TotalPostsCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{TotalPostsCompare}} %</div>

                    <p>New Posts</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                  </div>
                  <a href="#" class="small-box-footer" ng-click="openGraph('newposts');">More info <i class="fa fa-arrow-circle-right"></i>


                  </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                  <div class="inner">
                    <h3>&nbsp;{{TotalComments}}</h3>
                  <div ng-if="TotalCommentsCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>
                  {{TotalCommentsCompare}} %</div>
                  <div ng-if="TotalCommentsCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{TotalCommentsCompare}} %</div>

                    <p>New Comments</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                  </div>
                  <a href="#" class="small-box-footer" ng-click="openGraph('newcomments');">More info <i class="fa fa-arrow-circle-right"></i>
                  
                  
                  </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                  <div class="inner">
                    <h3>&nbsp;{{TotalLikes}}</h3>
                    <div ng-if="TotalLikesCompare>0" class="float-right no-of-users"><i class="fa fa-arrow-up"> </i>{{TotalLikesCompare}} %</div>
                    <div ng-if="TotalLikesCompare<0" class="float-right no-of-users"><i class="fa fa-arrow-down"> </i>{{TotalLikesCompare}} %</div>

                    <p>New Likes</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                  </div>
                  <a href="#" class="small-box-footer" ng-click="openGraph('newlikes');">More info <i class="fa fa-arrow-circle-right"></i>



                  </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
            <!-- small box -->
                <div class="small-box bg-success">
                  <div class="inner">
                    <h3>0</h3>
                    <p>App Uninstalls</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-person-add"></i>
                  </div>
                  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
              <!-- DONUT CHART -->
              <div class="card card-success google-dash-card-height">
                <div class="card-header">
                  <h3 class="card-title">Android App version</h3>
                </div>
                <div class="card-body">
                      <div class="col-10">
                      <canvas id="pieChart-Appversion" style="height:230px">No Data Available</canvas> 
                      </div>
                      <div class="col-2">
                       <ul class="chart-legend clearfix">
                        <li ng-repeat="paicolor in Androidpaicolors"><i class="fa fa-circle-o"  style="color:  {{paicolor.colorcode}}"></i> {{paicolor.colorText}} </li>
                      </ul>
                      </div>
                </div>
              <!-- /.card-body -->
              </div>
            </div>
            <div class="col-lg-4">
              <!-- DONUT CHART -->
              <div class="card card-danger google-dash-card-height">
                <div class="card-header">
                  <h3 class="card-title">IOS App version</h3>
                </div>
                <div class="card-body">
                  <div class="col-10">
                  <canvas id="pieChart-IOSAppversion" style="height:230px">No Data Available</canvas>
                  </div>
                  <div class="col-2">
                     <ul class="chart-legend clearfix">
                      <li ng-repeat="paicolor in IOSpaicolors"><i class="fa fa-circle-o"  style="color:  {{paicolor.colorcode}}"></i> {{paicolor.colorText}} </li>
                      
                    </ul>
                  </div>
                </div>
              <!-- /.card-body -->
              </div>
            </div>
            <div class="col-lg-4">
               <!-- BAR CHART -->
                <div class="card card-info google-dash-card-height">
                  <div class="card-header">
                    <h3 class="card-title">Time of login users</h3>
                  </div>
                  <div class="card-body">
                    <div class="chart">
                      <canvas id="barChart-Timeofuserslogin" style="height:230px"></canvas>
                    </div>
                  </div>
                  <!-- /.card-body -->
                </div>
            </div>
        </div>      
        <div class="row">
            <div class="col-lg-6">
                 <div class="card card-info">
                      <div class="card-header">
                        <h3 class="card-title">Top Influencers</h3>
                       
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body admin-card-body user-img-default">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                          <li class="item" ng-repeat="( activityIndex, topInfluencersData ) in topInfluencersRowData" ng-if="topInfluencersData.FirstName">
                            <div class="product-img">
                              <img ng-if="topInfluencersData.ProfilePicture !== '' && topInfluencersData.ProfilePicture!=='user_default.jpg'"  ng-src="{{ImageServerPath + 'upload/profile/' + topInfluencersData.ProfilePicture}}" alt="" class="img-size-50">
                              <span ng-if="topInfluencersData.ProfilePicture=='user_default.jpg' || topInfluencersData.ProfilePicture==''" class="default-thumb mob-people-default"><span ng-bind="getDefaultImgPlaceholder(topInfluencersData.FirstName+' '+topInfluencersData.LastName)"></span></span>
                            </div>
                            <div class="product-info">
                              <a href="javascript:void(0)" class="product-title">{{topInfluencersData.FirstName}} {{topInfluencersData.LastName}}
                                <span class="badge badge-warning float-right">{{topInfluencersData.NoOfLikes}} Likes</span>
                              </a>
                              <span class="product-description">
                                {{topInfluencersData.NoOfComments}} Comments
                              </span>
                            </div>
                          </li>
                          <!-- /.item -->
                          <!-- /.item -->
                        </ul>
                      </div>
                      
                    </div>
            </div>
             <div class="col-lg-6">
                 <div class="card card-success">
                      <div class="card-header">
                        <h3 class="card-title">Top Contributors</h3>
                     </div>
                      <!-- /.card-header -->
                      <div class="card-body p-0 user-img-default">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                          <li class="item" ng-repeat="( activityIndex, topContributorsData ) in topContributorsRowData" ng-if="topContributorsData.FirstName">
                            <div class="product-img">
                              <img ng-if="topContributorsData.ProfilePicture !== '' && topContributorsData.ProfilePicture!=='user_default.jpg'"  ng-src="{{ImageServerPath + 'upload/profile/' + topContributorsData.ProfilePicture}}" alt="" class="img-size-50">
                            </div>
                            
                            <div class="product-info">
                              <a href="javascript:void(0)" class="product-title">{{topContributorsData.FirstName}} {{topContributorsData.LastName}}
                                <span class="badge badge-warning float-right">{{topContributorsData.NoOfLikes}} Likes</span>
                              </a>
                              <span class="product-description">
                                {{topContributorsData.NoOfComments}} Comments
                              </span>
                            </div>
                          </li>
                          <!-- /.item -->
                          <!-- /.item -->
                        </ul>
                      </div>
                      
                    </div>
            </div>
            
            
        </div>        
        <!---AdminLTE---->

        <!--Main googleAnalyticsCtrl angular -->
          <div class="row">
              <div class="col-md-4">
              <!---------visitors----->
              <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Visitors</h3>
               
              </div>
              <!-- /.card-header -->
              <div class="card-body visitors-piechart">
                <div class="row">
                  <div class="col-md-12">
                    <div class="chart-responsive">
                      <div class="col-9">
                      <canvas id="pieChart-Visitors" height="217">No Data Available</canvas>
                      </div>
                      <div class="col-3">
                       <ul class="chart-legend clearfix">
                        <li ng-repeat="paicolor in Visitorspaicolors"><i class="fa fa-circle-o"  style="color:  {{paicolor.colorcode}}"></i> {{paicolor.colorText}} </li>
                      
                      </ul>
                    </div>
                    </div>
                    <!-- ./chart-responsive -->
                  </div>
                  <!-- /.col -->
                   <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- /.card-body -->
              <!-- /.footer -->
            </div>
              <!---------visitors----->
              </div>
              <!-- /.col -->

              <!----------map col-8--->             
               <!-- Left col -->
          <div class="col-md-8">
            <!-- MAP & BOX PANE -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Visitors Report</h3>

                <!-- <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-widget="remove">
                    <i class="fa fa-times"></i>
                  </button>
                </div> -->
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
               
                <div id="world-map-countrywise" style="height: 250px; width: 100%;"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
                        
          </div>
          <!-- /.col -->
              <!----------map col-8--->             
              <!-- /.col -->
            </div>
            <!-- /.row -->
    </div>
</section>

