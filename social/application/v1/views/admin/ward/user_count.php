
<div  ng-controller="WardListCtrl" id="WardListCtrl">
    <div class="container" ng-init="initFn()">
        <div class="main-container"> 
            <div class="page-heading">
                <div class="row">
                    Ward User Analytics
                </div>
            </div>


            <div class="panel panel-secondary">
                <div class="panel-body">
                    <div class="table-listing">
                        <table class="table table-hover crm-table"> 
                            <thead ng-show="wardList.length">
                                <tr> 
                                    <th style="vertical-align: top;"  >
                                        Ward Number                                        
                                    </th>
                                    <th colspan="5" style="vertical-align: top;">
                                        <table class="table"> 
                                                <tr> 
                                                    <td colspan="5" style="text-align: center;">
                                                        New Registration
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td ng-repeat="(key, days) in lastFiveDay" >{{days.name}}</td>
                                                </tr>                                            
                                        </table>
                                    </th>

                                    <th style="vertical-align: top;">Total Users</th> 
                                    
                                </tr>
                            </thead>
                            <tbody>                                
                                <tr ng-if="wardList.length == 0" >
                                    <td colspan="7" style="text-align: center;">
                                        No Result Found.
                                    </td>
                                </tr>
                                
                                <tr ng-repeat="(key, ward) in wardList" >
                                                                       
                                    <td>{{ward.Name}} - {{ward.Number}}</td>
                                  
                                    <td ng-repeat="(key, days) in lastFiveDay" ng-bind="ward.DateData[key]?ward.DateData[key]:0">
                                                    
                                    </td>   
                                    
                                    <td>{{ward.TotalUser}}</td> 
                                </tr>
                                 <tr ng-if="wardList.length > 0" >                                                                       
                                    <th>Total</th>                                  
                                    <th ng-repeat="(key, days) in lastFiveDay" ng-bind="days.total">
                                    </th>
                                    <td>{{TotalUser}}</td> 
                                </tr>
                            </tbody>
                        </table>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
