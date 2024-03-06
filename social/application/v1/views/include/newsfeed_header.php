<!-- <div class="secondary-fixed-nav">
  <div class="secondary-nav">
          <div class="container">
              <div class="row nav-row">
                  <div class="col-lg-7 col-xs-9 col-md-7 col-sm-8">
                      <aside class="pulled-nav tabs-menus marging-0">
                          <div class="tab-dropdowns">
                              <a href="javascript:void(0);"> <i class="icon-smallcaret"></i> <span>WALL</span> </a>
                          </div>
                          <ul class="nav navbar-nav small-screen-tabs hidden-xs">
                              <li ng-click="applyFilterType('0');" ng-class="(IsReminder=='0' && IsFilePage!=='1') ? 'active' : '' ;"><a>Posts</a></li>
                              <li ng-cloak ng-click="applyFilterType('3');" ng-class="(IsReminder=='1' && IsFilePage!=='1') ? 'active' : '' ;"><a>Reminders    <span ng-if="trr>0" ng-cloak ng-bind="trr" class="count-view-secondary"></span></a></li>
                              <li ng-cloak ng-click="IsFilePage = '1'" ng-class="(IsFilePage=='1') ? 'active' : '' ;"><a>Files</a></li>
                              
                          </ul>
                      </aside>
                  </div>

              </div>
          </div>
      </div>
    </div> -->

    <!-- Show mentions of -->
    <div class="mentions-block tab-pane" role="tabpanel" id="mentionView">
        <div class="mentions-fixed">
            <div class="container">
                <div class="mentions-content row">
                    <div class="col-sm-4 col-md-2">
                        <h3 class="panel-title pull-left">Show mentions of</h3>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-sm-8 col-md-10">
                        <div class="text-field-select">
                            <tags-input on-tag-adding="mentionHeight()" on-tag-removing="mentionHeight()" on-tag-added="getFilteredWall(); mentionHeight()" on-tag-removed="getFilteredWall(); mentionHeight()" display-property="Title" key-property="ModuleEntityGUID" ng-model="suggestPage" add-from-autocomplete-only="true" data-placeholder="+ add pages you manage">
                              <auto-complete load-on-empty="true" load-on-focus="true" min-length="0" source="loadPages($query)"></auto-complete>
                            </tags-input> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--// Mentions -->