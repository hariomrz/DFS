<!--Bread crumb-->
<div ng-if="show_daily_digest">
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
        <div class="container" ng-controller="DashboardFeedController" id="DashboardFeedController">
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
                                <button class="btn btn-default" ng-click="AddDetailsPopUp()" ng-show="userList.length != 0"><i class="ficon-plus"></i> <?php echo lang('Add'); ?></button>                    
                            </div>
                        </div>
                    </div>
                </div>  
            </div>
            <!--/Info row-->

            <div class="row-flued" ng-cloak>
                <div class="panel panel-secondary">
                    <div class="panel-body">
                        <!-- Pagination -->
                            <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                            <ul uib-pagination total-items="totalRecord" 
                            items-per-page="numPerPage" 
                            ng-model="currentPage" 
                            ng-change="getThisPage()"
                            max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                        <!-- Pagination -->
                        <table class="table table-hover ips_table">
                            <tr>
                                <th id="date" class="ui-sort selected">                           
                                    <div class="shortdiv sortedDown">Date</div>
                                </th>
                                <th><?php echo lang('Actions'); ?></th>
                            </tr>

                            <tr ng-repeat="dataList in listData[0].ObjIP|orderBy:orderByField:reverseSort">
                                <td>
                                    <p data-ng-bind="dataList.name"></p>
                                </td>
                                <td>
                                    <p data-ng-bind="dataList.parent_name"></p>
                                </td>
                                <td>
                                    <p data-ng-bind="dataList.ModuleName"></p>
                                </td>
                                <td>
                                    <a href="javascript:void(0);" ng-click="SetDetail(dataList);" class="smtp_action" onClick="smtpActionDropdown()">
                                        <i class="icon-setting">&nbsp;</i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <div id="ipdenieddiv"></div>
                        <!-- Pagination -->
                            <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                            <ul uib-pagination 
                            total-items="totalRecord" 
                            items-per-page="numPerPage" 
                            ng-model="currentPage" 
                            ng-change="getThisPage()"
                            max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
                        <!-- Pagination -->
                    </div>
                </div>
            </div>

            <style>
                .cus-class .from-subject{
                        width: 50%;
                        padding: 7px 0 0 19px;
                        float: left;
                }

            </style>    
        </div>
    </section>
</div>

