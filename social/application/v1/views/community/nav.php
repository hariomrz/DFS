<div class="nav-tab-filter">     
  <div class="filter-fixed" ng-show="filterFixed" ng-cloak>
    <button class="btn btn-default close-filter" ng-click="setFilterFixed(false)">
      <span class="icon">
        <i class="ficon-cross"></i>                
      </span>
    </button>
    <?php $this->load->view('community/filter') ?>
  </div>            
  <div class="row">
    <div class="col-xs-10">
      <ul class="nav nav-tabs nav-tabs-liner primary nav-tabs-scroll" role="tablist">
        <li ng-class="(PostTypeName=='All Posts') ? 'active' : '' ;">
              <a class="routing-url" ng-href="{{BaseUrl}}" ng-bind="::lang.all_posts"></a>
          </li>
          <li ng-class="(PostTypeName=='Discussions') ? 'active' : '' ;">
              <a class="routing-url" ng-href="{{BaseUrl+'community/type/discussions'}}" ng-bind="::lang.discussions"></a>
          </li>
          <li ng-class="(PostTypeName=='Questions') ? 'active' : '' ;">
              <a class="routing-url" ng-href="{{BaseUrl+'community/type/questions'}}" ng-bind="::lang.qna"></a>
          </li>
          <li ng-class="(PostTypeName=='Articles') ? 'active' : '' ;">
              <a class="routing-url" ng-href="{{BaseUrl+'community/type/articles'}}" ng-bind="lang.articles"></a>
          </li>
          <li ng-cloak ng-hide="LoginSessionKey==''" ng-class="(PostTypeName=='Announcements') ? 'active' : '' ;">
              <a class="routing-url" ng-href="{{BaseUrl+'community/type/announcements'}}" ng-bind="lang.c_announcement"></a>
          </li>
      </ul> 
    </div>
    <?php if($this->session->userdata('LoginSessionKey')!=''){ ?>
    <div class="col-xs-2">
      <div class="filter-actions">
        <button class="btn btn-default btn-sm btn-filter" ng-click="setFilterFixed(true)">
          <span class="icon">
            <i class="ficon-filter"></i>
          </span>            
        </button>
      </div>
    </div>
    <?php } ?>
  </div>
</div>