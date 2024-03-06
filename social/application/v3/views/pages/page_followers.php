<div data-ng-controller="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')" ng-cloak>
  <div ng-init="GetPageDetails('<?php echo $PageGUID;?>');GetPageFollower('<?php echo $PageGUID;?>');GetPageAdmins('<?php echo $PageGUID;?>')"> 
    <!--Header-->
    <?php $this->load->view('profile/profile_banner'); ?>
    <!--//Header--> 
    <!--Container-->
    <div class="container wrapper">
      <div class="row"> 
        <!-- Left Wall-->
        <aside class="col-md-9 col-sm-8">
            <div class="page-heading">
                  <div class="row">
                      <div class="col-sm-5">
                          <h4 class="page-title">Followers</h4>
                      </div>
                      <div class="col-sm-7">
                          <div class="page-actions">
                              <ul class="list-page">                                            
                                  <li class="items">
                                      <div class="input-search form-control right">
                                          <input type="text" ng-keyup="GetPageAdmins('<?php echo $PageGUID;?>');GetPageFollower('<?php echo $PageGUID;?>',1); changeSearchIcon();" ng-model="search.FollowerSearch" id="srch-filters" name="srch-filters" placeholder="Quick search" class="form-control">
                                          <div class="input-group-btn">
                                            <button class="btn search_followers" ng-click="ResetFollowerSearch('<?php echo $PageGUID;?>');">
                                              <i ng-click="ResetFollowerSearch('<?php echo $PageGUID;?>');" class="ficon-search" ng-if="search.FollowerSearch==''"></i>
                                              <i ng-click="ResetFollowerSearch('<?php echo $PageGUID;?>');" class="ficon-cross" ng-if="search.FollowerSearch!=''"></i>
                                            </button>
                                          </div>
                                      </div>
                                  </li>
                              </ul>
                          </div>
                      </div>
                  </div>
              </div>
          
            <div class="panel-group">
              <div ng-if="PageCreatorsLen>0" class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title">
                    <span ng-cloak ng-if="PageCreatorsLen>1"><?php echo lang('page_managers');?></span><span ng-cloak ng-if="PageCreatorsLen==1"><?php echo lang('page_manager');?></span> <span ng-cloak ng-if="PageCreatorsLen>0">({{PageCreatorsLen}})</span>
                  </h3>
                </div>
                <!-- New -->
                <div class="panel-body">
                  <ul class="list-items-group list-group-inline list-items-column row">
                    <li class="items col-sm-6" id="usr{{list.UserGUID}}"  ng-repeat="list in listObj = PageCreators" ng-hide="list.length>0" repeat-done="repeatDoneBCard()">
                      <div class="list-items-sm"> 
                        <div class="list-inner">
                          <figure> 
                            <a entitytype="user" entityguid="{{list.UserGUID}}" class="loadbusinesscard" href="{{'<?php echo site_url() ?>'+list.ProfileLink}}"> 
                              <img   ng-if="list.ProfilePicture !='' " class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+list.ProfilePicture}}" /> 
                              <span ng-if="list.ProfilePicture=='' || list.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(list.FirstName+' '+list.LastName)"></span></span>
                            </a> 
                          </figure>
                          <div class="list-item-body">
                            <h4 class="list-heading-xs">
                              <a entitytype="user" entityguid="{{list.UserGUID}}" href="{{'<?php echo site_url() ?>'+list.ProfileLink}}" class="name loadbusinesscard" ng-bind="list.FirstName+' '+list.LastName"></a> 
                            </h4>
                            
                            <div ng-if="list.ModuleRoleID==7" ng-cloak>
                              <a class="small text-off">
                                <span class="text">Creator</span>
                              </a>
                            </div>

                            <div class="dropdown" ng-if="list.ModuleRoleID==8">                  
                              <a class="small text-off" data-toggle="dropdown">
                                <span class="text">Admin</span> 
                                <span class="icon"><i class="ficon-arrow-down f-lg"></i></span>
                              </a>
                              <ul role="menu" class="dropdown-menu pull-left">
                                <li>
                                  <a ng-click='addRemoveRole(list.PageGUID,list.UserGUID,"Remove","9",$index)'>
                                    <?php echo lang('page_remove_admin_rights'); ?>
                                  </a>
                                </li>
                                <li>
                                  <a ng-click='removeFromPage(list.PageGUID,"Admin",list.UserID,$index)' >
                                    <?php echo lang('page_remove_from'); ?>
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </div>
                        </div>                          
                      </div>
                    </li>
                  </ul>
                </div>
                <!-- New -->
              </div>
              <div class="panel panel-info" ng-cloak ng-if="PageUsersLen>0">
                <div class="panel-heading">
                  <h3 class="panel-title">
                    <span ng-cloak ng-if="PageUsersLen>1"><?php echo lang('page_followers');?></span>
                    <span ng-cloak ng-if="PageUsersLen<=1"><?php echo lang('page_follower');?></span>
                    <span ng-cloak ng-if="PageUsersLen>0">({{PageUsersLen}})</span>
                  </h3>
                </div>
                <!-- New -->
                <div class="panel-body">
                  <ul class="list-items-group list-group-inline list-items-column row">
                    <li class="items col-sm-6" id="usr{{list.UserGUID}}"  ng-repeat="list in listObj = PageUsers| limitTo: paginationLimitMembers()" ng-hide="list.length>0" repeat-done="repeatDoneBCard()">
                      <div class="list-items-sm"> 
                        <div class="list-inner">
                          <figure> 
                            <a entitytype="user" entityguid="{{list.UserGUID}}" class="loadbusinesscard" href="{{'<?php echo site_url() ?>'+list.ProfileLink}}"> 
                              <img   ng-if="list.ProfilePicture !='' " class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+list.ProfilePicture}}" /> 
                              <span ng-if="list.ProfilePicture=='' || list.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(list.FirstName+' '+list.LastName)"></span></span>
                            </a> 
                          </figure>
                          <div class="list-item-body">
                            <h4 class="list-heading-xs">
                              <a entitytype="user" entityguid="{{list.UserGUID}}" href="{{'<?php echo site_url() ?>'+list.ProfileLink}}" class="name loadbusinesscard" ng-bind="list.FirstName+' '+list.LastName"></a> 
                              <i ng-cloak ng-if="list.CanPostOnWall==0" class="ficon ficon-noedit"></i>
                            </h4>

                            <div class="dropdown">                  
                              <a class="small text-off" data-toggle="dropdown">
                                <span class="text">Follower</span> 
                                <span class="icon"><i class="ficon-arrow-down f-lg"></i></span>
                              </a>
                              <ul role="menu" class="dropdown-menu pull-left">
                                <li> <a ng-if='list.CanPostOnWall==1' ng-click='addRemoveCanPost(list.PageGUID,list.UserGUID,"0", $index)'><?php echo lang('page_can_not_add_post'); ?></a> <a ng-if='list.CanPostOnWall==0' ng-click='addRemoveCanPost(list.PageGUID,list.UserGUID,"1",$index)' ><?php echo lang('page_can_add_post'); ?></a> </li>
                                <li><a ng-click='addRemoveRole(list.PageGUID,list.UserGUID,"Add","8",$index)' ><?php echo lang('page_make_admin'); ?></a></li>
                                <li><a ng-click='removeFromPage(list.PageGUID,"Follower",list.UserID,$index)'><?php echo lang('page_remove_from'); ?></a></li>
                              </ul>
                            </div>
                          </div>
                        </div>                          
                      </div>
                    </li>
                    <li ng-if='PageUsersLen==0' class="nolistFound">
                      <img ng-src="<?php echo ASSET_BASE_URL ?>img/page-default.jpg"   class="img-circle">
                      <p class="m-t-15" ng-if='FollowerSearch!="" && PageUsersLen==0'><?php echo lang('no_record'); ?></p>
                      <p class="m-t-15" ng-if='FollowerSearch=="" && PageUsersLen==0'><?php echo lang('no_page_following'); ?></p>
                    </li>
                  </ul>
                </div>
                <div class="panel-footer text-left" ng-show="PageUsers.length<PageUsersLen">
                    <a class="loadmore" ng-click="GetPageFollower('<?php echo $PageGUID;?>');">
                      <span class="text">Load more</span>
                      <span class="icon">
                        <i class="ficon-arrow-create"></i>
                      </span>
                      <span class="loader" ng-cloak ng-if="showFollowLoader">&nbsp;</span>
                    </a>
                </div>
                <!-- New -->
              </div>
              <div ng-if="PageCreatorsLen==0 && PageUsersLen==0 && search.FollowerSearch!=''" class="panel panel-info">
                <div class="panel-body nodata-panel">
                  <div class="nodata-text">
                    <span class="nodata-media">
                        <img ng-src="{{AssetBaseUrl}}img/empty-img/empty-no-search-results-found.png" >
                    </span>
                    <h5>No Results Found!</h5>
                    <p class="text-off">
                      You will find all the file attachments you shared,
                      <br>
                      or shared with you here.
                    </p>
                  </div>
                </div>
              </div>
            
            </div>
        </aside>
        <!-- //Left Wall--> 
        
        <!-- Right Wall-->
        <aside class="col-md-3 col-sm-4 sidebar">
          
          <?php $this->load->view('pages/about_page'); ?>
          <?php $this->load->view('pages/create_page_html'); ?>
        </aside>
        <!-- //Right Wall--> 
      </div>
    </div>
    <!--//Container--> 
  </div>
</div>
<input type="hidden" name="Visibility" id="visible_for" value="1" />
<input type="hidden" name="Commentable" id="comments_settings" value="1" />
<input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
