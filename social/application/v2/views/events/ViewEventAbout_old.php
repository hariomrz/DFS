<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID']?>');initialize('<?php echo $Section;?>');">
  <?php $this->load->view('profile/profile_banner') ?>
    <div class="container wrapper">
    <div class="row">
      <div class="col-md-9 col-sm-8">
        <span ng-show="DetailPageLoaded == 0" class="loader text-lg" style="display:block;">&nbsp;</span>

        <!-- about section begins here-->
        <div ng-include="about_description"></div>
        <!-- about section ends here-->

        <!-- location section begins here-->
        <div ng-include="about_map"></div>
        <!-- location section ends here-->

        <!-- media section begins here-->
        <div data-ng-controller="EventMediaController" ng-include="event_media"></div>
        <!-- media section ends here-->
      </div>
      <div class="col-md-3 col-sm-4 sidebar sidebar-pullup" ng-cloak ng-if="show_sidebar" data-scroll="fixed" ng-init="initScrollFix()">

        <div data-ng-controller="EventUserController">
          <div ng-include="event_schedule"></div>
          <div ng-include="event_hosted_by"></div>
        </div>     

        <div data-ng-controller="EventAttendeesController" ng-include="event_attendees"></div>

        <div data-ng-controller="EventInviteController" ng-include="event_invite"></div>

        <div data-ng-controller="EventShareController" ng-include="event_social_share"></div>        
      </div>

      <!-- edit event popup start here-->
      <div ng-include="edit_event"></div>
      <!-- edit event popup end here-->
      <?php //$this->load->view('events/UpdateEventPopup');?>
    </div>
  </div>

  <div ng-include="total_invity_popup"></div>

  <!--//Container-->
  <input type="hidden" id="WallPageNo" value="1" />
  <input type="hidden" id="FeedSortBy" value="2" />
  <input type="hidden" id="IsMediaExists" value="2" />
  <input type="hidden" id="PostOwner" value="" />
  <input type="hidden" id="ActivityFilterType" value="0" />
  <input type="hidden" id="AsOwner" value="0" />
  <input type="hidden" id="page_name" value="about" />
  
  
  <?php $this->load->view('events/ViewEventClosedModal') ?>

