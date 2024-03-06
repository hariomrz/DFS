<!-- Interest popup start -->
  <div class="modal fade" ng-if="SettingsData.m31==1" id="addInterest" ng-init="get_interests();" ng-cloak>
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="icon-close"></i></span>
          </button>
          <h4 class="modal-title">Add Interest</h4>
        </div>
        <div class="modal-body">

          <div class="form-group">              
              <div class="tag-added">
                <div class="tag-edit-view">
                  <div class="tag-add">
                    <tags-input ng-model="interests_popup" min-length="2" add-from-autocomplete-only="true" ng-model="search_tags" key-property="CategoryID" display-property="Name" on-tag-added="addTagInList($tag)" on-tag-removed="removeTagInList($tag)" placeholder="Search" replace-spaces-with-dashes="false" template="interestTags">
                      <auto-complete source="loadSearchInterest($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4"></auto-complete>
                    </tags-input>
                    <script type="text/ng-template" id="interestTags">
                      <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                          <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                          <a class="ficon-cross tag-remove ng-scope" ng-click="$removeTag()"></a>
                      </div>
                    </script>
                  </div>
                </div>
              </div>
          </div>
          
          <div class="global-scroll max-ht400 mCustomScrollbar">
            <div class="row list-group-inline">
              <div class="col-sm-4" ng-repeat="interest in allInterests">
                <div class="panel panel-list">
                  <div class="panel-heading">
                    <h3 class="panel-title" ng-bind="interest.Name"></h3>
                  </div>
                  <div class="panel-body">
                    <ul class="list-group-seleted">
                      <li class="list-items-xs" ng-repeat="i in interest.interest" ng-class="isActive(i.CategoryID)">
                        <div class="list-inner">             
                          <a class="icon check-icon"><i class="ficon-check"></i></a>
                          <figure>
                            <a ng-click="addTagToPopup(i);"><img err-src="{{AssetBaseUrl+'img/Interest-default.jpg'}}" ng-src="{{image_server_path+'upload/category/220x220/'+i.ImageName}}" class="img-circle"  ></a>
                          </figure>
                          <div class="list-item-body">
                            <h4 class="list-heading-xs"><a ng-click="addTagToPopup(i);" class="ellipsis" ng-bind="i.Name"></a></h4>
                          </div>
                        </div>
                      </li>
                    </ul> 
                  </div>
                </div>
            </div>
          </div>

          </div>
        </div>
        <div class="modal-footer">
          <a class="btn btn-primary pull-right" ng-click="addToInterest()">Add</a>
        </div>
      </div>
    </div>
  </div>
<!-- Interest popup ends -->