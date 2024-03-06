<div class="container wrapper" data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize(LoggedInUserGUID)" ng-cloak>
    <div class="page-heading">
        <h4 class="page-title uppercase" ng-bind="lang.page_create"></h4>
    </div>
    <div class="panel panel-info">            
        <div class="panel-body p-v-lg">              
            <div class="row" ng-init="PageCategories('','ParentCategory')">
                <div class="col-sm-4" data-ng-repeat="Cat in CategoryData">
                    <a class="thumbnail thumbnail-page" href="{{BaseUrl}}pages/{{Cat.CategoryID}}/createPage">
                        <figure>
                            <img ng-src="{{AssetBaseUrl+'img/page/'+Cat.Icon}}" />
                        </figure>
                        <div class="caption">
                          <h4 class="title" ng-bind="Cat.Name"></h4>
                        </div>
                    </a>                
                </div>
            </div>          
        </div>
   </div>    
</div>
<!--All POPUP's Included-->
<input type="hidden" id='OrderBy' value="">
<input type="hidden" id="hdnQuery" value="">
<input type="hidden" id="pageType" value="<?php echo $this->session->userdata('CurrentSection'); ?>">
<input type="hidden" id="searchgrp" value="">
<input type="hidden" id="hdncrdtype" value="">
<input type="hidden" id="GroupListPageNo" value="1" />
<input type="hidden" id="unique_id" value="" />
<input type="hidden" id="UserGUID" value="LoggedInUserGUID" />
