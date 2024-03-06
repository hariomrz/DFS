<div class="tab-pane fade default-scroll scrollbar" id="Communication">
    <!-- <div class="section-content clearfix">
        <h2>Emails/News Letters</h2>
        <button data-toggle="modal" data-target="#communicate_single_user" class="btn btn-default pull-right">Send Email</button>
    </div>
    <div class="section-content clearfix">
        <div class="table-info">
            <table class="table table-bordered email-status">
                <thead>
                    <tr>
                        <th>Emails Sent <span ng-if="persona_communications_total>0" ng-bind="'('+persona_communications_total+')'"></span></th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="communication in persona_communications">
                        <td><span ng-bind="communication.Subject"></span></td>
                        <td><span ng-bind="communication.CreatedDate"></span></td>
                        <td class="text-center"><i class="ficon-check mail-status" ng-class="(communication.State=='sent') ? 'read' : '' ;"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> -->
    <div>
        <div class="activity-listing clearfix" ng-repeat="( comIndex, comData ) in persona_communications">
            <ul class="list-group list-group-thumb sm">
                <li class="list-group-item">
                    <div class="list-group-body">
                        <figure class="list-figure">
                            <i ng-if="comData.Type == 1" class="ficon-bell"></i>
                            <i ng-if="comData.Type == 2" class="ficon-comment"></i>
                        </figure>
                        <i ng-hide="comData.Type == 2" class="ficon-arrow-down acc-arrow collapsed"  data-toggle="collapse" data-target="#acc{{$index}}"></i>
                        <div class="list-group-content">
                            <h6 class="list-group-item-heading" ng-if="comData.Type == 1" ng-bind="comData.Content.PushNotificationTitle"></h6>
                            <h6 class="list-group-item-heading" ng-if="comData.Type == 2" ng-bind="comData.Content.SmsText"></h6>
                            <!-- <ul class="list-activites">
                                <li ng-bind="createDateObject(comData.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
                            </ul> -->
                            <ul class="list-activites">
                                <li ng-bind="createDateObject(utc_to_time_zone(comData.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                            </ul>
                        </div>
                    </div>
                    <div class="collapse" id="acc{{$index}}">
                        <div class="list-group-bottom ng-scope">
                            <p ng-if="comData.Content.PushNotificationText != ''" class="list-group-item-text ng-binding ng-scope" ng-bind-html="textToLink(comData.Content.PushNotificationText)"></p>
                            <p ng-if="!comData.Content.PushNotificationText" class="list-group-item-text ng-binding ng-scope">N/A</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div ng-if="persona_communications_total>persona_communications.length && show_load_more==1" class="bottom-loader">
            <div class="panel-body">
                <button ng-click="getCommunications(userPersonaDetail.UserID)" class="btn btn-default btn-block">Load More</button>
            </div>
        </div>
    </div>
</div>
