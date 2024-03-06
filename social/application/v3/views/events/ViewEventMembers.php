<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID']?>');initialize('<?php echo $Section;?>');">
    <?php $this->load->view('profile/profile_banner') ?>
    <!--Container-->
    <div class="container container-primary wrapper" ng-cloak>
        <div class="row">
            <!-- Left Wall-->
            <div class="col-md-9 col-sm-8">
                <span ng-show="DetailPageLoaded == 0" class="loader text-lg" style="display:block;">&nbsp;</span>
                <div data-ng-controller="EventMemberController" ng-include="event_member"></div>
            </div>
            <!-- //Left Wall-->
            <!-- Right Wall-->
            <div class="col-md-3 col-sm-4" ng-cloak ng-if="show_sidebar" data-scroll="fixed" ng-init="initScrollFix()">  
                <div data-ng-controller="SimilarEventController" ng-include="event_similar"></div>
<!--                <div data-ng-controller="EventAttendeesController" ng-include="event_attendees"></div>-->
                <div data-ng-controller="EventInviteController" ng-include="event_invite"></div>        
                <div data-ng-controller="EventShareController" ng-include="event_social_share"></div>
            </div>
            <div ng-include="edit_event"></div>
            <!-- //Right Wall-->
        </div>
    </div>
    <!--//Container-->
    <div ng-include="total_invity_popup"></div>
    <input type="hidden" id="page_name" value="member" />


    
    <?php $this->load->view('events/ViewEventClosedModal') ?>