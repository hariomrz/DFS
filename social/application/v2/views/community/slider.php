<div class="panel panel-content md">
  <div class="container">
    <div class="panel-heading">
      <h3 class="panel-title" ng-bind="lang.c_have_question_find_answer"></h3>
    </div>
    <!-- main categories slider -->
    <div class="panel-body">
      <div class="slick-carousel">
        <ul id="QuestionSlider" class="fixed-width-slide list-nothumb" ng-init="get_parent_categories();">
          <slick class="slider" ng-if="community_categories.length>0" settings="categoryConfig">
            <li class="image-hover-effect items" ng-repeat="category in community_categories" ng-cloak>
              <a target="_self" ng-href="{{BaseUrl+category.FullURL}}" class="thumbnail">
                <figure>       
                  <div class="hover-overlay slider-img-holder" ng-if="category.ProfilePicture!==''">           
                    <img ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+category.ProfilePicture}}"  class="img-rounded">
                  </div>
                  <div class="hover-overlay slider-img-holder block" ng-if="category.ProfilePicture==''" ng-cloak>
                    <i class="ficon-category f-50" err-src="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" class="ficon-category f-50"></i>
                  </div>
                </figure>
                <figcaption ng-bind="category.Name"></figcaption>
              </a>
            </li>
          </slick>
        </ul>
      </div>
    </div>
    <!-- main categories slider -->
    <div class="panel-footer">
      <a target="_self" class="btn btn-letter btn-line-primary" href="<?php echo site_url('community/discover') ?>" ng-bind="lang.c_all_discussion_rooms"></a>
    </div>        
  </div>
</div>