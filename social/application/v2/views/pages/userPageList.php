<!--//Header--><!-- #EndLibraryItem --><!--Container-->
<div class="container wrapper pages_list">
  <div class="row" data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize(LoggedInUserGUID)" ng-cloak>
    <!-- Left Wall-->
    <aside class="col-md-9 col-sm-8">
      <div class="panel-group">        
        <!-- New -->
        <div class="page-heading">
          <div class="row">
            <div class="col-sm-3">
                <h4 class="page-title">PAGES</h4>
            </div>
            <div class="col-sm-9">
                <div class="page-actions">
                    <ul class="list-page">                                            
                        <li class="items" ng-class="(pageListsLen <= 0 && pageFollowListsLen <= 0) ? 'filters filters-hide': 'filters filters-show'">
                            <div class="input-search form-control right">
                                <input type="text" placeholder="Quick search" class="form-control" ng-keyup="SearchListByKey()" ng-model="myPageSearch">
                                <div class="input-group-btn">
                                  <button class="btn" ng-click="ResetSearch();">
                                    <i class="ficon-search"></i>
                                  </button>
                                </div>
                            </div>
                        </li>
                        <li class="items" ng-cloak ng-if="pageListsLen > 0 || pageFollowListsLen > 0">
                            <div class="dropdown">
                                <button type="button" class="btn btn-default" data-toggle="dropdown" aria-expanded="false">
                                    <span class="text" ng-bind="sort_by_page_name"></span> <i class="caret"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a ng-click="myPages('Title', 'ASC');myFollowPages('Title', 'ASC')" ng-bind="lang.name"></a></li>
                                    <li><a ng-click="myPages('LastActionDate', 'DESC');myFollowPages('LastActionDate', 'DESC')" ng-bind="lang.activity_date"></a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
          </div>
        </div>
        <!-- New -->

        <div class="clear"></div>
      
        <div class="panel panel-info" ng-show="pageListsLen>0" ng-cloak ng-init="myPages('LastActionDate', 'DESC')">
          <div ng-cloak class="panel-heading ">
            <h3 class="panel-title" ng-bind="lang.page_manage"></h3>
          </div>
          <!-- New -->
          <div class="panel-body">
            <ul class="list-items-group">
              <li class="items" id="page_{{list.PageGUID}}" ng-repeat="list in listObj = pageLists | limitTo: paginationLimit('MyPage')" repeat-done="repeatDoneBCard()" ng-hide="list.length>0">
                <div class="list-items-sm">
                  <div class="actions right">
                    <a ng-click='removePageFromList(list.PageGUID,"Manage",lang.delete_page,lang.delete_page_message,list.PageID)'><i class="ficon-cross"></i></a>
                  </div>   
                  <div class="list-inner">
                    <figure>
                      <a entitytype="page" entityguid="{{list.PageGUID}}" class="loadbusinesscard" href="{{BaseUrl}}page/{{list.PageURL}}">
                        <img ng-src="{{ImageServerPath+list.PageIcon}}" class="img-circle"  >
                      </a>
                    </figure>
                    <div class="list-item-body">
                      <h4 class="list-heading-xs"><a entitytype="page" entityguid="{{list.PageGUID}}" class="loadbusinesscard" href="{{BaseUrl}}page/{{list.PageURL}}" ng-bind="list.Title"></a></h4>                      
                      <ul class="list-activites block">
                        <li>
                            <span class="text-off">By</span> <a entitytype="user" entityguid="{{list.UserGUID}}" class="loadbusinesscard"  href="{{BaseUrl+list.CreatedByURL}}" ng-bind="list.CreatedByFirstName+' '+list.CreatedByLastName"></a>
                        </li>
                        <li>
                            <span class="icon">
                                <i class="ficon-friends"></i>&nbsp;
                            </span>
                            <span class="text" ng-cloak ng-if="list.NoOfFollowers==1">1 Follower</span>
                            <span class="text" ng-cloak ng-if="list.NoOfFollowers>1" ng-bind="list.NoOfFollowers+' Follwers'"></span>
                        </li>
                        <li ng-cloak>
                            <span class="text-off">{{lang.last_activity}} :</span> {{dateFormat(list.LastActivity)}}
                        </li>
                      </ul>                                      
                      <p>{{list.Description|limitTo:DescriptionLimit}} <span ng-if="list.Description.length > DescriptionLimit"> ...</span></p>                                   
                    </div>
                  </div>                          
                </div>
              </li>
            </ul>
          </div>
          <div class="panel-footer text-left" ng-show="hasMoreItemsToShow('MyPage')">
            <a class="loadmore" ng-click="showMoreItems('MyPage')">
              <span class="text">Load more</span>
              <span class="icon">
                <i class="ficon-arrow-create"></i>
              </span>
              <span class="loader" ng-cloak ng-if="showMyPageLoader"></span>
            </a>
          </div>
          <!-- New -->
        </div>

        <div  class="panel panel-info" ng-init="myFollowPages('LastActionDate', 'DESC')" ng-show="pageFollowListsLen>0" ng-cloak>
          <div ng-cloak class="panel-heading">
            <h3 class="panel-title" ng-bind="lang.page_follow"></h3>
          </div>
          <!-- New My Follow Page -->
          <div class="panel-body">
            <ul class="list-items-group">
              <li class="items" id="page_{{list.PageGUID}}" ng-repeat="list in listObj = pageFollowLists | limitTo: paginationLimit('JoinedPage')" repeat-done="repeatDoneBCard()" ng-hide="list.length>0">
                <div class="list-items-sm">
                  <div class="actions right">
                    <a ng-click='removePageFromList(list.PageGUID,"Follow",lang.unfollow_page,lang.unfollow_page_message,list.PageID)'><i class="ficon-cross"></i></a>
                  </div>   
                  <div class="list-inner">
                    <figure>
                      <a entitytype="page" entityguid="{{list.PageGUID}}" class="loadbusinesscard" href="{{BaseUrl}}page/{{list.PageURL}}">
                        <img ng-src="{{ImageServerPath+list.PageIcon}}" class="img-circle"  >
                      </a>
                    </figure>
                    <div class="list-item-body">
                      <h4 class="list-heading-xs"><a entitytype="page" entityguid="{{list.PageGUID}}" class="loadbusinesscard" href="{{BaseUrl}}page/{{list.PageURL}}" ng-bind="list.Title"></a></h4>                      
                      <ul class="list-activites block">
                        <li>
                            <span class="text-off">By</span> <a entitytype="user" entityguid="{{list.UserGUID}}" class="loadbusinesscard"  href="{{BaseUrl+list.CreatedByURL}}" ng-bind="list.CreatedByFirstName+' '+list.CreatedByLastName"></a>
                        </li>
                        <li>
                            <span class="icon">
                                <i class="ficon-friends"></i>&nbsp;
                            </span>
                            <span class="text" ng-cloak ng-if="list.NoOfFollowers==1">1 Follower</span>
                            <span class="text" ng-cloak ng-if="list.NoOfFollowers>1" ng-bind="list.NoOfFollowers+' Follwers'"></span>
                        </li>
                        <li ng-cloak>
                            <span class="text-off">{{lang.last_activity}} :</span> {{dateFormat(list.LastActivity)}}
                        </li>
                      </ul>                                      
                      <p>{{list.Description|limitTo:DescriptionLimit}} <span ng-if="list.Description.length > DescriptionLimit"> ...</span></p>                                   
                    </div>
                  </div>                          
                </div>
              </li>
            </ul>
          </div>
          <div class="panel-footer text-left" ng-show="hasMoreItemsToShow('JoinedPage')">
            <a class="loadmore" ng-click="showMoreItems('JoinedPage')">
              <span class="text">Load more</span>
              <span class="icon">
                <i class="ficon-arrow-create"></i>
              </span>
              <span class="loader" ng-cloak ng-if="myFollowPagesLoader"></span>
            </a>
          </div>
          <!-- New My Follow Page -->
        </div>

        <div class="panel panel-info" ng-cloak ng-if="pageListsLen=='0' && pageFollowListsLen=='0'">
          <div class="panel-body nodata-panel">
            <div class="nodata-text">
              <span class="nodata-media">
                  <img ng-src="{{AssetBaseUrl}}img/empty-img/empty-no-pages-created.png" >
              </span>
              <h5>{{lang.no_pages_heading}}</h5>
              <p class="text-off">{{lang.no_pages_message}}</p>
              <a ng-href="{{BaseUrl+'pages/types'}}">Create Page</a>
            </div>
          </div>
        </div>
      </div>
    </aside>
    <!-- //Left Wall-->
    
    <!-- Right Wall-->
    <aside class="col-md-3 col-sm-4 sidebar" ng-init="PageSuggestion(5,'0',0);">
      <?php $this->load->view('pages/create_page_html'); ?>
      <div class="panel panel-widget" ng-cloak ng-show="SuggestionObj.length>0">
          <div class="panel-heading">
              <h3 class="panel-title">
                <span class="text" ng-bind="lang.suggested_pages"></span>
              </h3>        
          </div>
          <div class="panel-body no-padding">
              <ul class="list-items-hovered list-items-borderd">
                <li id="suggestion_{{suggestion.PageID}}"  ng-repeat="suggestion in SuggestionObj = pageSuggestions | limitTo: 5">
                  <div class="list-items-xmd">
                    <div class="actions right">
                      <a ng-click='hideSuggestedPage(suggestion.PageGUID)'><i class="ficon-cross"></i></a>
                    </div>   
                    <div class="list-inner">
                      <figure>
                        <a entitytype="page" entityguid="{{suggestion.PageGUID}}" class="loadbusinesscard" href="page/{{suggestion.PageURL}}">
                          <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+suggestion.ProfilePicture}}" class="img-circle"  >
                        </a>
                      </figure>
                      <div class="list-item-body">
                        <h4 class="list-heading-xs ellipsis"><a ng-bind="suggestion.Title"></a></h4>
                        <div ng-cloak ng-if="suggestion.NoOfFollowers>0">
                          <small ng-cloak ng-if="suggestion.NoOfFollowers==1">1 Follower</small>
                          <small ng-cloak ng-if="suggestion.NoOfFollowers>1" ng-bind="suggestion.NoOfFollowers+' Followers'"></small>
                        </div>
                        <div class="btn-toolbar">
                          <a class="btn btn-xs btn-default" ng-click='toggleFollow(suggestion.PageID,"UserList",suggestion.PageGUID);'><span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text">Follow</span></a>
                        </div>
                      </div>
                    </div>                          
                  </div>
                </li>
              </ul>
          </div>
      </div>
    </aside>
    <!-- //Right Wall-->
  </div>
</div>
<!--//Container-->