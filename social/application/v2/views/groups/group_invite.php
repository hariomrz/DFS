<div ng-cloak ng-init="getFriendsForGroup();" ng-if="tr==0 && GroupDetails.IsAdmin">
	<div ng-cloak class="m-t-15" ng-if="TotalMembers==1 && totalGroupRecords>0">
		<div class="panel panel-default">
		    <div class="block-group">
		        <!-- <div class="panel-header invite-panel-header">
		          <span class="grey-text">There are no post in the group. Start writing now....</span>
		        </div> -->
		        <div class="panel-block members-block invite-block">
		            <div class="panel-heading">
		              <div class="row">
		                    <div class="col-sm-6 col-xs-12">
		                        <h3 class="panel-title">INVITE CONNECTIONS TO JOIN </h3>
		                    </div>
		                    <div class="col-sm-6 col-xs-12">
		                        <div class="search-cmn member-search">
		                            <button class="search-contentinput visible-xs btn btn-default btn-sm" type="button"><span class="icon"><i class="ficon-search"></i></span></button>
		                            <div class="filters hidden-xs">
		                              <div class="filters-search">
		                                <div class="input-group global-search">
		                                  <input type="text" class="form-control" placeholder="Quick Search" ng-model="searchMember2" name="srch-filters" ng-keyup="searchFilter2();" id="srch-filters2">
		                                  <div class="input-group-btn">
		                                    <button ng-click="removeGroupSearch();" class="btn-search quick-search" type="button"><i id="searchfilterbtn" class="ficon-search"></i></button>
		                                  </div>
		                                </div>
		                              </div>
		                            </div>
		                        </div>
		                    </div>
		                </div>
		            </div>
		            <div class="panel-body">
		                <div class="row">
		                    <div class="col-lg-12">
		                        <ul class="list-group invite-listin">
		                          <li class="col-sm-6" ng-repeat="groupinvite in inviteGroupFriend">
		                            <div class="checkbox check-primary custom-check">
		                                <input type="checkbox" id="till-date{{$index}}" class="add-delete-checkbox" ng-click="AddDeleteUserCheckbox()" ng-value="groupinvite.UserGUID">
		                                <label for="till-date{{$index}}"></label>
		                            </div>
		                            <figure>
		                            <a>
		                            <img ng-if="groupinvite.ProfilePicture!==''" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{groupinvite.ProfilePicture}}" class="img-circle"  >
		                            <img ng-if="groupinvite.ProfilePicture==''" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" class="img-circle"  >
		                            </a>
		                            </figure>
		                            <div class="description">
		                              <a target="_self" class="name" ng-href="<?php site_url() ?>{{Url}}" ng-bind="groupinvite.FirstName+' '+groupinvite.LastName"></a>
		                              <span class="location" ng-if="groupinvite.City!==null && groupinvite.Country!==null" ng-bind="groupinvite.City+', '+groupinvite.Country"></span>
		                            </div>
		                          </li> 
		                        </ul>
		                      </div>
		                </div>
		                <div class="panel-bottom p-b-0">
		                  <button ng-show="inviteGroupFriend.length<totalGroupRecords && inviteGroupFriend.length>0 && inviteGroupFriend.length==8" class="btn  btn-link" type="button" ng-click="groupLoadMore();">Load More <span><i class="caret"></i></span></button>
		                </div>
		            </div> 
		        </div>
		        <div class="panel-footer custom-panel-footer ">
		          <button type="button" class="btn btn-primary custom-btn-right" ng-click="SubmitInviteGroupFriend()">INVITE</button>
		            <span class="grey-text text"><span ng-bind="totalSelected"></span> people selected</span>
		        </div>
		  	</div>
	  	</div>
	</div>
</div>
