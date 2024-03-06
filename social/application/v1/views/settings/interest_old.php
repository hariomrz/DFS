<div class="container wrapper">
    <div class="row">
        <!-- Right Wall-->
        <?php $this->load->view('settings/sidebar') ?>
        <!-- //Right Wall-->
        <!-- Left Wall-->
        <aside class="col-md-8 col-sm-8 col-xs-12" id="InviteFriendCtrl" ng-controller="InviteFriendCtrl">
            <div class="panel panel-default fadeInDown" ng-cloak>
                <div class="panel-heading notification-header  border-bottom">
                    <h3 class="panel-title">Area of Interest</h3>
                    <button ng-cloak ng-if="interest_list.length>0" class="btn btn-primary btn-sm pull-right" ng-click="save_categories(1)" type="button">SAVE</button>
                </div>
                <div class="panel-body" ng-init="get_interest();">
                    <div class="area-of-interest">
                        <div class="row">
                            <ul class="communities-list">
                                <li ng-cloak ng-repeat="interest in interest_list" class="col-sm-4 col-xs-6">
                                  <div class="list-content" ng-class="(interest.IsInterested==1) ? 'active' : '' ;">
                                      <input type="checkbox" name="CategoryIDs[]" value="{{interest.CategoryID}}" ng-checked="interest.IsInterested==1" id="Category-{{interest.CategoryID}}" onchange="$(this).parent().toggleClass('active');" >
                                      <div class="imagewrapper"> 
                                        <div class="selected-communities"> <i class="icon-selected-cm"></i> </div>
                                        <figure style="background-image:url('<?php echo IMAGE_SERVER_PATH ?>upload/category/220x220/{{interest.ImageName}}');"></figure>
                                      </div>
                                      <div class="list-title ellipsis" ng-bind="interest.Name"></div>
                                  </div>
                              </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel-footer privacy-footer">
                    <button ng-cloak ng-if="interest_list.length>0" class="btn btn-primary btn-sm pull-right" ng-click="save_categories(1)" type="button">SAVE</button>                         
                </div>
            </div>
        </aside>
        <!-- //Left Wall-->
    </div>
</div>