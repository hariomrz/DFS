<div data-ng-controller="EventPopupFormCtrl" id="EventPopupFormCtrl" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID']?>');initialize('<?php echo $Section;?>');">
  <?php $this->load->view('profile/profile_banner') ?>
  <!--Container-->
  <!-- <div ng-controller="AlbumCtrl" id="AlbumCtrl" ng-cloak ng-init="setProfileUrl('<?php echo $profile_url;?>');setModuleSection('<?php echo $moduleSection;?>','<?php echo $sectionGUID ?>')">
    <div class="container wrapper">
      <?php //$this->load->view('album/album'); ?>
      <?php //$this->load->view('events/UpdateEventPopup');?>
    </div>
      
    <div class="editDropdown dropdown-menu" id="editLocation">
        <div class="custom-content">
            <div data-error="hasError" class="text-field location">
              <i class="icon-location">&nbsp;</i>
              <input type="text" placeholder="Select Location">
              <label class="error-block-overlay"></label>
            </div>
          <div class="dropdown-footer">
            <button class="btn btn-primary btn-sm pull-right" type="button">SAVE</button>
            <button type="button" class="btn btn-sm btn-link pull-right" onclick="$('.editDropdown').hide();">Cancel</button>
          </div>  
        </div>
    </div>
    <div class="editDropdown dropdown-menu" id="addTag">
        <div class="custom-content">
            <div data-error="hasError" class="text-field"> 
              <input type="text" placeholder="add # tags">
              <label class="error-block-overlay"></label>
            </div>
          <div class="dropdown-footer">
            <button class="btn btn-primary btn-sm pull-right" type="button" onclick="">SAVE</button>
            <button type="button" class="btn btn-sm btn-link pull-right" onclick="$('.editDropdown').hide();">Cancel</button>
          </div>  
        </div>
    </div>
  </div> -->

  <div class="container container-primary wrapper">
    <div class="row">
      <div class="col-md-9 col-sm-8">
        <span ng-show="DetailPageLoaded == 0" class="loader text-lg" style="display:block;">&nbsp;</span>
        <!-- media section begins here-->
        <div data-ng-controller="EventMediaController" ng-include="event_media"></div>
        <!-- media section ends here-->
      </div>

      <div class="col-md-3 col-sm-4 sidebar" ng-cloak ng-if="show_sidebar" data-scroll="fixed" ng-init="initScrollFix()">

<!--        <div data-ng-controller="EventUserController">
          <div ng-include="event_schedule"></div>
          <div ng-include="event_hosted_by"></div>
        </div>     -->

        <div data-ng-controller="SimilarEventController" ng-include="event_similar"></div>

        <div data-ng-controller="EventAttendeesController" ng-include="event_attendees"></div>

        <div data-ng-controller="EventInviteController" ng-include="event_invite"></div>        

        <div data-ng-controller="EventShareController" ng-include="event_social_share"></div>
      </div>
    </div>
    <div ng-include="edit_event"></div>
  </div>
  <div ng-include="total_invity_popup"></div>

<input type="hidden" id="hdn_module_id" name="hdn_module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  }?>" />
<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="EventGUID" value="<?php echo $EventGUID;?>" />
<input type="hidden" id="hdn_module_guid" value="<?php echo $EventGUID;?>" />
<input type="hidden" id="page_name" value="media" />
