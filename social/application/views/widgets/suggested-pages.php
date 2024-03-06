<div ng-cloak class="panel panel-widget" ng-show="pageSuggestions.length>0 && !IsMyDeskTab">
    <div class="panel-heading">
        <h3 class="panel-title">
            <a target="_self" class="link" href="<?php echo site_url('pages/types') ?>" ng-bind="lang.create"></a>
            <span class="text" ng-bind="lang.w_suggested_pages_caps"></span> 
        </h3>        
    </div>
    <div class="panel-body no-padding">
        <div class="nodata-panel nodata-default" ng-cloak ng-if="pageSuggestions.length==0">
            <div class="nodata-text">
                <span class="nodat-circle sm shadow">
                    <i class="ficon-smiley"></i>
                </span>
                <p class="no-margin">{{::lang.w_no_pages_suggestions}} <br>{{::lang.w_for_you}}</p>
            </div>
        </div>
        <ul class="list-items-hovered list-items-borderd">
            <li ng-repeat="suggestion in SuggestionObj = pageSuggestions | limitTo: 3">
                <div class="list-items-xmd">
                    <div class="actions">
                            <button class="btn btn-default follow-btn" ng-click='toggleFollow(suggestion.PageID,"UserList",suggestion.PageGUID);'><i class="ficon-plus"></i> Follow</button>
                    </div>
                    <div class="list-inner">
                        <figure>
                            <a target="_self" entitytype="page" entityguid="{{suggestion.PageGUID}}" class="loadbusinesscard" href="page/{{suggestion.PageURL}}"><img ng-src="<?php echo IMAGE_SERVER_PATH;?>{{::suggestion.PageIcon}}" class="img-circle"  ></a>
                        </figure>
                        <div class="list-item-body">
                            <h4 class="list-heading-xs"><a target="_self" entitytype="page" entityguid="{{suggestion.PageGUID}}" class="ellipsis conv-name loadbusinesscard" href="<?php echo base_url();?>page/{{suggestion.PageURL}}">{{::suggestion.Title}} </a></h4>
                            <div>
                                <small class="location" ng-if='suggestion.NoOfFollowers == 1'>{{::suggestion.NoOfFollowers+' Follower'}}</small>
                                <small class="location" ng-if='suggestion.NoOfFollowers > 1'>{{::suggestion.NoOfFollowers+' Followers'}}</small>
                            </div>
                        </div>                        
                    </div>
                </div>
            </li> 
        </ul>
    </div> 
</div>