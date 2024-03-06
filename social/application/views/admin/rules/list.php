<!-- Main Content -->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span>Rules</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container" ng-controller="RulesCtrl" id="RulesCtrl">
    <div class="main-container">
        <div class="page-heading">
            <div class="row">
                <div class="col-sm-6">
                    <h2 class="page-title">Rules <i class="ficon-info-outline"></i></h2>
                </div>
                <div class="col-sm-6">
                    <div class="btn-toolbar btn-toolbar-right">
                        <!-- <button class="btn btn-default outline">Add Newsletter</button> -->
                        <button class="btn btn-default outline" id="setRule"><i class="ficon-cog"></i></button>
                        <button class="btn btn-primary" ng-click="clear_current_rule()" data-toggle="modal" data-target="#createRule">Create Rule</button>
                    </div>
                </div>
            </div>
        </div> 

        <div class="panel panel-secondary">
            <div class="panel-body" ng-init="get_rules();">
                <div class="table-listing">
                    <table class="table table-hover rulelist-table">
                        <thead>
                            <tr>
                                 <th width="15">&nbsp;</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Demographics</th>
                                <th>Interests</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody ui-sortable="sortableOptions" ng-model="rules_list">
                            <tr class="item-drag move" ng-repeat="rule in rules_list" repeat-done="cellWidth()">
                                <td><span class="move-icon"><i class="ficon-move"></i></span></td>
                                <td><span ng-bind="rule.Name"></span> <span class="block text-small-off" ng-bind="'Created on '+rule.CreatedDate"></span></td>
                                <td>
                                    <span ng-cloak ng-if="rule.Location.length==0">Any</span>
                                    <span ng-cloak ng-if="rule.Location.length>0" ng-bind="rule.Location[0].City+', '+rule.Location[0].Country"></span>
                                </td>
                                <td>
                                    <span ng-cloak ng-if="rule.AgeGroupName && rule.Gender" ng-bind="rule.AgeGroupName+' Y, '+rule.Gender"></span>
                                    <span ng-cloak ng-if="rule.AgeGroupName && !rule.Gender" ng-bind="rule.AgeGroupName+' Y'"></span>
                                    <span ng-cloak ng-if="!rule.AgeGroupName && rule.Gender" ng-bind="rule.Gender"></span>
                                    <span ng-cloak ng-if="!rule.AgeGroupName && !rule.Gender">Any</span>
                                </td>
                                <td>
                                    <span class="cell-max-length sm">
                                        <span ng-cloak ng-if="rule.InterestData.length==0">Any</span>
                                        <span ng-cloak ng-if="rule.InterestData.length>0" ng-repeat="interest in rule.InterestData">
                                            <span ng-bind="interest.Name"></span>
                                            <span ng-cloak ng-if="!$last">, </span>
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <div class="action">
                                        <a class="ficon-add-content" ng-click="clearPopup(); get_rule_details(rule.ActivityRuleID);set_current_rule(rule.ActivityRuleID)" data-toggle="modal" data-target="#addContent"></a>
                                        <a ng-cloak ng-if="rule.IsEditable==1" ng-click="set_current_rule(rule.ActivityRuleID)" data-toggle="modal" data-target="#createRule" class="ficon-edit"></a>
                                        <a ng-click="delete_rule(rule.ActivityRuleID)" ng-cloak ng-if="rule.IsEditable==1" class="ficon-bin"></a>
                                    </div> 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('admin/rules/popup') ?>
</div>
