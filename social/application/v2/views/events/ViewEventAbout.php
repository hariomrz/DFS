<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID'] ?>'); initialize('<?php echo $Section; ?>');">
    <?php $this->load->view('profile/profile_banner') ?>


    <div class="container container-primary wrapper">
        <div class="row" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl">
            <div class="col-md-8">
                <div ng-cloak  ng-include="AssetBaseUrl + 'partials/include/post/newsfeed.html'"></div>
                <!-- about section begins here-->
                <div ng-include="about_description"></div>
                <!-- about section ends here-->
            </div>
            <div class="col-md-4 sidebar">
                <div class="panel panel-info" ng-if="EventDetail.CanPostOnWall" ng-cloak>
                    <a class="link-contest" ng-if="!LoginSessionKey" ng-click="loginRequired()" ng-cloak>
                        <span class="icon"><i class="ficon-question f-orange"></i></span>
                        <span class="text">ASK A QUESTION</span>
                    </a>
                    <a class="link-contest" ng-if="LoginSessionKey" ng-click="updateActivePostType(2);showNewsFeedPopup();" ng-cloak>
                        <span class="icon"><i class="ficon-question f-orange"></i></span>
                        <span class="text">ASK A QUESTION</span>
                    </a>
                </div>
                <div data-ng-controller="EventAttendeesController" ng-include="event_attendees"></div>
                <div data-ng-controller="EventMediaController" ng-include="event_media_widget"></div>
                <div data-ng-controller="SimilarEventController" id="SimilarEventController" ng-include="event_more"></div>
                <div data-ng-controller="PastEventController" id="PastEventController" ng-include="past_events"></div>
            </div>
            <!-- edit event popup start here-->
            <div ng-include="edit_event"></div>
            <!-- edit event popup end here-->
        </div>
    </div>

    <div ng-include="total_invity_popup"></div>
</div>
    <!--//Container-->
    <input type="hidden" id="WallPageNo" value="1"/>
    <input type="hidden" id="FeedSortBy" value="4"/>
    <input type="hidden" id="IsMediaExists" value="2"/>
    <input type="hidden" id="PostOwner" value=""/>
    <input type="hidden" id="ActivityFilterType" value="0"/>
    <input type="hidden" id="AsOwner" value="0"/>
    <input type="hidden" id="page_name" value="about"/>
    <input type="hidden" id="newsFeedPageSize" value="2"/>


    <?php $this->load->view('events/ViewEventClosedModal') ?>

