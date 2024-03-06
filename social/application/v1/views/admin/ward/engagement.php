<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Ward</span></li>
                    <li>/</li>
                    <li><span>Engagement</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
    <div class="container" ng-controller="WardListCtrl" id="WardListCtrl">
        <!--Info row-->
        <div class="row-flued">                     
           
                    <div class="row">
                        <div class="col-sm-7">&nbsp;
                        </div>
                        <div class="col-sm-5">
                            <div class="row">
                                <div class="col-sm-4">
                                </div>
                                <div class="col-sm-2">
                                    <label class="label label p-t-sm">Sort By:</label>
                                </div>
                                <div class="col-sm-6">
                                    <select  data-chosen="" ng-change="filter_engagement();" ng-options="POptions.MKey as POptions.Name for POptions in EngFilterOptions" data-ng-model="filter_eng" data-disable-search="true">
                                        <option value=""></option>
                                    </select> 
                                </select>
                                </div> 
                            </div>
                        </div>
                    </div>
                    
            
        </div>
    <!--/Info row-->
        <div class="row-flued" ng-init="initEngFn()">

                <div class="panel panel-secondary">
                    <div class="panel-body">
                        <div class="table-listing">
                            <table class="table table-hover crm-table"> 
                                <thead ng-show="wardList.length">
                                    <tr> 
                                        <th style="vertical-align: top;width: 18%;" rowspan="2"  >
                                            Ward Number                                        
                                        </th>
                                        <th style="text-align: center;" colspan="3" ng-repeat="(key, days) in lastFiveDay" >
                                            {{days.name}}
                                        </th>                                        
                                    </tr>
                                    <tr>
                                        <td>Post</td>
                                        <td>Comment</td>
                                        <td>Like</td>
                                        <td>Post</td>
                                        <td>Comment</td>
                                        <td>Like</td>
                                        <td>Post</td>
                                        <td>Comment</td>
                                        <td>Like</td>
                                        <td>Post</td>
                                        <td>Comment</td>
                                        <td>Like</td>
                                        <td>Post</td>
                                        <td>Comment</td>
                                        <td>Like</td>
                                    </tr>
                                </thead>
                                <tbody>                                
                                    <tr ng-if="wardList.length == 0" >
                                        <td colspan="16" style="text-align: center;">
                                            No Result Found.
                                        </td>
                                    </tr>
                                    
                                    <tr ng-repeat="(key, ward) in wardList | orderBy : sort_field" >
                                                                        
                                        <td><span>{{ward.Name}}</span><span ng-bind="ward.Number?' - '+ward.Number:''"></span></td>
                                    
                                        <td colspan="3" ng-repeat="(key, days) in lastFiveDay" > 
                                            <table class="table" style="margin: 0;">                                                
                                                    <tr>
                                                        <td class="bdr-none" ng-bind="ward.DateData[key].total_post?ward.DateData[key].total_post:0"></td>
                                                        <td class="bdr-none" ng-bind="ward.DateData[key].total_comment?ward.DateData[key].total_comment:0"></td>
                                                        <td class="bdr-none" style="text-align: right;" ng-bind="ward.DateData[key].total_like?ward.DateData[key].total_like:0"></td>
                                                    </tr>                                            
                                            </table>  
                                        </td>   
                                    </tr>
                                    <tr ng-if="wardList.length > 0" >                                                                       
                                        <th>Total</th>                                  
                                        <th colspan="3" ng-repeat="(key, days) in lastFiveDay" >
                                            <table class="table" style="margin: 0;">                                                
                                                    <tr>
                                                        <td class="bdr-none" >{{days.total_post}}</td>
                                                        <td class="bdr-none" >{{days.total_comment}}</td>
                                                        <td class="bdr-none" style="text-align: right;">{{days.total_like}}</td>
                                                    </tr>                                            
                                            </table>  
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                </div>
           
        </div>
    </div>
</section>
