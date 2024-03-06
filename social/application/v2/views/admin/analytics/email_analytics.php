<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/analytics/email_analytics') ?>"><?php echo lang('Analytics'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('EmailAnalytics'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">
<div ng-controller="emailAnalyticsCtrl" id="emailAnalyticsCtrl"  class="container">
    <!--Info row-->
    <div class="info-row row-flued clearfix">
        <h2><?php echo lang('EmailAnalytics'); ?></h2>
        <ul class="sub-nav matop10 media_right_filter">
            <li><a id="mandrill" href="javascript:void(0);" ng-click="loadEmailAnalyticsData('mandrill');" class="selected"><?php echo lang('EmailAnalytics_MandrillAnalytics'); ?></a></li>
            <li><a id="smtp" href="javascript:void(0);" ng-click="loadEmailAnalyticsData('smtp');"><?php echo lang('EmailAnalytics_SmtpAnalytics'); ?></a></li>
        </ul>
    </div>
    <!--/Info row-->

    <section class="email-pie-wrap">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <aside class="text-center col-sm-3">
                        <div id="emailAnalyticsPieChart" ng-init="emailAnalyticsChart()"></div>        
                    </aside> 
                    <aside class="registration-graph col-sm-9">
                        <div class="info-row row-flued">
                            <h2>{{LineChartHeading}}</h2>
                            <div class="float-right">
                                <div class="tab-analytics martop0">
                                    <a id="flter_1" href="javascript:void(0);" ng-click="ChangeLineChart(1);"><?php echo lang('Filter_Monthly'); ?></a>
                                    <a id="flter_2" href="javascript:void(0);" ng-click="ChangeLineChart(2);"><?php echo lang('Filter_Weekly'); ?></a>
                                    <a id="flter_3" class="active" href="javascript:void(0);" ng-click="ChangeLineChart(3);"><?php echo lang('Filter_Daily'); ?></a>
                                    <a id="flter_4" href="javascript:void(0);" ng-click="ChangeLineChart(4);"><?php echo lang('Filter_Hourly'); ?></a>
                                </div>
                            </div>
                        </div>
                        <section ng-init="emailAnalyticsLineChart()" id="emailAnalyticsLineChart" style="width: 100%; min-height: 225px" class="text-center"></section>
                    </aside>
                </div>
            </div>
        </div>

        <div class="clear"></div>
        <div class="info-row row-flued emailsubhead clearfix"><h2>{{StatistcsHeading}} STATISTCS</h2></div>
        <div class="row-flued m-t-sm" id="EmailsStatistcs">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <!-- Pagination -->
                    <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                    <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                <!-- Pagination -->
                    <table class="table table-hover  email_statistcs_table">
                        <tr>
                            <th id="CreatedDate" class="ui-sort selected" ng-click="orderByField = 'CreatedDate'; reverseSort = !reverseSort; sortBY('CreatedDate')">                           
                                <div class="shortdiv sortedUp">Date<span class="icon-arrowshort">&nbsp;</span></div>
                            </th>
                            <th id="MessageSent" class="ui-sort" ng-click="orderByField = 'MessageSent'; reverseSort = !reverseSort; sortBY('MessageSent')">                           
                                <div class="shortdiv">Sent<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th>                           
                                <div class="shortdiv">Hard Bounce<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th>                           
                                <div class="shortdiv">Soft Bounce<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th>                           
                                <div class="shortdiv">Open<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th>
                                <div class="shortdiv">Click<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                        </tr>

                        <tr ng-repeat="datalist in listData[0].ObjEmail">
                            <td>
                                <p>{{datalist.CreatedDate}}</p>
                            </td>
                            <td>
                                <a ng-click="LoadSentMessage(datalist.CreatedDate);" href="javascript:;">{{datalist.MessageSent}}</a>
                            </td>
                            <td>
                                <a href="javascript:;">{{datalist.HardBounce}}</a>
                            </td>
                            <td>
                                <a href="javascript:;">{{datalist.SoftBounce}}</a>
                            </td>
                            <td>
                                <a href="javascript:;">{{datalist.Open}}</a>
                            </td>
                            <td>
                                <a ng-click="LoadEmailClickList(datalist.CreatedDate,datalist.Click);" href="javascript:;">{{datalist.Click}}</a>
                            </td>
                        </tr>
                    </table>
                    <div id="emaildenieddiv"></div>
                    <!-- Pagination -->
                    <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                    <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                <!-- Pagination -->
                </div>
            </div>
        </div>


        <div class="clear"></div>
        <div class="email_sent_table hide" id="SentEmailsStatistcs">        
            <div class="info-row row-flued emailsubhead"><h2>{{StatistcsHeading}} - SENT</h2></div>
            <div class="row-flued">
                <div>
                    <div data-pagination="" data-total-items="totalRecordSent" data-num-per-page="numPerPage" data-num-pages="numPagesSent()" data-current-page="currentPageSent" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination email_top_pagination"></div>
                    <table class="users-table email_sent_statistcs_table">
                        <tr>
                            <th id="SenderEmail" class="ui-sort" ng-click="orderByFieldSent = 'SenderEmail'; reverseSortSent = !reverseSortSent; SentSortBY('SenderEmail')">                           
                                <div class="shortdiv">Sender Email<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="ReceiverEmail" class="ui-sort" ng-click="orderByFieldSent = 'ReceiverEmail'; reverseSortSent = !reverseSortSent; SentSortBY('ReceiverEmail')">                           
                                <div class="shortdiv">Receiver email<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="Subject" class="ui-sort" ng-click="orderByFieldSent = 'Subject'; reverseSortSent = !reverseSortSent; SentSortBY('Subject')">                           
                                <div class="shortdiv">Subject<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th id="CreatedDate" class="ui-sort selected" ng-click="orderByFieldSent = 'CreatedDate'; reverseSortSent = !reverseSortSent; SentSortBY('CreatedDate')">                           
                                <div class="shortdiv sortedUp">Action Date<span class="icon-arrowshort">&nbsp;</span></div>
                            </th>
                            <th>
                                <div class="shortdiv">Click<span class="icon-arrowshort hide">&nbsp;</span></div>
                            </th>
                            <th ng-show="AnalyticType=='smtp'">Actions</th>
                        </tr>

                        <tr ng-repeat="datalist in listDataSent[0].ObjSentEmail">
                            <td>
                                <p>{{datalist.SenderEmail}}</p>
                            </td>
                            <td>
                                <p>{{datalist.ReceiverEmail}}</p>
                            </td>
                            <td>
                                <p>{{datalist.Subject}}</p>
                            </td>
                            <td>
                                <p>{{datalist.CreatedDate}}</p>
                            </td>
                            <td>
                                <a href="javascript:;">{{datalist.Click}}</a>
                            </td>
                            <td ng-show="AnalyticType=='smtp'">
                                <a href="javascript:void(0);" ng-click="SetEmail(datalist);" class="email-action" onClick="emailActiondropdown()">
                                    <i class="icon-setting">&nbsp;</i>
                                </a>
                            </td>
                        </tr>
                    </table>
                    <div id="sentemaildenieddiv"></div>
                    <div data-pagination="" data-total-items="totalRecordSent" data-num-per-page="numPerPage" data-num-pages="numPagesSent()" data-current-page="currentPageSent" data-max-size="maxSize" data-boundary-links="true" class="simple-pagination"></div>
                    
                    <!--Actions Dropdown menu-->
                    <ul class="action-dropdown emailActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                        <?php if(in_array(getRightsId('email_analytics_emails_view_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li id="ActionView"><a ng-click="summaryPopup()" href="javascript:void(0);">View</a></li>
                        <?php } ?>
                        <?php if(in_array(getRightsId('email_analytics_emails_resend_event'), getUserRightsData($this->DeviceType))){ ?>
                            <li id="ActionResend" ng-hide="currentUserStatusId==3"><a ng-click="ResendEmail()" href="javascript:void(0);">Resend</a></li>
                        <?php } ?>
                    </ul>
                    <!--/Actions Dropdown menu-->
                </div>
            </div>
        </div>

        <!--Popup for Show Clicks email urls -->
        <div class="popup mendrillpopup animated" id="EmailClickListPopup">
            <div class="popup-title">Clicks Detail</div>
            <div class="popup-content">
                <div class="communicate-footer row-flued" id="EmailClickUrlList">
                    <table class="users-table mendrill-table">
                        <tbody>
                            <tr>
                                <th>URLs</th>
                                <th>Counts</th>
                            </tr>

                            <tr ng-repeat="datalist in ClickListData[0].ObjClickEmail">
                                <td>
                                    <p>{{datalist.Url}}</p>
                                </td>
                                <td>
                                    <p>{{datalist.ClickCount}}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="form-control padtb20">
                        <input type="button" value="<?php echo lang('Close'); ?>" class="button" onclick="closePopDiv('EmailClickListPopup', 'bounceOutUp');">
                    </div>
                </div>
            </div>
        </div>
        <!--Popup for Show Clicks email urls -->
        
        <!--Popup for Show message which send to a user -->
        <div class="popup animated wid600" id="summaryPopup">
            <div class="popup-content">
                <div class="scroller">
                    <table class="popup-table">
                        <tbody>
                            <tr>
                                <td class="text-bold">Subject</td>
                                <td>:</td>
                                <td class="title-view" id="summarySubject"></td>
                            </tr>
                            <tr>  
                                <td class="text-bold">Date</td>
                                <td>:</td>               	
                                <td id="summaryCreatedDate"></td>
                            </tr>
                            <tr>
                                <td colspan="3" id="summaryBody"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="communicate-footer seprator">
                    <button onclick="closePopDiv('summaryPopup', 'bounceOutUp');" class="button"><?php echo lang('Close'); ?></button>
                </div>
            </div>
        </div>
        <!--Popup end for Show message which send to a user -->

    </section>
</div>
</section>