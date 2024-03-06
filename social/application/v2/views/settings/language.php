<div class="container wrapper" ng-controller="PrivacyCtrl">
    <div class="row">
        <!-- Right Wall-->
        <?php $this->load->view('settings/sidebar') ?>
        <!-- //Right Wall-->
    <!-- Left Wall-->

    <aside class="col-sm-8 col-xs-12" ng-cloak>
      <div class="panel panel-default fadeInDown">
        <div class="panel-heading notification-header  border-bottom">
            <h3 class="panel-title" ng-bind="::lang.language"></h3> 
        </div>

        <div id="lang" class="tab-pane panel-body" role="tabpanel">
           <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12 center-block">
          	<div class="inner-form clearfix">
            <div class="form clearfix">
            <div class="form-group">
              <label>{{::lang.select}} {{::lang.language}}</label>
              <div class="text-field-select">
                <select data-disable-search="true" onChange="changeLanguage(this.value)" data-chosen="">
                  <option <?php if($this->config->item('language')=='english'){ echo 'selected="selected"'; } ?> value="english">English</option>
                  <option <?php if($this->config->item('language')=='french'){ echo 'selected="selected"'; } ?> value="french">French</option>
                </select>
              </div>
            </div>
          </div>
          </div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>