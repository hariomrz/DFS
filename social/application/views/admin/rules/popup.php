<!--Rule Settings Starts -->
<div class="rule-setting" id="ruleSetting" ng-init="get_rules_config()">
    <div class="rule-popup-header">Rules Applicable Till <i class="ficon-cross" id="closeRuleSetting"></i> </div>
    <div class="rule-content">
        <div class="row">
            <div class="col-sm-12">
                <p>You are just away to create your rule. Add content by following below steps. </p>
            </div>
            <div class="col-sm-7">
                <label>When no. of Post(s)</label>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    <input ng-model="rules_config.NoOfPostConfVal" type="number" class="form-control" placeholder="No. of Posts">
                </div>
            </div>
            <div class="col-sm-7">
                <label>When no. of Friend(s)</label>
            </div>
            <div class="col-sm-5">
                <div class="form-group">
                    <input ng-model="rules_config.NoOfFrndConfVal" type="number" class="form-control" placeholder="No. of Posts">
                </div>
            </div>
        </div>
    </div>
    <div class="rule-footer">
        <button class="btn btn-primary pull-right" ng-click="set_rules_config()">Apply</button>
    </div>
</div>
<!-- Rule Settings Ends -->

<?php $this->load->view('admin/rules/create_rules_modal') ?>