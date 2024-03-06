<?php $this->load->view('widgets/related_articles') ?>

<!-- Custom Email Popup Start -->
<div ng-include="custom_email_popup_tmplt"></div>
<!-- Custom Email Popup Ends -->

<!-- Interest Modal Start -->
<div class="modal fade" id="allInterest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title"> All interest</h4>
            </div>
            <div class="modal-body padd-l-r-0 non-footer">
                <div class="designer-scroll mCustomScrollbar">
                    <div class="p-h">
                        <ul class="profiles-listing int-listing">
                            <li ng-cloak class="col-sm-6 col-xs-6" ng-repeat="interest in userInterestPopup">
                                <div class="listing-content">
                                    <div class="listing-desc">
                                        <figure>
                                            <img err-src="{{AssetBaseUrl}}img/Interest-default.jpg" ng-src="{{ImageServerPath+'upload/category/220x220/'+interest.ImageName}}" >
                                        </figure>
                                        <a ng-bind="interest.Name"></a>
                                        <span class="location" ng-if="interest.Followers==1" ng-bind="interest.Followers+' Follower'"></span>
                                        <span class="location" ng-if="interest.Followers>1" ng-bind="interest.Followers+' Followers'"></span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Interest Modal Ends -->

<!-- Modal Start Votes Modal -->
<div ng-if="SettingsData.m30=='1'" ng-controller="PollCtrl" id="PollCtrl">
    <div role="dialog" class="modal fade" id="votesModal" aria-labelledby="myModalLabel" aria-hidden="false" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button data-dismiss="modal" class="close" type="button">
                        <span aria-hidden="true"><i class="icon-close"></i></span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
            <span ng-bind="totalVotes"></span> 
            <span data-ng-if="totalVotes==1">Person</span>
            <span data-ng-if="totalVotes>1">People</span> voted
        </h4>
                </div>
                <div class="modal-body padd-l-r-0 non-footer">
                    <div class="designer-scroll mCustomScrollbar">
                        <ul class="list-group awaitinglist list-group-horizontal scrollbox scrollbox-md-height" tabindex="0">
                            <li ng-repeat="list in VotesDetails track by $index" class="list-group-item ">
                                <figure class="media-left">
                                    <a ng-if="list.ModuleID == '3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}">
                                    <img class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/220x220/{{list.ProfilePicture}}">
                                </a>
                                    <a ng-if="list.ModuleID == '18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}">
                                    <img class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/220x220/{{list.ProfilePicture}}">
                                </a>
                                </figure>
                                <div class="description">
                                    <a ng-if="list.ModuleID == '3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                                    <a ng-if="list.ModuleID == '18'" entitytype="Page" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}" data-ng-bind="list.Name"></a>
                                    <p data-ng-if="list.ProfileTypeName != ''" data-ng-bind="list.ProfileTypeName"></p>
                                    <p data-ng-if="list.Location.Location != ''" class="gray-text" data-ng-bind="list.Location.Location"></p>
                                </div>
                            </li>
                            <li class="load-more" data-ng-show="IsVotesLoadMore == '1'"><i class="loading"></i></li>
                        </ul>
                        <div class="enscroll-track vertical-track">
                            <a href="" class="vertical-handle">
                                <div class="top"></div>
                                <div class="bottom"></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- //Votes Modal Ends -->

<!-- TotalParticipants -->

<div class="modal fade" id="totalParticipate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="ficon-cross"></i></span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span ng-if="IsVoted==1">VOTED</span>
                    <span ng-if="IsVoted!==1">PARTICIPANTS</span> (<span ng-bind="totalParticipate"></span>)
                </h4>
            </div>
            <div class="modal-body">
                <div class="global-scroll max-ht400 default-scroll scrollbar" when-scrolled="participateDetailsEmit(LastParticipateActivityGUID, LastParticipateEntityType);">
                    <ul class="listing-group suggest-list">
                        <li ng-repeat="ld in participateDetails" class="list-group-item">
                            <div class="list-items-sm">
                                <div class="list-inner">
                                    <figure>
                                        <a ng-href="{{SiteURL + ld.ProfileURL}}" class="loadbusinesscard" entitytype="user" entityguid="{{ld.UserGUID}}">
                                            <img  ng-if="ld.ProfilePicture !== ''"  class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{ld.ProfilePicture}}" />
                                            <span ng-if="ld.ProfilePicture == '' || ld.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(ld.FirstName+' '+ld.LastName)"></span></span>
                                        </a>
                                    </figure>
                                    <div class="list-item-body">
                                        <h4 class="list-heading-xs">
                                            <a class="loadbusinesscard" entitytype="user" entityguid="{{ld.UserGUID}}" ng-href="{{SiteURL + ld.ProfileURL}}" class="name" ng-bind="ld.FirstName + ' ' + ld.LastName"></a>
                                        </h4>
                                        <div>
                                            <small class="location" ng-if="ld.CityName !== '' && ld.CountryName !== ''" ng-bind="ld.CityName + ', ' + ld.CountryName"></small>
                                        </div> 
                                    </div>

                                    <div class="follow-btn"   ng-cloak ng-if="ld.ShowFriendsBtn == '1' && (ld.FriendStatus == '1' || ld.FriendStatus == '2')">
                                        <button class="btn btn-default follow-btn" data-toggle="dropdown">
                                            <i class="ficon-double-check"></i> <span ng-bind="(ld.FriendStatus=='1' ? 'Friends' : 'Request Sent')"></span> 
                                            <i class="ficon-arrow-down"></i> 
                                        </button>
                                        <ul ng-if="ld.ShowFriendsBtn == '1'" class="dropdown-menu dropdown-left" role="menu">
                                            <li ng-cloak ng-if="ld.ShowFriendsBtn == '1' && ld.FriendStatus == '1'" ng-click="rejectRequest(ld.UserGUID, 'likepopup')"><a>Unfriend</a></li>
                                            <li ng-cloak ng-if="ld.ShowFriendsBtn == '1' && ld.FriendStatus == '2' && ld.ShowFollowBtn"><a id="followlikepopup{{ld.UserGUID}}" ng-click="follow(ld.UserGUID, 'likepopup')" ng-bind="ld.follow">Unfollow</a></li>
                                            <li ng-cloak ng-if="ld.ShowFriendsBtn == '1' && ld.FriendStatus == '2'" ng-click="rejectRequest(ld.UserGUID, 'likepopup')"><a>Cancel request</a></li>
                                        </ul>
                                    </div>
                                    <button ng-cloak ng-if="ld.ModuleID == '18'" ng-click="toggleFollowPage(ld.UserGUID)" class="btn btn-default follow-btn"><i class="ficon-add-friend"></i> <span ng-bind="ld.follow"></span></button>
                                    <button ng-cloak ng-if="ld.ShowFriendsBtn == '1' && ld.FriendStatus == '4'" ng-click="sendRequest(ld.UserGUID, 'likepopup')" class="btn btn-default follow-btn"><i class="ficon-add-friend"></i> Add as Friend</button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="ParticipatePageNo" value="1" />
</div>
<!--// TotalParticipants -->

<!-- TotalLikes -->

<div ng-include="like_details_modal_tmplt"></div>

<input type="hidden" id="ActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : ''; ?>" />
<input type="hidden" id="CommentGUID" value="<?php echo isset($CommentGUID) ? $CommentGUID : ''; ?>" />
<!--// TotalLikes -->

<!-- TotalSeen -->
<div ng-include="seen_details_modal_tmplt"></div>
<!--// TotalSeen -->

<!-- Share Popup Code Starts -->
<div ng-include="share_popup_modal_tmplt"></div>
<!-- Share Popup Code Ends -->

<!-- Poll popup starts -->
<div class="modal fade postedForModal" id="postedForModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>
                <h4 class="modal-title" id="myModalLabel3">Posted for</h4>
            </div>
            <div class="modal-body padd-l-r-0 non-footer">
                <div class="designer-scroll mCustomScrollbar">
                    <ul class="list-group awaitinglist">
                        <li ng-repeat="list in postedForUser" class="list-group-item">
                            <figure class="media-left">
                                <a ng-if="list.ModuleID == '1'" entitytype="Group" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}">
                                    <img ng-if="list.ProfilePicture == ''" class="img-circle mCS_img_loaded" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                                    <img ng-if="list.ProfilePicture !== ''" class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/220x220/{{list.ProfilePicture}}" />
                                </a>
                                <a ng-if="list.ModuleID == '3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}">
                                    <img ng-if="list.ProfilePicture == ''" class="img-circle mCS_img_loaded" ng-src="{{AssetBaseUrl}}img/profiles/user_default.jpg" />
                                    <img ng-if="list.ProfilePicture !== ''" class="img-circle mCS_img_loaded" ng-src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/220x220/{{list.ProfilePicture}}" />
                                </a>
                            </figure>
                            <div class="description">
                                <a ng-if="list.ModuleID == '1'" entitytype="Group" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}" data-ng-bind="list.FirstName"></a>
                                <a ng-if="list.ModuleID == '3'" entitytype="User" entityguid="{{list.ModuleEntityGUID}}" class="name loadbusinesscard" ng-href="<?php echo base_url(); ?>{{list.ProfileURL}}" data-ng-bind="list.FirstName + ' ' + list.LastName"></a>
                                <p data-ng-if="list.ProfileTypeName != ''" data-ng-bind="list.ProfileTypeName"></p>
                                <p data-ng-if="list.Location.Location != ''" class="gray-text" data-ng-bind="list.Location.Location"></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
