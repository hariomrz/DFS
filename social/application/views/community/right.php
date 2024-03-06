<aside class="col-lg-4 col-sm-4 sidebar" data-scroll="sticky"  >
  <?php if($this->session->userdata('LoginSessionKey')!=''){ ?>
    <div class="panel panel-transparent" id="TourTwo">
      <button class="btn btn-letter btn-primary btn-block" ng-click="setEditVariable(false);showNewsFeedPopup();" ng-bind="lang.c_start_a_discussion"></button>
    </div>
  <?php } else { ?>
    <div class="panel panel-transparent">
      <button class="btn btn-letter btn-primary btn-block" ng-click="loginRequired()" ng-bind="lang.c_start_a_discussion"></button>
    </div>
  <?php } ?>
  
  <!-- Newbies -->
    <div ng-if="LoginSessionKey != ''" ng-cloak ng-show="total_latest_users > 0" class="panel panel-widget" ng-init="get_latest_users(1, 20, 15,1)">
        <div class="panel-heading">
            <h3 class="panel-title"> 
                <span class="text">Newbies</span>
            </h3>
        </div>
        <div class="panel-body no-padding">
            <div class="newbies-slider" id="newbies-slider">
                <slick class="slider" ng-if="latest_users.length > 0" settings="newbiesConfig">
                    <div ng-repeat="(k,value) in latest_users">
                        <ul class="list-items-hovered list-items-borderd list-items-newbies">
                            <li ng-repeat="(key,user) in value">
                                <div class="list-items-xmd">
                                    <div class="actions">
                                        <a class="btn btn-sm" ng-cloak ng-class="(user.SentRequest)?'btn-primary':'btn-default'">
                                            <span class="icon" ng-cloak ng-if="!user.SentRequest"><i class="ficon-plus f-lg"></i></span>
                                            <span class="icon" ng-cloak ng-if="user.SentRequest"><i class="ficon-check f-lg"></i></span>
                                            <span class="text" ng-cloak ng-if="!user.SentRequest" ng-click="follow(user.ModuleEntityGUID, user, user, key, 'user', 1, 1);">Follow</span>
                                            <span class="text" ng-if="user.SentRequest" ng-click="follow(user.ModuleEntityGUID, user, user, key, 'user', 1, 0);">Following</span>
                                        </a>
                                    </div>
                                    <div class="list-inner">
                                        <figure>
                                            <a entitytype="user" entityguid="{{user.ModuleEntityGUID}}" ng-href="<?php echo site_url() ?>{{::user.ProfileURL}}" target="_self">
                                                <img err-name="{{user.Name}}" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + user.ProfilePicture}}" class="img-circle" alt="" title="" err-Name="{{user.Name}}" />
                                            </a>
                                        </figure>
                                        <div class="list-item-body">
                                            <h4 class="list-heading-xs ellipsis">
                                                <a target="_self" entitytype="user" entityguid="{{user.ModuleEntityGUID}}" class="a-link name" ng-href="<?php echo site_url() ?>{{::user.ProfileURL}}">{{::user.Name}}</a>
                                            </h4>
                                            <div ng-if="user.CityName !== '' && user.CountryName == ''" class="ellipsis">
                                                <small ng-bind="user.CityName"></small>
                                            </div>
                                            <div ng-if="user.CityName == '' && user.CountryName !== ''" class="ellipsis">
                                                <small ng-bind="user.CountryName"></small>
                                            </div>
                                            <div ng-if="user.CityName !== '' && user.CountryName !== ''" class="ellipsis">
                                                <small ng-bind="user.CityName + ', ' + user.CountryName"></small>
                                            </div>
                                            <div ng-if="user.CityName == '' && user.CountryName == ''" class="ellipsis">
                                                <small>&nbsp;</small>
                                            </div>
                                        </div>
                                    </div>                    
                                </div>
                            </li>
                        </ul>
                    </div>
                </slick>
            </div>
        </div>
        <div class="panel-footer">
            <a ng-cloak ng-if="LoginSessionKey !== ''">&nbsp;</a>
        </div>
    </div>
    <!-- Newbies ends here -->

  <?php $this->load->view('widgets/event-near-you') ?>

<!--  <div class="panel panel-widget" id="TourOne" ng-if="LoginSessionKey!==''" ng-hide="top_active_user.length==0" ng-init="get_top_active_users()">
    <div class="panel-heading">
      <h3 class="panel-title" ng-bind="lang.c_top_contributors"></h3>        
    </div>
    <div class="panel-body no-padding">
      <ul class="list-items-hovered list-items-borderd">
        <li class="items" ng-repeat="user in top_active_user">
          <div class="list-items-sm">
            <div class="list-inner">
              <figure>
                <a target="_self" class="loadbusinesscard" entitytype="user" entityguid="{{user.UserGUID}}" ng-href="{{BaseUrl+user.ProfileUrl}}">
                  <img err-name="{{user.Name}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+user.ProfilePicture}}" class="img-circle"  >
                </a>
              </figure>
              <div class="list-item-body">
                <h4 class="list-heading-xs ellipsis"><a target="_self" entitytype="user" entityguid="{{user.UserGUID}}" ng-href="{{BaseUrl+user.ProfileUrl}}" class="loadbusinesscard" ng-bind="user.Name"></a></h4>
                <div>
                  <small ng-cloak ng-if="user.TotalFollowers==1" ng-bind="'1 '+lang.c_follower"></small>
                  <small ng-cloak ng-if="user.TotalFollowers>1" ng-bind="user.TotalFollowers+' '+lang.c_followers"></small>
                </div>
              </div>                    
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>-->

  <div class="panel panel-widget" ng-cloak ng-hide="article_list.length==0" ng-init="get_suggested_articles()">
    <div class="panel-heading">
      <h3 class="panel-title"> 
        <span class="text" ng-bind="lang.c_popular_articles"></span>
      </h3>        
    </div>
    <div class="panel-body">
      <div class="thumbnail thumbnail-card thumbnail-popular" ng-repeat="article in article_list">
        <div class="thumbnail-header">
            <div class="thumbnail-icn">
                <a class="icon" ng-if="key >= 1" ng-cloak tooltip title="Add to favorite" data-container="body">
                    <i class="ficon-star f-xlg"></i>
                </a>
                <a class="icon active" ng-if="key <= 0" ng-cloak tooltip title="Add to favorite" data-container="body">
                    <i class="ficon-star f-xlg"></i>
                </a>
            </div>
            
            <h5 class="thumbnail-subtitle"> 
                <span class="text" ng-bind="article.EntityName"></span>
            </h5>                                
        </div>
        <div class="thumbnail-body">  
            <div class="caption">      
                <div class="content">           
                  <h4 class="thumbnail-title"><a ng-href="{{BaseUrl+article.ActivityURL}}" ng-bind="article.PostTitle"></a></h4>
                </div>  
                  <div class="member-list-block" ng-if="article.MembersList.length>0">
                      <ul class="member-list">
                          <li class="member-item">                                           
                            <a target="_self" ng-repeat="member in article.MembersList" ng-if="member.ProfilePicture!==''" class="thumb-item" tooltip ng-attr-title="{{member.Name}}" data-container="body" data-placement="bottom">
                               <img err-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+member.ProfilePicture}}"   />
                            </a>  
                          </li>
                      </ul>
                      <ul class="list-activites text-off">
                        <li>
                          <span ng-bind-html="::get_members_talking(article.MembersList)"></span>                  
                        </li> 
                      </ul>
                  </div>
            </div>
        </div>
        <div class="thumbnail-footer">
            <ul class="feed-actions small">
              <li>
                <span class="like-btn">
                  <i class="ficon-heart sm"></i>
                  <abbr class="sm" ng-bind="article.NoOfLikes"></abbr>
                </span>
              </li>
              <li ng-if="article.NoOfComments>0"><a ng-bind="'Comment ('+article.NoOfComments+')'"></a></li>                                  
            </ul>
            <div class="btn-toolbar btn-toolbar-xs right" ng-if="LoginSessionKey!==''">
                <button ng-click="subscribeEmit('ACTIVITY', article.ActivityGUID); article.IsSubscribed='1'" class="btn btn-default btn-xs p-h-9" ng-if="article.IsSubscribed == '0'" ng-cloak>Follow</button>
                <button ng-click="subscribeEmit('ACTIVITY', article.ActivityGUID); article.IsSubscribed='0'" class="btn btn-primary btn-xs p-h-9" ng-if="article.IsSubscribed == '1'" ng-cloak>
                    <span class="icon"><i class="ficon-check"></i></span>
                    <span class="text">Following</span>
                </button>
                <!-- subscribeEmit('ACTIVITY', data.ActivityGUID); -->
            </div>
            <div class="btn-toolbar btn-toolbar-xs right" ng-if="LoginSessionKey==''">
                <button ng-click="subscribeEmit('ACTIVITY', article.ActivityGUID);" class="btn btn-default btn-xs p-h-9" ng-if="article.IsSubscribed == '0'" ng-cloak>Follow</button>
                <button ng-click="subscribeEmit('ACTIVITY', article.ActivityGUID);" class="btn btn-primary btn-xs p-h-9" ng-if="article.IsSubscribed == '1'" ng-cloak>
                    <span class="icon"><i class="ficon-check"></i></span>
                    <span class="text">Following</span>
                </button>
                <!-- subscribeEmit('ACTIVITY', data.ActivityGUID); -->
            </div>
        </div>
      </div>
    </div>
  </div>

  <?php
    if($this->session->userdata('LoginSessionKey')!=''){
        $this->load->view('widgets/suggested-groups');
        $this->load->view('widgets/popular-tags');
    }
  ?>
</aside>