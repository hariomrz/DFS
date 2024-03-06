<?php if(isset($IsLoggedIn) && $IsLoggedIn): ?>
  <div ng-cloak class="panel panel-slider" ng-show="total_latest_users>0 && !IsMyDeskTab">
    <div class="panel-heading">
      <h3 class="panel-title">               
        <!-- <a target="_self" data-toggle="collapse" ng-click="toggleShowNewbie(); reinitSlider()" data-target="#newbies" class="accordion-icon pull-right">
          <span class="icon"><i class="ficon-arrow-down"></i></span>
        </a> -->
        <span class="text">{{::lang.w_the_newbies}} <span ng-bind="'('+total_latest_users+')'"></span></span>
      </h3>
    </div>
      <div class="panel-body">
          <ul class="listing-group multiple-items" ng-init="get_latest_users(1,20,15,0)">
          <slick class="slider" ng-if="latest_users.length>0" settings="newbieSliderSettings">
            <li ng-repeat="user in latest_users" class="items">
              <div class="multiple-slide">
                <div class="list-items-md">
                  <div class="list-inner">
                    <figure>
                      <a target="_self" ng-href="{{BaseUrl+user.ProfileURL}}"><img err-name="{{user.Name}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" class="img-circle"  ></a>
                    </figure>
                    <div class="list-item-body">
                      <h4 class="list-heading-xs"><a target="_self" class="ellipsis text-black" ng-href="{{BaseUrl+user.ProfileURL}}" ng-bind="user.Name"></a></h4>

                      <div ng-if="user.CityName!=='' && user.CountryName==''">
                        <small ng-bind="user.CityName"></small>
                      </div>
                      <div ng-if="user.CityName=='' && user.CountryName!==''">
                        <small ng-bind="user.CountryName"></small>
                      </div>
                      <div ng-if="user.CityName!=='' && user.CountryName!==''">
                        <small ng-bind="user.CityName+', '+user.CountryName"></small>
                      </div>
                      <div ng-if="user.CityName=='' && user.CountryName==''">
                        <small>&nbsp;</small>
                      </div>
                      
                      <p class="text-sm-muted" ng-if="user.TagLine!==''" ng-bind="user.TagLine"></p>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </slick>
          </ul>
      </div>
    <!-- <div ng-class="(showNewbie) ? 'in' : '' ;" class="collapse" id="newbies">
    </div> -->
  </div>
<?php endif; ?>