<div ng-controller="GroupMemberCtrl" id="GroupMemberCtrl" ng-init="GroupDetail()">
  <?php $this->load->view('profile/profile_banner') ?>
  <!--Container-->
  <div ng-controller="AlbumCtrl" id="AlbumCtrl" ng-cloak ng-init="setModuleSection('<?php echo $moduleSection;?>','<?php echo $sectionGUID ?>')">
    <div class="container wrapper">
      <?php $this->load->view('album/album'); ?>
      <!-- Right Wall-->
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
      
  </div>

<input type="hidden" id="hdn_module_id" name="hdn_module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  }?>" />
<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="hdn_module_guid" value="<?php echo $ModuleEntityGUID;?>" />

<!-- <input type="hidden" id="ProfilePicURLGM" value="" />
<input type="hidden" id="ProfilePicMediaGUIDGM" value="" /> -->
