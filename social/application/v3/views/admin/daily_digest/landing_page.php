<div id="UserListCtrl" ng-controller="UserListCtrl">
    <div ng-controller="DashboardFeedController"> 
        <div ng-if="DD.show_daily_digest == 0">
            <?php $this->load->view('admin/daily_digest/filters'); ?>
            <section class="main-container">
                <div class="container">
                    <div class="page-heading">
                        <div class="row">
                            <div class="col-xs-7">
                                <h4 class="page-title" ng-if="activityTotalRecord">Posts ({{activityTotalRecord}})</h4>
                            </div>
                            <div class="col-xs-2">
                                <p style="mrgin-top:5px;">Daily Digest for <span ng-bind="createDateObject(DD.DailyDigestDate) | date : 'dd MMM, y'"></span></p>
                            </div>
                            <div class="col-xs-3" >
                                <button ng-click="saveDailyDigest(11);" ng-disabled="!showPublishButton" class="btn btn-default btn-sm" type="button">SAVE AS DRAFT</button>
                                <button ng-click="saveDailyDigest(2);" ng-disabled="!showPublishButton" class="btn btn-default btn-sm" type="button">PUBLISH</button>
                                <button ng-click="cancelDailyDigest()" class="btn btn-default btn-sm" type="button">CANCEL</button>    
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?php $this->load->view('admin/daily_digest/feedList'); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <business-card data="businesscard"></business-card>

        <!--Bread crumb-->
        <div ng-if="DD.show_daily_digest == 1">
            <div class="bread-crumbs">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12">
                            <ul class="bread-crumb-nav">
                                <li><span>Daily Digest</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--Bread crumb-->
            <section class="main-container">
                <div class="container" ng-init="initDailyDigestFn()"> 
                    <!--Info row-->
                    <div class="info-row row-flued">       
                        <h2 class="ng-binding"><span id="spnh2" class="ng-binding"></span></h2> 
                        <div class="info-row-right rightdivbox">
                            <div class="row">
                                <div class="col-sm-8">    
                                    &nbsp;                    
                                </div>
                                <div class="col-sm-4">                        
                                    <div class="btn-toolbar btn-toolbar-right" >
                                        <button ng-disabled= (!snb) class="btn btn-default" ng-click="show_notification_popup()">Send Push Notification</button>
                                        <button class="btn btn-default" ng-click="show_daily_digest_date_popup()"><i class="ficon-plus"></i> <?php echo lang('Add'); ?></button>                    
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </div>
                    <!--/Info row-->

                    <div class="row-flued matop10" ng-cloak>
                        <div class="panel panel-secondary">
                            <div class="panel-body">
                                <!-- Pagination --> 
                                    <div class="showingdiv"><label class="ng-binding" paging-info total-record="pagination.totalRecord" num-per-page="numPerPage" current-page="pagination.currentPage"></label></div>
                                    <ul uib-pagination total-items="pagination.totalRecord" 
                                    items-per-page="numPerPage" 
                                    ng-model="pagination.currentPage" 
                                    ng-change="getThisPage()"
                                    max-size="pagination.maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                                <!-- Pagination -->
                                <table class="table table-hover ips_table">
                                    <tr>
                                        <th id="date" >                           
                                            <div class="shortdiv sortedDown">Date</div>
                                        </th>
                                        <th id="status" >                           
                                            <div class="shortdiv sortedDown">Status</div>
                                        </th>
                                        <th><?php echo lang('Actions'); ?></th>
                                    </tr>
                                    <tr ng-repeat="dataList in dailyDigestList">
                                        <td>
                                            <p ng-bind="createDateObject(dataList.DailyDigestDate) | date : 'dd MMM, y'" ></p>
                                        </td>
                                        <td>
                                        <p ng-bind="dataList.Status==11 ? 'DRAFT' : 'PUBLISHED'" ></p>
                                        </td>
                                        <td>
                                            <div class="action">
                                                <a class="ficon-edit mrgn-l-20" ng-cloak ng-click="edit_daily_digest(dataList.DailyDigestDate)" uib-tooltip="Edit" tooltip-append-to-body="true"></a>
                                                <span>&nbsp;</span>
                                                <a class="ficon-bin" ng-cloak ng-click="delete_daily_digest(dataList.DailyDigestDate)" uib-tooltip="Delete" tooltip-append-to-body="true"  ></a>                                    
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <!-- Pagination --> 
                                <div class="showingdiv"><label class="ng-binding" paging-info total-record="pagination.totalRecord" num-per-page="numPerPage" current-page="pagination.currentPage"></label></div>
                                    <ul uib-pagination total-items="pagination.totalRecord" 
                                    items-per-page="numPerPage" 
                                    ng-model="pagination.currentPage" 
                                    ng-change="getThisPage()"
                                    max-size="pagination.maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                                <!-- Pagination -->
                                <div id="ipdenieddiv"></div>
                            </div>
                        </div>
                    </div> 

                    <div class="popup  communicate  animated" id="daily_digest_popup">
                        <div class="popup-title">
                            <span>Daily Digest&nbsp;</span>
                            <i class="icon-close" onClick="closePopDiv('daily_digest_popup', 'bounceOutUp');">&nbsp;</i>
                        </div>
                        <div class="popup-content">
                            <div class="modal-body has-padding">
                                <div class="form-group">
                                    <label class="control-label">Select Date</label>
                                    <div data-error="hasError" class="date-field">
                                        <input type="text"
                                               ng-model="DD.DailyDigestDate"
                                               placeholder="__ /__ /__"
                                               readonly
                                               ng-change="checkValDatepicker()"
                                               id="adminDashboardFilterDatepicker"
                                               init-filter-datepicker
                                                pickerType="from"
                                                fromid="adminDashboardFilterDatepicker"
                                                toid="adminDashboardFilterDatepicker2"
                                               class="form-control" />
                                        <label id="errorFromDate" class="error-block-overlay"></label>
                                        <label class="iconDate" for="adminDashboardFilterDatepicker">
                                            <i class="ficon-calendar"></i>
                                        </label>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="label">Select Date</label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control datepicker" ng-model="DailyDigestDate" id="to" placeholder="dd-mm-yyyy" on-focus>
                                        <label class="input-group-addon" for="to">
                                            <i class="ficon-calender"></i>
                                        </label>
                                    </div>
                                </div> -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-warning" data-dismiss="modal" onClick="closePopDiv('daily_digest_popup', 'bounceOutUp');"  data-ng-bind="lang.close"></button>
                                <button ng-disabled= (!DD.DailyDigestDate) type="button" ng-click="continue_daily_digest()" class="btn btn-primary"><i class=""></i>Continue</button>
                            </div>                        
                        </div>
                    </div>

                    <!--Popup for pushnotification a user  -->
                    <div class="popup confirme-popup animated" id="pushnotification_popup">
                        <div class="popup-title">Send Push Notification&nbsp;</i></div>
                        <div class="popup-content">
                            <form role="form" ng-submit="send_notification();">
                                <div class="modal-body has-padding">
                                    <div class="form-group mojis-start-textarea"> 
                                        <label data-ng-bind="lang.message"></label><span class="color-red">*</span>
                                        
                                        <textarea autofocus class="form-control" id="message" name="message" ng-model="DD.notification_text" maxlength="200" >  
                                        </textarea>
                                        <label for="message" class="error hide" id="message_error"></label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-warning" data-dismiss="modal" onclick="closePopDiv('pushnotification_popup', 'bounceOutUp');" data-ng-bind="lang.close"></button>
                                    <button ng-disabled= (!DD.notification_text) type="submit" class="btn btn-primary"><i class=""></i>Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Popup end pushnotification a user  -->

                </div>
            </section>
        </div> 
    </div>
</div>

<input type="hidden" id="LoggedInUserGUID" value="<?php echo $this->session->userdata('AdminLoginSessionKey') ?>" />