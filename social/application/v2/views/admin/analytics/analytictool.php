<?php
$t = "Girish";
?>

<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/analytics/analytictool') ?>"><?php echo lang('AnalyticsTools_Tools'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('AnalyticsTools_AnalyticTool'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
        <div data-ng-controller="analyticTools" ng-init="getAnalyticsProviders();" class="container">
            <div class="panel">
            <form method="post" name="frmsmtp" id="smtp_form" autocomplete="off">
            <div class="panel-body">
                <div class="alert alert-danger clearfix" style="display:none;" >
                    <span id="commonError"></span>
                </div>
            
                <div class="row">
                    <div class="col-sm-6">
                        <label class="label"><?php echo lang('AnalyticsTools_AnalyticProviders'); ?></label>  
                        <select class="width100" chosen data-disable-search="true" data-ng-options="item.ProviderName for item in analyticProviders track by item.AnalyticsProvidersID" name="analyticProviders" id="analyticProviders" ng-model="selectedprovider" ng-change="loadToolsData();" data-placeholder="Select Provider">
                            <option value="">Select Provider</option>
                        </select> 
                        <div class="errordiv">
                            <div class="error-holder">{{errorMessage}}</div>
                        </div> 
                        <label class="label" for="subjects"><?php echo lang('AnalyticsTools_AnalyticCode'); ?></label>   
                        <div class="text-field" style="height: 225px;">
                            <textarea style="height: 225px;" cols="20" id="AnalyticsCode" name="AnalyticsCode" rows="2" type="text"></textarea>
                            <span class="field-validation-valid" data-valmsg-for="AnalyticsCode" data-valmsg-replace="true"></span>                            
                        </div>
                        <div class="errordiv">
                            <div class="error-holder">{{errorCodeMessage}}</div>
                        </div>
                        <div class="clearfix">&nbsp;</div> 
                    </div>
                </div>
            
            </div>
            <div class="panel-footer">
                 <div style="max-width:52%; margin-bottom:10px">
                    <?php if(in_array(getRightsId('analytics_tool_save_edit_event'), getUserRightsData($this->DeviceType))){ ?>
                        <button id="btnSave" type="submit" class="btn btn-primary" ng-click="SaveAnalyticsCode();"><?php echo lang('Save_Lower'); ?></button>
                    <?php } ?>
                    <span id="spnCustomError" style="float: right; color: red"></span>
                </div>
            </div>
            </form>

            </div>
        </div>
    
</section>
