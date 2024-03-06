<section class="main-container container-fluid">
    <?php 
       if (isset($global_settings['date_format']))
       {
          $startDate1 = date($global_settings['date_format'], strtotime("01/01/2014"));
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
        <!--Info row-->
        <div class="info-row row-flued">
            <h2>Analytics Dashboard</h2>  
            <div class="col-sm-7 float-right">  <h2 class="float-right"> Alexa Rank : {{summaryData.alexarank}}</h2> 
                <!-- <div class="dropdown dropdown-time float-right" data-dropdown="hide">
                    <a href="javascript:void(0);" class="month-view dropdown-menu-right">
                       <span id="dateFilterText" ng-bind="statFilterName">All</span>
                       <input type="hidden" id="SpnFrom" value=""/>
                       <input type="hidden" id="SpnTo" value=""/>
                       <i class="ficon-arrow-down"></i>
                   </a>
                    <div class="action-dropdown monthView wid210" id="date_dropdown">
                        <ul class="viewList">
                             <li ng-repeat="dateRangeFilterOption in dateRangeFilterOptions" ng-click="onSelectDateRange(dateRangeFilterOption)">
                                <a ng-bind="dateRangeFilterOption.label"></a>
                            </li>                   
                        </ul>
                        <ul class="custom-select">
                            <li><a href="javascript:void(0);" class="customSelect">Custom</a></li>
                        </ul>
                        <ul class="customView">
                            <li>
                                <div class="form-group">
                                    <label class="label-control">From</label>
                                    <input type="text" class="form-control" id="dateFrom" value="<?php echo $startDate;?>">
                                </div>
                            </li>
                            <li>
                                <div class="form-group">
                                    <label class="label-control">To</label>
                                    <input type="text"  class="form-control"  id="dateTo" value="<?php echo $endDate;?>">
                                </div>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="btn btn-default" id="submit_analytic_date">Submit</a>
                            </li>
                        </ul>
                    </div>
                </div> -->
            </div>
        </div>
        <!--/Info row-->
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
              </div>
              <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i> 
                
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
              <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>

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
              <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>
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

                    <p>New Post</p>
                  </div>
                  <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                  </div>
                  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>


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
                  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>
                  
                  
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
                  <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i>



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
              <div class="card card-success">
                <div class="card-header">
                  <h3 class="card-title">Android App version</h3>
                </div>
                <div class="card-body">
                  <canvas id="pieChart-Appversion" style="height:230px">No Data Available</canvas>
                </div>
              <!-- /.card-body -->
              </div>
            </div>
            <div class="col-lg-4">
              <!-- DONUT CHART -->
              <div class="card card-danger">
                <div class="card-header">
                  <h3 class="card-title">IOS App version</h3>
                </div>
                <div class="card-body">
                  <canvas id="pieChart-IOSAppversion" style="height:230px">No Data Available</canvas>
                </div>
              <!-- /.card-body -->
              </div>
            </div>
            <div class="col-lg-4">
               <!-- BAR CHART -->
                <div class="card card-info">
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
                      <div class="card-body admin-card-body">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                          <li class="item" ng-repeat="( activityIndex, topInfluencersData ) in topInfluencersRowData" ng-if="topInfluencersData.FirstName">
                            <div class="product-img">
                              <img ng-if="topInfluencersData.ProfilePicture !== '' && topInfluencersData.ProfilePicture!=='user_default.jpg'"  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + topInfluencersData.ProfilePicture}}" alt="" class="img-size-50">
                              <span ng-if="topInfluencersData.ProfilePicture=='user_default.jpg' || topInfluencersData.ProfilePicture==''" class="default-thumb mob-people-default" style="width:5%;height:5%"><span ng-bind="getDefaultImgPlaceholder(topInfluencersData.FirstName+' '+topInfluencersData.LastName)"></span></span>
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
                      <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                          <li class="item" ng-repeat="( activityIndex, topContributorsData ) in topContributorsRowData" ng-if="topContributorsData.FirstName">
                            <div class="product-img">
                              <img ng-if="topContributorsData.ProfilePicture !== '' && topContributorsData.ProfilePicture!=='user_default.jpg'"  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + topContributorsData.ProfilePicture}}" alt="" class="img-size-50">
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
                <!-- DIRECT CHAT -->
                <?php /* ?>
                <div class="card direct-chat direct-chat-warning">
                  <div class="card-header">
                    <h3 class="card-title">Recent Activities</h3>

                    <!-- <div class="card-tools">
                      <span data-toggle="tooltip" title="3 New Messages" class="badge badge-warning">3</span>
                      <button type="button" class="btn btn-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                      </button>
                      <button type="button" class="btn btn-tool" data-toggle="tooltip" title="Contacts"
                              data-widget="chat-pane-toggle">
                        <i class="fa fa-comments"></i></button>
                      <button type="button" class="btn btn-tool" data-widget="remove"><i class="fa fa-times"></i>
                      </button>
                    </div> -->
                  </div>
                  <!-- /.card-header -->
                  <div class="card-body" ng-controller="DashboardFeedController" ng-init="getActivityList();">
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages">
                      <!-- Message. Default to the left -->
                      <div class="direct-chat-msg" ng-repeat="( activityIndex, activityData ) in activityDataList">
                        <div class="direct-chat-info clearfix">
                          <span class="direct-chat-name float-left">
                          {{activityData.subject_user.UserName}}</span>
                          <span class="direct-chat-timestamp float-right">{{activityData.activity.CreatedDate}}</span>
                        </div>
                        <!-- /.direct-chat-info -->
                       <!--  <img class="direct-chat-img" src="dist/img/user1-128x128.jpg" alt="message user image"> -->
                        <!-- /.direct-chat-img -->
                         <img ng-if="activityData.activity.EntityProfilePicture !== '' && activityData.activity.EntityProfilePicture!=='user_default.jpg'" title="" alt="" class="direct-chat-img" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.activity.EntityProfilePicture}}">
                        <div class="direct-chat-text">
                           <!-------------------feedlist ------------->
                           <div infinite-scroll="getActivityList()" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">
                                <div ng-if="((!activityDataListLoader && (requestObj.PageNo === 1)) || (activityDataList.length > 0))" id="adminActityFeed-{{activityData.activity_log_details.ID}}" class="panel panel-primary" ng-class="{ 'selected-feed' : ( currentActivityIndex === activityIndex ) }" ng-repeat="( activityIndex, activityData ) in activityDataList">
                                    <div class="panel-body">
                                        <ul class="list-group list-group-thumb sm">
                                            <li class="list-group-item">
                                                <div class="list-group-body">
                                                    <div class="btn-toolbar btn-toolbar-right">
                                                        
                                                        <a class="btn btn-xs btn-icn btn-default" 
                                                           ng-class="(activityData.activity.IsPromoted == '1') ? 'promoted' : 'promote'"
                                                           ng-click="setPromotionStatus(activityData.activity)"
                                                           uib-tooltip="{{(activityData.activity.IsPromoted == '1') ? 'Promoted' : 'Promote'}}"
                                                           tooltip-append-to-body="true"
                                                        >
                                                            <span class="icn">
                                                                <i class="ficon-promoted"></i>
                                                            </span>
                                                        </a>
                                                        
                                                        
                                                        <a class="btn btn-xs btn-icn btn-default featured-post " 
                                                           
                                                           uib-tooltip="Mark as feature"
                                                           tooltip-append-to-body="true"
                                                           ng-if="activityData.activity.IsFeatured == 0"
                                                           ng-click="change_activity_feature_status(activityData.activity);" 
                                                        >
                                                            <span class="icn">
                                                                <i class="ficon-featured-post"></i>
                                                            </span>
                                                        </a>
                                                        
                                                        
                                                        <a class="btn btn-xs btn-icn btn-default featured-post " 
                                                           
                                                            uib-tooltip="Featured"
                                                           tooltip-append-to-body="true"
                                                           ng-if="activityData.activity.IsFeatured == 1" 
                                                           ng-class="(activityData.activity.IsAdminFeatured) ? 'manual' : 'auto';"
                                                           ng-click="change_activity_feature_status(activityData.activity);" 
                                                        >
                                                            <span class="icn">
                                                                <i class="ficon-featured-post"></i>
                                                            </span>
                                                        </a>
                                                        
                                                        
                                                        <a class="btn btn-xs btn-default verify-btn" ng-click="verify_activity(activityData.activity.ActivityID,activityData.subject_user.UserID, activityData.activity)">
                                                            <span class="icn" ng-if="activityData.activity.Verified != 0" ng-cloak>
                                                                <i class="ficon-check"></i>
                                                            </span>
                                                            <span class="text">Verify</span>
                                                        </a>
                                                        <a uib-tooltip="Draft" ng-cloak ng-if="activityData.activity.StatusID=='10'" class="btn btn-xs btn-icn btn-default">
                                                            <span class="icn">
                                                                <i class="ficon-draft"></i>
                                                            </span>
                                                        </a>
                                                        <a ng-click="delete_activity(activityData.activity.ActivityID,activityData.subject_user.UserID)" class="btn btn-xs btn-icn btn-default">
                                                            <span class="icn">
                                                                <i class="ficon-bin"></i>
                                                            </span>
                                                        </a>
                                                        <a class="btn btn-xs btn-icn btn-default" ng-disabled="(currentActivityDataID == activityData.activity_log_details.ID)" ng-click="gotoActiveFeed(activityData.activity_log_details.ID, activityIndex);">
                                                            <span class="icn">
                                                             <i class="ficon-getdetails"></i>
                                                            </span> 
                                                        </a>
                                                    </div>
                                                    <figure class="list-figure">

                                                        <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '18' && activityData.activity.ActivityTypeID !== 23 && activityData.activity.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + activityData.subject_user.UserProfileURL}}">
                                                            <img ng-if="activityData.activity.EntityProfilePicture !== 'user_default.jpg'" err-name="{{activityData.activity.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.activity.EntityProfilePicture}}">
                                                        </a>
                                                        <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '3' && activityData.activity.ActivityTypeID !== '23' && activityData.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                                            <img ng-if="activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{activityData.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.subject_user.ProfilePicture}}">
                                                        </a>
                                                        <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="(activityData.activity.ActivityTypeID == '23' || activityData.activity.ActivityTypeID == '24') && activityData.activity.ModuleID !== '18'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                                            <img err-name="{{activityData.subject_user.UserName}}" ng-if="activityData.subject_user.ProfilePicture !== '' && activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.subject_user.ProfilePicture}}">
                                                        </a>

                                                        <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.activity.EntityGUID}}" ng-if="(activityData.activity.ActivityTypeID == 23 || activityData.activity.ActivityTypeID == 24) && activityData.activity.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + activityData.activity.EntityProfileURL}}">
                                                            <img ng-if="activityData.activity.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.activity.EntityProfilePicture}}">
                                                        </a>

                                                    </figure>
                                                    <div class="list-group-content" ng-init="activityTitleMessage='';">
                                                        <h6 class="list-group-item-heading" create-title-message parent-comment-id="activityData.parent_comment_details.PostCommentID" group-profile="activityData.group_profile" page-profile="activityData.page_profile" event-profile="activityData.event_profile" user-profile="activityData.user_profile" poll-data="activityData.PollData" activity-log-details="activityData.activity_log_details" subject-user="activityData.subject_user" activity-user="activityData.activity_user" parent-comment-user="activityData.parent_comment_user" activity="activityData.activity" parent-activity="activityData.parent_activity" parent-activity-user="activityData.parent_activity_user" activity-title-message="activityTitleMessage" activity-post-type="activityPostType">
                                                                </h6>
                                                        <ul class="list-activites">
                                                            <li ng-if="activityData.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(activityData.comment_details.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                                            <li ng-if="activityData.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(activityData.activity.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <ng-include ng-if="((activityData.activity_log_details.ActivityTypeID != 23) && (activityData.activity_log_details.ActivityTypeID != 24) && (activityData.activity_log_details.ActivityTypeID != 25) && (activityData.activity_log_details.ActivityTypeID != 16))" src="partialPageUrl + '/dashboard/activityFeed/activityContent.html'"></ng-include>
                                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 16)" src="partialPageUrl + '/dashboard/activityFeed/ratingReview.html'"></ng-include>
                                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 25)" src="partialPageUrl + '/dashboard/activityFeed/pollCreated.html'"></ng-include>
                                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 23 || activityData.activity_log_details.ActivityTypeID == 24)" src="partialPageUrl + '/dashboard/activityFeed/activityContentPersona.html'"></ng-include>
                                            <ng-include ng-if="( sharedActivityPostType[activityData.activity_log_details.ActivityTypeID] && ( activityData.activity_log_details.ActivityTypeID != 14 ) && ( activityData.activity_log_details.ActivityTypeID != 15 ) )" src="partialPageUrl + '/dashboard/activityFeed/blockquoteContent.html'"></ng-include>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div ng-if="activityDataListLoader" class="panel panel-primary">
                                    <div class="panel-body extra-block">
                                        <span class="loader text-lg" style="display:block;">&nbsp;</span>
                                    </div>
                                </div> 
                                 
                            </div>
                           <!-------------------feedlist ------------->
                        </div>
                        <!-- /.direct-chat-text -->
                      </div>
                      <!-- /.direct-chat-msg -->

                     
                    </div>
                    <!--/.direct-chat-messages-->

                    
                  </div>
                </div> <?php */ ?>
                <!--/.direct-chat -->
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
                      <canvas id="pieChart-Visitors" height="217">No Data Available</canvas>
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

