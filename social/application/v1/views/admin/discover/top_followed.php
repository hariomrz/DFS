<div  ng-controller="TrendingTagCtrl" id="TrendingTagCtrl" ng-init="initTopFollowedTagFn()">




    <div class="container">
        <div class="main-container">             


            <div class="page-heading">
                <div class="row">
                    <div class="col-sm-4 " >                        
                    <div class="info-row"> <h2>Top Followed Tags</h2></div>
                    </div>
                    <div class="col-sm-8">
                        <div class="btn-toolbar btn-toolbar-right" ng-show="top_followed_tags.length != 0">
                            <div class="total-pages" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></div>
                            <nav class="page navigation">
                                <ul 
                                    uib-pagination total-items="totalRecord" items-per-page="numPerPage" 
                                    ng-model="currentPage" max-size="maxSize" 
                                    num-pages="numPages" class="pagination-sm" boundary-links="false" 
                                    ng-change="getThisPage()"
                                    >

                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>


            <div class="panel panel-secondary">
                <div class="panel-body">



                    <div class="table-listing">
                        <table class="table table-hover crm-table"> 
                            <thead ng-show="totalRecord">
                                <tr> 
                                    <th style="vertical-align: top;" ng-click="orderByField('Name')"  ng-class="getOrderByClass('Name')" >
                                        Name 
                                        <a class="sort" ng-if="getOrderByClass('Name')">
                                            <span class="icn">
                                                <i class="ficon-sort-arrow"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th style="vertical-align: top;" ng-click="orderByField('TotalFollowers')"  ng-class="getOrderByClass('TotalFollowers')" >
                                    Total Followers
                                    <a class="sort" ng-if="getOrderByClass('TotalFollowers')">
                                            <span class="icn">
                                                <i class="ficon-sort-arrow"></i>
                                            </span>
                                        </a>
                                    </th> 
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr ng-if="totalRecord == 0" >
                                    <td colspan="2" style="text-align: center;">
                                        No Result Found.
                                    </td>
                                </tr>
                                
                                <tr ng-repeat="(key, user) in top_followed_tags" repeat-done="popOverInit();">
                                    
                                    <td>
                                        <div class="list-group list-group-thumb xs"> 
                                            <div class="list-group-item">
                                                <div class="list-group-body">
                                                    <div class="list-group-content">
                                                        <div class="list-group-item-heading ellipsis">                                               
                                                            <label class="cursor-pointer" uib-tooltip="{{user.Name}}"  ng-bind="user.Name"></label>
                                                            
                                                        </div>
                                                    </div>   
                                                </div>                           
                                            </div>
                                        </div>
                                    </td>
                                  
                                    <td>{{user.TotalFollowers}}</td> 
                                    
                                </tr>
                            </tbody>
                        </table>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
