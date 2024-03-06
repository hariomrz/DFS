<div data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>');GetPageDetails('<?php echo $PageGUID;?>')">
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
            <button class="btn btn-primary btn-sm pull-right" type="button" ng-bind="::lang.a_save_caps"></button>
            <button type="button" class="btn btn-sm btn-link pull-right" onclick="$('.editDropdown').hide();" ng-bind="::lang.cancel"></button>
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
            <button class="btn btn-primary btn-sm pull-right" type="button" onclick="" ng-bind="::lang.a_save_caps"></button>
            <button type="button" class="btn btn-sm btn-link pull-right" onclick="$('.editDropdown').hide();" ng-bind="::lang.cancel"></button>
          </div>  
        </div>
    </div>
      
  </div>
</div>

<input type="hidden" id="hdn_module_id" name="hdn_module_id" value="<?php if(!empty($ModuleID) && isset($ModuleID)) {echo $ModuleID ;  }?>" />
<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="PageGUID" value="<?php if(!empty($PageID) && isset($PageID)) {echo $PageID ;  }?>" />
<input type="hidden" id="hdn_module_guid" value="<?php if(!empty($PageGUID) && isset($PageGUID)) {echo $PageGUID ;  }?>" />
