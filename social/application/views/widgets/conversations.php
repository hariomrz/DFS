<div class="panel panel-transparent" ng-init="getRecentCoversation()" ng-show="IsReminder == 0">  
    <div class="panel-heading">
        <h3 class="panel-title text-sm" ng-bind="lang.w_conversation"></h3>
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


        <div ng-if="recentConversations.length == 0" class="blank-view">
            <p><a target="_self" href="<?php echo site_url('network/grow_your_network') ?>" ng-bind="lang.w_grow_your_network"></a> {{::lang.w_to_start_conversation}}</p>
        </div>

        <ul class="listing-group">
            <li ng-if="postEditormode" ng-repeat="rconv in recentConversations" repeat-done="triggerTooltip()" class="list-items-xs draggable" draggable="rconv" draggable-target='.sortable'>
                <div class="list-inner">
                    <figure>
                        <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}">
                        <img ng-if="rconv.ProfilePicture !== '' && (rconv.ProfilePicture !== 'group-no-img.jpg' || rconv.Type == 'FORMAL')" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + rconv.ProfilePicture}}" class="img-circle"   err-src="{{defaultGroupProfilePic}}" />

                        <span ng-if="rconv.ProfilePicture == '' || rconv.ProfilePicture == 'user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(rconv.FirstName + ' ' + rconv.LastName)"></span></span>

                        <div ng-if="rconv.Type == 'INFORMAL' && rconv.ProfilePicture == 'group-no-img.jpg'" ng-class="(rconv.Members.length > 2) ? 'group-thumb' : 'group-thumb-two';" class="m-user-thmb group-thumb">
                            <span ng-repeat="r in rconv.Members">
                                <img  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + r.ProfilePicture}}" entitytype="user" ng-if="$index <= 2" err-src="{{defaultGroupProfilePic}}">
                            </span>                                          
                        </div>
                        </a>
                    </figure>
                    <div class="list-item-body">
                        <h4 class="list-heading-xs"> 
                            <a target="_self" ng-href="{{SiteURL+rconv.ProfileURL}}" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="ellipsis conv-name loadbusinesscard" ng-bind="rconv.FirstName + ' ' + rconv.LastName + ''" target="_self"></a>
                            <i class="ficon-globe" ng-if="rconv.IsPublic !== '' && rconv.IsPublic == 1"></i>
                            <i class="ficon-secrets" ng-if="rconv.IsPublic !== '' && rconv.IsPublic == 2"></i>
                            <i class="ficon-close" ng-if="rconv.IsPublic !== '' && rconv.IsPublic == 0"></i>
                        </h4>
                    </div> 
                </div>
            </li>
            <li ng-if="!postEditormode" ng-repeat="rconv in recentConversations" repeat-done="triggerTooltip()" class="list-items-xs">                
                <div class="list-inner">
                    <figure>
                        <img ng-if="rconv.ProfilePicture !== '' && (rconv.ProfilePicture !== 'group-no-img.jpg' || rconv.Type == 'FORMAL')" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + rconv.ProfilePicture}}" class="img-circle"   err-src="{{defaultGroupProfilePic}}" />

                        <span ng-if="rconv.ProfilePicture == '' || rconv.ProfilePicture == 'user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(rconv.FirstName + ' ' + rconv.LastName)"></span></span>

                        <div ng-if="rconv.Type == 'INFORMAL' && rconv.ProfilePicture == 'group-no-img.jpg'" ng-class="(rconv.Members.length > 2) ? 'group-thumb' : 'group-thumb-two';" class="m-user-thmb group-thumb">
                            <span ng-repeat="r in rconv.Members">
                                <img  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + r.ProfilePicture}}" entitytype="user" ng-if="$index <= 2" err-src="{{defaultGroupProfilePic}}">
                            </span>                                          
                        </div>
                    </figure>
                    <div class="list-item-body">
                        <h4 class="list-heading-xs"> 
                            <a target="_self" entitytype="{{rconv.EntityType}}" entityguid="{{rconv.ModuleEntityGUID}}" class="ellipsis conv-name loadbusinesscard" ng-bind="rconv.FirstName + ' ' + rconv.LastName + ''" target="_self"></a>
                            <i class="ficon-globe" ng-if="rconv.IsPublic !== '' && rconv.IsPublic == 1"></i>
                            <!--<i class="ficon-secrets" ng-if="rconv.IsPublic!=='' && rconv.IsPublic==2"></i>-->
                            <i class="ficon-close" ng-if="rconv.IsPublic !== '' && rconv.IsPublic == 0"></i>
                        </h4>
                    </div> 
                </div>
            </li>
        </ul>
    </div>

</div>