<div class="container wrapper" ng-controller="InviteFriendCtrl" id="InviteFriendCtrl">
  <div class="custom-modal fadeInDown" ng-init="get_interest()" ng-cloak>
    <div class="row offset-title"> 
      <div class="col-xs-12">
    		<h4 class="label-title secondary-title" ng-bind="lang.help_grow_network"></h4>
      </div>  
    </div>

    <div class="row">
      <div class="col-xs-12 title-text-500">
           <span ng-cloak ng-if="interest_list.length>0" ng-bind="lang.choose_area_of_interest"></span>
           <button ng-cloak ng-if="interest_list.length>0" class="btn btn-primary btn-sm pull-right" type="button" ng-click="save_categories()" ng-bind="lang.caps_next"></button>
      </div>
    </div>

    <div class="communities-block">
      <div class="row">
        <ul class="communities-list" ng-cloak> 
          <li ng-repeat="interest in interest_list" class="col-sm-3 col-xs-6">
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
    <div class="row">
      <div class="col-xs-12">
        <button ng-cloak ng-if="interest_list.length>0" class="btn btn-primary btn-sm pull-right" type="button" ng-click="save_categories()" ng-bind="lang.caps_next"></button>
      </div>        
    </div>     
     
   </div>
   <div class="loader" ng-if="interest_list.length==0" style="width:60px;height:60px;transform:translate(-50%,-50%)"></div>
 </div>