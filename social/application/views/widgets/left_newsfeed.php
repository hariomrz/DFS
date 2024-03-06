<div class="panel-group">
<span ng-init="get_widgets();"></span>
<?php if(!isset($_GET['files']) && !isset($_GET['links'])) { ?>
    <div class="panel panel-transparent" ng-class="(postEditormode) ? 'white-bg-rc' : '' ;" ng-init="getRecentCoversation()" ng-show="IsReminder==0">
        <div class="panel-heading">      
          <h3 class="panel-title text-sm"> 
            <span class="text" ng-bind="lang.w_conversation"></span>
          </h3>
        </div>    
        <div class="panel-body transparent">
            <div class="form-group">
                <div class="input-search right quick-search">
                  <input ng-keyup="getRecentCoversation();" ng-model="rcsearch" id="rcsearch" type="text" class="form-control" placeholder="Quick search">
                  <div class="input-group-btn">
                    <button class="btn">
                        <i class="ficon-search" ng-if="!showCrossBtn"></i>
                        <i ng-if="showCrossBtn" ng-click="clearSearchConversation()" class="ficon-cross"></i>
                    </button>
                  </div>
                </div>
            </div>
            <div ng-cloak ng-if="recentConversations.length == 0 && recent_count>0" class="nodata-panel nodata-default p-v">
                <div class="nodata-text">
                    <p class="no-margin" ng-bind="lang.w_no_conversation_yet"></p>
                    <a target="_self" class="text-primary semi-bold" href="<?php echo site_url('network/grow_your_network') ?>" ng-bind="lang.w_grow_your_network"></a>
                </div>
            </div>
            <ul class="listing-group">
                <li ng-if="postEditormode" ng-repeat="rconv in recentConversations" repeat-done="triggerTooltip()" class=" draggable list-items-xs" draggable="rconv" draggable-target='.sortable'>                
                    <div class="list-inner">
                        
                        <figure>
                            <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}">
                                <img ng-if="rconv.ProfilePicture !== '' && (rconv.ProfilePicture !== 'group-no-img.jpg' || rconv.Type == 'FORMAL')" ng-src="{{ImageServerPath+'upload/profile/220x220/'+rconv.ProfilePicture}}" class="img-circle"   err-src="{{defaultGroupProfilePic}}" />
                                
                                <span ng-if="rconv.ProfilePicture=='' || rconv.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(rconv.FirstName+' '+rconv.LastName)"></span></span>

                                <div ng-if="rconv.Type == 'INFORMAL' && rconv.ProfilePicture == 'group-no-img.jpg'" ng-class="( rconv.Members.length > 2 ) ? 'group-thumb' : 'group-thumb-two' ;" class="m-user-thmb group-thumb">
                                    <span ng-repeat="r in rconv.Members">
                                        <img  ng-src="{{ImageServerPath+'upload/profile/220x220/'+r.ProfilePicture}}" entitytype="user" ng-if="$index <= 2" err-src="{{defaultGroupProfilePic}}">
                                    </span>                                          
                                </div>
                            </a>
                        </figure>
                        
                        <div class="list-item-body">
                            <h4 class="list-heading-xs"> 
                                <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}" class="ellipsis conv-name "  target="_self">
                                    <span class="loadbusinesscard" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" ng-bind="rconv.FirstName+' '+rconv.LastName+''"></span>
                                    <i data-toggle="tooltip" data-original-title="Public" class="ficon-globe" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==1"></i>
                                    <i data-toggle="tooltip" data-original-title="Secret" class="ficon-secrets f-lg" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==2"></i>
                                    <i data-toggle="tooltip" data-original-title="Close" class="ficon-close" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==0"></i>
                                </a>
                            
                            </h4>
                        </div> 
                    </div>
                </li>
                <li ng-if="!postEditormode" ng-repeat="rconv in recentConversations" repeat-done="triggerTooltip()" class="list-items-xs">                
                    <div class="list-inner">
                        
                        <figure>
                            <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}">
                                <img ng-if="rconv.ProfilePicture !== 'user_default.jpg' && rconv.ProfilePicture !== '' && (rconv.ProfilePicture !== 'group-no-img.jpg' || rconv.Type == 'FORMAL')" ng-src="{{ImageServerPath+'upload/profile/220x220/'+rconv.ProfilePicture}}" class="img-circle"   err-src="{{defaultGroupProfilePic}}" />
                                <img ng-if="rconv.ProfilePicture=='' || rconv.ProfilePicture=='user_default.jpg'" src="" err-name="{{rconv.FirstName+' '+rconv.LastName}}" class="img-circle"   />

                                <div ng-if="rconv.Type == 'INFORMAL' && rconv.ProfilePicture == 'group-no-img.jpg'" ng-class="( rconv.Members.length > 2 ) ? 'group-thumb' : 'group-thumb-two' ;" class="m-user-thmb group-thumb">
                                    <span ng-repeat="r in rconv.Members">
                                        <img  ng-src="{{ImageServerPath+'upload/profile/220x220/'+r.ProfilePicture}}" entitytype="user" ng-if="$index <= 2" err-src="{{defaultGroupProfilePic}}">
                                    </span>                                          
                                </div>
                             </a>
                        </figure>
                        
                        <div class="list-item-body">
                            
                            <h4 class="list-heading-xs"> 
                                <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="ellipsis conv-name"  target="_self">
                                    
                                    <span entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="loadbusinesscard" ng-bind="rconv.FirstName+' '+rconv.LastName+''"></span>
                                    <i data-toggle="tooltip" data-original-title="Public" class="ficon-globe" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==1"></i>
                                    <i data-toggle="tooltip" data-original-title="Secret" class="ficon-secrets f-lg" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==2"></i>
                                    <i data-toggle="tooltip" data-original-title="Close" class="ficon-close" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==0"></i>
                                </a>
                            </h4>
                        </div> 
                    </div>
                </li>
            </ul>
        </div>

    </div>
    <?php } ?>

    <?php if(!$this->settings_model->isDisabled(1)) : // Check if group module is enabled  ?>
    <div ng-hide="IsMyDeskTab" ng-cloak class="panel panel-transparent" id="FormCtrl">   
        <div class="panel-heading">
            <h3 class="panel-title text-sm"> 
                <a target="_self" ng-click="loadCreateGroup();createGroup();" class="btn btn-default btn-xs pull-right"><span class="icon" data-toggle="tooltip" data-original-title="Add New Group"><i class="ficon-plus f-md"></i></span></a>
                <span class="text" ng-bind="lang.w_my_groups"></span>
            </h3>              
        </div>  
        <div class="panel-body transparent">
            <div class="nodata-panel nodata-default" ng-cloak ng-if="TopGroup.length==0 && widget_call>0">
                <div class="nodata-text p-h-sm">
                    <span class="nodat-circle sm shadow">
                        <i class="ficon-smiley"></i>
                    </span>
                    <p class="no-margin">{{::lang.w_not_member_of_any_group}} <a target="_self" class="text-primary semi-bold" href="<?php echo site_url('group') ?>" ng-bind="lang.w_browse_groups"></a> {{::lang.w_might_interest_you}} <a target="_self" class="text-primary semi-bold" ng-click="loadCreateGroup();createGroup();" ng-bind="lang.w_create_group"></a>.</p>
                </div>
            </div>
            <ul class="listing-group">
                <li class="list-items-xs" id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = TopGroup|limitTo:3">                    
                    <div class="list-inner">
                        <figure ng-if="list.Type=='FORMAL'"> 
                            <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                            </a>
                        </figure>
                          
                        <figure ng-if="list.Type == 'INFORMAL' && list.ProfilePicture == 'group-no-img.jpg'" ng-class="( list.EntityMembers.length > 2 ) ? 'group-thumb' : 'group-thumb-two' ;" class="m-user-thmb group-thumb">
                            <span ng-repeat="r in list.EntityMembers">
                                <img  ng-src="{{ImageServerPath+'upload/profile/220x220/'+r.ProfilePicture}}" entitytype="user" ng-if="$index <= 2" err-src="{{defaultGroupProfilePic}}">
                            </span> 
                        </figure>
                          
                        <div class="list-item-body">
                            <h4 class="list-heading-xs">
                                <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="ellipsis conv-name loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                                <span entitytype="group" entityguid="{{list.GroupGUID}}" class="ellipsis conv-name loadbusinesscard" ng-bind="list.GroupName+''"></span>
                                <i data-toggle="tooltip" data-original-title="Public" class="ficon-globe" ng-if="list.IsPublic!=='' && list.IsPublic==1"></i>
                                <i data-toggle="tooltip" data-original-title="Secret" class="ficon-secrets f-lg" ng-if="list.IsPublic!=='' && list.IsPublic==2"></i>
                                <i data-toggle="tooltip" data-original-title="Close" class="ficon-close" ng-if="list.IsPublic!=='' && list.IsPublic==0"></i>
                                </a>
                            </h4>                        
                        </div>
                    </div>
                </li>
            </ul>        
        </div>
    </div>
    <?php endif; ?>


    <?php 
    if(!$this->settings_model->isDisabled(18)) : // Check if page module is enabled        
    ?>
    <div ng-hide="IsMyDeskTab" ng-cloak class="panel panel-transparent">
        <div class="panel-heading">
            <h3 class="panel-title text-sm"> 
                <a target="_self" ng-href="<?php echo site_url('pages/types') ?>" class="btn btn-default btn-xs pull-right"><span class="icon" data-toggle="tooltip" data-original-title="Add New Page"><i class="ficon-plus f-md"></i></span></a>
                <span class="text" ng-bind="lang.w_my_pages"></span>
            </h3>              
        </div> 
        <div class="panel-body transparent">
            <div class="nodata-panel nodata-default" ng-cloak ng-if="top_user_pages.length==0 && widget_call>0">
                <div class="nodata-text p-h-sm">
                    <span class="nodat-circle sm shadow">
                        <i class="ficon-smiley"></i>
                    </span>
                    <p class="no-margin">{{::lang.w_not_following_any_page}} <a target="_self" class="text-primary semi-bold" href="<?php echo site_url('pages') ?>" ng-bind="lang.w_browse_pages"></a> {{::lang.w_might_interest_you}} <a target="_self" class="text-primary semi-bold" href="<?php echo site_url('pages/types') ?>" ng-bind="lang.w_create_page"></a>.</p>
                </div>
            </div>
            <ul class="listing-group">
                <li ng-repeat="user_pages in top_user_pages|limitTo:3" class="list-items-xs">                    
                    <div class="list-inner">
                        <figure>
                            <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard">
                                <img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{user_pages.ProfilePicture}}" class="img-circle"   />
                            </a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs">
                                <a target="_self" entitytype="page" entityguid="{{user_pages.PageGUID}}" class="loadbusinesscard ellipsis" ng-href="<?php echo site_url('page') ?>/{{user_pages.PageURL}}" ng-bind="user_pages.Title"></a>
                            </h4>
                            <div ng-if="LoginSessionKey!=='' && user_pages.FollowStatus=='0'" ng-click="toggleFollowPage(user_pages.PageID)" class="button-wrap-sm">
                                <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                            </div>
                            <div ng-if="LoginSessionKey=='' && user_pages.FollowStatus=='0'" ng-click="likeEmit('', 'ACTIVITY', '');" class="button-wrap-sm">
                                <button class="btn btn-default btn-xs" ng-bind="lang.w_follow_f_caps"></button>
                            </div>
                        </div>
                    </div>
                </li> 
            </ul>
        </div>        
    </div>

    <?php endif; ?>

</div>