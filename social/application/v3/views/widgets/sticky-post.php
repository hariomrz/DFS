<?php if($this->session->userdata('UserID')){ ?>
  <div class="panel panel-widget" ng-hide="(IsNewsFeed=='1' && IsMyDeskTab) || (config_detail.ModuleID=='3' && !config_detail.IsAdmin)">
    <div class="panel-heading no-border">
      <h3 class="panel-title"> 
        <span class="text" ng-bind="lang.w_sticky_post"></span>
      </h3>        
    </div>
    <div class="panel-body no-padding">
      <div class="nav-tabs-default">
        <ul class="nav nav-tabs nav-tabs-liner primary row no-gutter" role="tablist">
          <li role="presentation" class="active col-xs-6"><a target="_self" data-target="#stickyMe" role="tab" data-toggle="tab" ng-bind="lang.w_my_sticky"></a></li>
          <li role="presentation" class="col-xs-6"><a target="_self" data-target="#stickyShare" role="tab" data-toggle="tab" ng-bind="lang.w_shared_with_me"></a></li>
        </ul> 
      </div>
      <!-- tab contents begins here-->
      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="stickyMe" ng-controller="StickyPostController as StickyPostCtrl" ng-init="StickyPostCtrl.stickyType = 2; StickyPostCtrl.getStickyPostList();">
          <div class="global-scroll max-ht300 mCustomScrollbar">
            <ul class="listing-group sticky-post">
              <li ng-repeat="( stickyIndex, sticky ) in StickyPostCtrl.stickyPostList">
                <span class="sticky">                                        
                   <i class="ficon-pin rotate-45"></i>
                </span>
                <ul class="feed-nav">
                  <li class="dropdown">
                    <button type="button" data-toggle="dropdown" class="btn btn-circle">
                      <span class="icon">
                        <i class="ficon-arrow-down f-lg"></i>
                      </span>
                    </button>
                    <ul class="dropdown-menu">
                      <li data-ng-if="( ( ( sticky.CanMakeSticky == 3 ) || ( sticky.CanMakeSticky == 2 ) || ( sticky.CanMakeSticky == 1 ) ) && sticky.SelfSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 1, stickyIndex);" ng-bind="lang.w_remove_sticky_me"></a>
                      </li>
                      <li data-ng-if="( ( ( sticky.CanMakeSticky == 2 ) || ( sticky.CanMakeSticky == 1 ) ) && sticky.GroupSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 2, stickyIndex);" ng-bind="lang.w_remove_sticky_group"></a>
                      </li>
                      <li data-ng-if="( ( sticky.CanMakeSticky == 1 ) && sticky.EveryoneSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 3, stickyIndex);" ng-bind="lang.w_remove_sticky_everyone"></a>
                      </li>
                    </ul>
                  </li>
                </ul>
                <div class="list-items-xs"> 
                    <div class="list-inner">
                      <figure>
                        <a target="_self" ng-href="{{ StickyPostCtrl.baseURL + sticky.ProfileURL }}">
                            <img ng-if="( sticky.ProfilePicture != '' )" ng-src="{{ StickyPostCtrl.ImageServerPath + 'upload/profile/220x220/' + sticky.ProfilePicture }}" class="img-circle"  >
                           <span ng-if="sticky.ProfilePicture=='' || sticky.ProfilePicture=='user_default.jpg' " class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(sticky.Name)"></span></span>
                        </a>
                      </figure>
                      <div class="list-item-body">
                          <h4 class="list-heading-xs"><a target="_self" class="ellipsis" ng-bind="sticky.Name" ng-href="{{ StickyPostCtrl.baseURL + sticky.ProfileURL }}"></a></h4>
                          <small ng-bind="StickyPostCtrl.sticky_date_format((sticky.CreatedDate))"></small>
                        </div>
                    </div>
                </div>
                <div ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" class="sticky-media-placeholder cursor-pointer">
                  <span class="icon" ng-cloak ng-if="(sticky.Album.length && sticky.Album[0].Media.length) || (sticky.Files.length)"><i class="ficon-attachment"></i></span>
                  <p class="title" ng-bind-html="getStickyText(sticky);">Attachment with this post</p>
                </div>
                <!--<div class="sticky-content">
                  <p ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" ng-if="sticky.PostTitle" ng-bind-html="sticky.PostTitle" class="cursor-pointer"></p>  
                  <p ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" ng-if="!sticky.PostTitle" ng-bind-html="textToLink(sticky.PostTitle, true)" class="cursor-pointer"></p>  
                </div>-->

              </li> 
            </ul>
          </div>
          <div class="nodata-panel" ng-cloak ng-if="StickyPostCtrl.stickyPostList.length==0">
            <div class="nodata-text p-v-lg p-h-sm">
                <span class="nodata-media">
                    <img src="<?php echo site_url() ?>assets/img/sticky.png" >
                </span>
              <p>{{::lang.w_sticky_posts_to_show}} <br> {{::lang.w_notice_board}}</p>
              <a target="_self" ng-click="StickyPostCtrl.openStickyPopup('tutorial');" class="text-primary semi-bold" ng-bind="lang.w_know_more"></a>
            </div>
          </div>
          <!--<div class="tabpanel-footer">
            <a target="_self" class="text-link bold">View All</a>
          </div>-->
        </div>
        <div role="tabpanel" class="tab-pane" id="stickyShare" ng-controller="StickyPostController as StickyPostCtrl" ng-init="StickyPostCtrl.stickyType = 1; StickyPostCtrl.getStickyPostList();">
          <div class="global-scroll max-ht300 mCustomScrollbar">
            <ul class="listing-group sticky-post">
              <li ng-repeat="( stickyIndex, sticky ) in StickyPostCtrl.stickyPostList">
                <span class="sticky">                                        
                   <i class="ficon-pin rotate-45"></i>
                </span>
                <ul class="feed-nav">
                  <li class="dropdown">
                    <button type="button" data-toggle="dropdown" class="btn btn-circle">
                      <span class="icon">
                        <i class="ficon-arrow-down f-lg"></i>
                      </span>
                    </button>
                    <ul class="dropdown-menu">
                      <li data-ng-if="( ( ( sticky.CanMakeSticky == 3 ) || ( sticky.CanMakeSticky == 2 ) || ( sticky.CanMakeSticky == 1 ) ) && sticky.SelfSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 1, stickyIndex);" ng-bind="lang.w_remove_sticky_me"></a>
                      </li>
                      <li data-ng-if="( ( ( sticky.CanMakeSticky == 2 ) || ( sticky.CanMakeSticky == 1 ) ) && sticky.GroupSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 2, stickyIndex);" ng-bind="lang.w_remove_sticky_group"></a>
                      </li>
                      <li data-ng-if="( ( sticky.CanMakeSticky == 1 ) && sticky.EveryoneSticky )">
                        <a target="_self" data-ng-click="StickyPostCtrl.unmarkAsSticky(sticky.ActivityGUID, 3, stickyIndex);" ng-bind="lang.w_remove_sticky_everyone"></a>
                      </li>
                    </ul>
                  </li>
                </ul>
                <div class="list-items-xs"> 
                    <div class="list-inner">
                      <figure>
                        <a target="_self" ng-href="{{ StickyPostCtrl.baseURL + sticky.ProfileURL }}">
                            <img ng-if="( sticky.ProfilePicture != '' )" ng-src="{{ StickyPostCtrl.ImageServerPath + 'upload/profile/220x220/' + sticky.ProfilePicture }}" class="img-circle"  >
                           <span ng-if="sticky.ProfilePicture=='' || sticky.ProfilePicture=='user_default.jpg' " class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(sticky.Name)"></span></span>
                        </a>
                      </figure>
                      <div class="list-item-body">
                          <h4 class="list-heading-xs"><a target="_self" class="ellipsis" ng-bind="sticky.Name" ng-href="{{ StickyPostCtrl.baseURL + sticky.ProfileURL }}"></a></h4>
                          <small ng-bind="StickyPostCtrl.sticky_date_format((sticky.CreatedDate))"></small>
                        </div>
                    </div>
                </div>
                <div ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" class="sticky-media-placeholder cursor-pointer">
                  <span class="icon" ng-cloak ng-if="(sticky.Album.length && sticky.Album[0].Media.length) || (sticky.Files.length)"><i class="ficon-attachment"></i></span>
                  <p class="title" ng-bind-html="getStickyText(sticky);">Attachment with this post</p>
                </div>
                <!--<div class="sticky-content">
                  <p ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" ng-if="sticky.PostTitle" ng-bind-html="sticky.PostTitle" class="cursor-pointer"></p>  
                  <p ng-click="StickyPostCtrl.openStickyPopup('activityHighlight', sticky.ActivityGUID);" ng-if="!sticky.PostTitle" ng-bind-html="textToLink(sticky.PostTitle, true)" class="cursor-pointer"></p>  
                </div>-->

              </li> 
            </ul>
          </div>
          <div class="nodata-panel" ng-cloak ng-if="StickyPostCtrl.stickyPostList.length==0">
            <div class="nodata-text p-v-lg p-h-sm">
                <span class="nodata-media">
                    <img src="<?php echo site_url() ?>assets/img/sticky.png" >
                </span>
              <p>{{::lang.w_sticky_posts_to_show}} <br> {{::lang.w_notice_board}}</p>
              <a target="_self" ng-click="StickyPostCtrl.openStickyPopup('tutorial');" class="text-primary semi-bold" ng-bind="lang.w_know_more"></a>
            </div>
          </div>
          <!--<div class="tabpanel-footer">
            <a target="_self" class="text-link bold">View All</a>
          </div>-->
        </div>
      </div>
    </div>
  </div>
<?php } ?>