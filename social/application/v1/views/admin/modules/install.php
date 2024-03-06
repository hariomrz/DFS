<div class="container" ng-controller="ModuleCtrl" id="ModuleCtrl">
  <div class="main-container" ng-init="getModules();"> 
      <div class="panel panel-module">
        <div class="panel-heading">
          <h3 class="panel-title">VSocial Marketplace</h3>
          <p>Common place to manage the modules in your product</p>
        </div>
         <div class="panel-body">
          <div class="row">
            <div class="col-md-10 col-md-offset-1">
              <div class="row">
                <div ng-repeat="module in modules" class="col-lg-3 col-md-4 col-sm-4">
                  <div class="thumbnail thumbnail-module">
                    <figure>
                      <a class="circle">
                        <img ng-cloak ng-if="module.Icon!=''" ng-src="{{BaseUrl}}assets/img/module/{{module.Icon}}" >
                        <img ng-cloak ng-if="module.Icon==''" ng-src="{{BaseUrl}}assets/img/module/{{defaultModuleIcon}}" >
                      </a>
                    </figure>
                    <div class="caption">
                      <div class="content">
                        <h4 class="title" ng-bind="module.ModuleName"></h4>
                      </div>
                      <div class="btn-toolbar btn-toolbar-center">
                        <a ng-cloak ng-if="module.IsActive=='0'" ng-click="changeModuleStatus(module.ModuleID,'1',module);" class="btn btn-primary btn-block">Activate</a>
                        <a ng-cloak ng-if="module.IsActive=='1'" ng-click="changeModuleStatus(module.ModuleID,'0',module);" class="btn btn-primary outline btn-block">
                          <span class="icn">
                            <i class="ficon-doubletick"></i>  
                          </span>
                          <span class="text">Deactivate</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>  
          </div>
         </div>
      </div>
  </div>
</div>