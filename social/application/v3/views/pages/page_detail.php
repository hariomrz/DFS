<!--Container-->
<!--Banner-->

<div data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')" ng-cloak>
  <div ng-init="GetPageDetails('<?php //echo $PageGUID;?>')">
    <?php $this->load->view('profile/profile_banner'); ?>
    <!--//Banner-->
    <div class="container wrapper" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="GetWallPostInit()">
      <div class="row"> 
        <aside class="col-md-3 col-sm-4 col-xs-12 sidebar pull-right" ng-class="{'col-md-3':IsSingleActivity == true, 'col-md-3':IsSingleActivity == false}">
            <?php 
            if(empty($ActivityGUID))
              {
                $this->load->view('pages/about_page'); 

                if (!$this->settings_model->isDisabled(42) && (( isset($pname) && $pname == 'wall' ) || ( isset($IsNewsFeed) && $IsNewsFeed == 1 ))) {
                    $this->load->view('widgets/sticky-post');
                }
                $this->load->view('pages/create_page_html'); 
              }
              else
              {
                $this->load->view('widgets/similar-discussions');
              }
             ?>
        </aside>  
        <div class="col-md-9 col-sm-8 col-xs-12 pull-left">          
                    
            <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>
            
            
        </div>
      </div>
    </div>
  </div>
</div>
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="FeedSortBy" value="2" />
      <input type="hidden" id="IsMediaExists" value="2" />
      <input type="hidden" id="PostOwner" value="" />
      <input type="hidden" id="ActivityFilterType" value="0" />
      <input type="hidden" id="AsOwner" value="0" />
<!--//Container-->