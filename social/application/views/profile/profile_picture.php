<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
          <h4 class="modal-title" id="myModalLabel">UPLOAD PROFILE PICTURE</h4>
        </div>
        <div class="modal-body">
            <div class="default-scroll sAssetBaseUrlcrollbar portfolio-item">
                <!-- <div class="portfolio-item"> -->
                <ul>
                    <li ng-repeat="pp in previousPictures"> <a href="javascript:void(0);" onclick="$('.profile-cropper-loader').show(); $('#photo6-small,#photo6-large').hide();" ng-click="changeCropBG('<?php echo IMAGE_SERVER_PATH ?>upload/profile/'+pp.ImageName,pp.MediaGUID);" data-toggle="modal" data-target="#croperUpdate"> <img ng-src="{{ImageServerPath}}upload/profile/220x220/{{pp.ImageName}}"   err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" /> </a> </li>
                </ul>
                <span ng-hide="previousPictures.length" ng-cloak class="text-center"> No previous profile picture(s) found. </span>
                <!-- </div> -->
            </div>
        </div>
        <div class="modal-footer text-center custom-sapce"> 
            <a style="display:none" onclick="beforeCropperStarts();" class="show-modal" data-toggle="modal" data-target="#croperUpdate">Show Modal</a> 
            <a href="javascript:void(0);" class="takepic select-image-btn" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                <span class="space-icon">
                    <i class="icon-camerablue"></i>
                </span> Upload New Photo
            </a>
            <!--<input type="file" id="uploadProfilePic" name="uploadProfilePic" ngf-select ngf-change="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ngf-model-options="{updateOn: 'change click', debounce: 0}">-->
        </div>
        <!--<div class="modal-footer text-center custom-sapce"> <a style="display:none" onclick="beforeCropperStarts();" class="show-modal" data-toggle="modal" data-target="#croperUpdate">Show Modal</a> <a class="takepic select-image-btn"> <span class="space-icon"> <i class="icon-camerablue"></i> </span> Upload New Photo </a> </div>-->
      </div>
    </div>
   
</div>
<div class="modal fade" id="croperUpdate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
          <h4 class="modal-title" id="myModalLabel">CROP PHOTO</h4>
        </div>
        <div class="modal-body">
          <div class="croper-block coper"> 
              
            <div class="cmn-loader cropper-loader">
                <div ng-if="profileUploadPrgrs.progressPercentage && profileUploadPrgrs.progressPercentage < 101" data-percentage="{{profileUploadPrgrs.progressPercentage}}" upload-progress-bar-cs></div>
                <span class="loader" ng-if="applyProfilePictureLoader"></span>
            </div> 
              
            
              
            <div class="image-editor">
              <div class="inne-imagecrop">
                <div class="croper-blockwrap">
                  <div class="cropit-image-preview-container">
                    <div class="cropit-image-background-container"></div>
                    <div class="cropit-image-preview cropit-image-loaded">                      
                    </div>
                    <!-- <div class="small-cropit">
                      <div class="cropit-image-preview cropit-image-loaded" style="width:32px; height:32px;"></div>
                    </div> -->
                  </div>
                </div>
              </div>
              <div class="slider-wrapper center-slide">
                <div class="pull-left"> <span class="image-icon"><i class="icon-small-image"></i></span>
                  <div class="range-slide">
                    <input type="range" class="cropit-image-zoom-input custom" min="0" max="1" step="0.01" />
                  </div>
                  <span class="image-icon"><i class="icon-large-image"></i></span> </div>
              </div>
            </div>            
            <div class="btn  btn-secondary btn-icon drag-btn" type="button" style="display:none;"> <i class="icon-dragpic"></i> Drag to Crop </div>
          </div>
        </div>
        <div class="modal-footer custom-footer">
          <div class="pull-left"> <!-- <a href="javascript:void(0);" class="skip">Skip Cropping</a> --> </div>
          <div class="pull-right">
            <button id="close_btn" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button id="CropAndSave" type="button" class="btn btn-primary cropnsave" ng-click="cropAndSave();">CROP AND SAVE
                <span class="btn-loader">
                  <span class="spinner-btn">&nbsp;</span>
                </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  
</div>
<input type="hidden" id="photo6-MediaGUID" value="" />
<input type="hidden" id="ProfilePicturePageNo" value="1" />