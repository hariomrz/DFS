<div class="tab-pane fade" id="Communication">
    <div class="section-content clearfix">
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
    </div>
</div>
