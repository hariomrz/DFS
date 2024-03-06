<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php //echo $auth['EventGUID'] ?>'); initialize('<?php echo $Section; ?>');">
    <?php $this->load->view('profile/profile_banner') ?>
    <!--Container-->
    <div class="container container-primary wrapper" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="GetWallPostInit()" ng-cloak>
        <div class="row">
            <div class="col-md-2 col-sm-12 hidden-xs" left-sidebar="fixed">
                <div class="panel panel-widget">
                    <div class="panel-heading ng-scope">
                        <h3 class="panel-title"><span class="text" ng-bind="lang.about_caps"></span></h3>
                    </div>
                    <div class="panel-body">
                        <p ng-cloak>                
                            {{::EventDetail.Description.substr(0, 200)}} 
                            <a class="text-link" ng-if="EventDetail.Description.length > 200" href="<?php echo $this->event_model->getEventTitleUrl('about', $ModuleEntityGUID, $EventTitleUrl); ?>" ng-bind="lang.read_more"></a>
                        </p>               
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-12 pull-right hidden-xs" ng-cloak ng-if="show_sidebar" data-scroll="fixed" ng-init="initScrollFix()">    

                <div data-ng-controller="SimilarEventController" ng-include="event_similar"></div>

                <div data-ng-controller="EventAttendeesController" ng-include="event_attendees"></div>

                <div data-ng-controller="EventInviteController" ng-include="event_invite"></div>        

                <div data-ng-controller="EventShareController" ng-include="event_social_share"></div>
            </div>
            <div class="col-md-7 col-sm-8 col-xs-12 pull-left">
                <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>
            </div>
            <div ng-include="edit_event"></div>
            <div ng-include="total_invity_popup"></div>
        </div>
    </div>
    <!--//Container-->

    <input type="hidden" id="WallPageNo" value="1" />
    <input type="hidden" id="FeedSortBy" value="2" />
    <input type="hidden" id="IsMediaExists" value="2" />
    <input type="hidden" id="PostOwner" value="" />
    <input type="hidden" id="ActivityFilterType" value="0" />
    <input type="hidden" id="AsOwner" value="0" />
    <input type="hidden" id="page_name" value="wall" />


    <?php $this->load->view('events/ViewEventClosedModal') ?>
