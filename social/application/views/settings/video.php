<div class="container wrapper" ng-controller="PrivacyCtrl">
    <div class="row">
        <!-- Right Wall-->
        <?php $this->load->view('settings/sidebar') ?>
        <!-- //Right Wall-->
    <!-- Left Wall-->

    <aside class="col-sm-8 col-xs-12" ng-cloak>
      <div class="panel panel-default fadeInDown">
        <div class="panel-heading notification-header  border-bottom">
            <h3 class="panel-title" ng-bind="::lang.video_settings"></h3> 
        </div>

        <div id="lang" class="tab-pane panel-body" role="tabpanel">
           <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12 center-block">
          	<div class="inner-form clearfix">
            <div class="form clearfix">
            <div class="form-group">
              <label>{{::lang.video_autoplay}}</label>
              <div class="text-field-select">
                <select ng-model="VideoAutoplay" data-disable-search="true" onChange="changeAutoplay(this.value)" ng-options="video.Value as video.Label for video in [{Label:'On',Value:'1'},{Label:'Off',Value:'0'}]" data-chosen="">
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