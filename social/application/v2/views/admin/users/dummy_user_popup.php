<div class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" id="createNewUser">
    <div class="modal-dialog modal-sm m-t-elg" role="document">
        <div class="modal-content">
            <div class="modal-body p-elg modal-thumbup">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="ficon-cross"></i></span></button>
                <div class="thumbnail-upload">
                    <aside class="profile-pic set-profile-pic">
                        <figure class="figure-circle img-profile-popup">
                            <img ng-if="ProfilePicMediaGUID != '' && ProfilePicMediaGUID != 'undefined'" err-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/default-148.png'}}" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/'+NewUserProfilePic}}" alt="User" title="User" class="img-circle">
                            <img ng-if="ProfilePicMediaGUID == '' || ProfilePicMediaGUID == 'undefined'" src="{{createUser.MediaUrl}}" alt="User" title="User" class="img-circle">
                        </figure>
                        <div class="dropdown upload-dropdown">
                            <a class="icn circle-icn circle-default circle-sm" data-toggle="dropdown">
                                <i class="ficon-camera"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li ng-init="getPreviousProfilePictures();">
                                    <a ng-show="previousPictures.length > 0" data-target="#uploadModal" data-toggle="modal" href="javascript:void(0);" ng-cloak>
                                        <span class="space-icon"><i class="icon-upload"></i></span>
                                        <?php echo lang('upload_new'); ?>
                                    </a>
                                    <a id="uploadProPic" ng-show="previousPictures.length == 0" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" ng-cloak>
                                       <span class="icn"><i class="ficon-upload"></i></span>
                                        <?php echo lang('upload_new'); ?>
                                    </a>
                                </li>
                                <li ng-click="removeProfilePicture(createUser.UserGUID)" ng-show="createUser.UserMediaGUID || createUser.ProfilePicture"><a><span class="icn"><i class="ficon-cross"></i></span><span class="text">Remove</span></a></li>
                            </ul>
                        </div>
                    </aside>
                </div>
                <div class="row">
                    <div class="form-group col-xs-6" ng-class="(createUserError.FirstName) ? 'has-error' : '' ;">
                        <label class="control-label">First Name<span class="required">*</span></label>
                        <input type="text" ng-model="createUser.FirstName" class="form-control" placeholder="Enter first name">
                        <div class="error-block">Please Enter first name.</div>
                    </div>
                    <div class="form-group col-xs-6" ng-class="(createUserError.LastName) ? 'has-error' : '' ;">
                        <label class="control-label">Last Name<span class="required">*</span></label>
                        <input type="text" ng-model="createUser.LastName" class="form-control" placeholder="Enter last name">
                        <div class="error-block">Please Enter last name.</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">Location</label>
                    <input type="text" ng-init="initCity();" class="form-control" id="add" placeholder="Enter user's location">
                </div>
                <div class="form-group">
                    <label class="control-label">Email ID</label>
                    <input type="text" ng-model="createUser.Email" class="form-control" placeholder="Enter email id e.g. johnp@companyname.com">
                </div>
                <div class="row">
                    <div class="form-group col-xs-6" ng-class="(createUserError.Gender) ? 'has-error' : '' ;">
                        <label class="control-label">Gender<span class="required">*</span></label>
                        <select chosen data-disable-search="true" ng-model="createUser.Gender" ng-options="g.key as g.value for g in [{key:0,value:'Select'},{key:1,value:'Male'},{key:2,value:'Female'}]" title="Select" placeholder="Select" class="form-control">
                        </select>
                        <div class="error-block">Please Enter Gender.</div>
                    </div>
                    <div class="form-group col-xs-6" ng-class="(createUserError.DOB) ? 'has-error' : '' ;">
                        <label class="control-label">Date of Birth<span class="required">*</span></label>
                        <div class="input-group date">
                            <input readonly="readonly" type="text" ng-model="createUser.DOB" class="form-control" id="dob12" placeholder="DD-MM-YYYY">
                            <label class="input-group-addon" for="dob12">
                                <i class="ficon-calender"></i>
                            </label>
                        </div>
                        <div class="error-block">Please Enter Date of Birth.</div>
                    </div>
                </div>
                <div class="btn-toolbar btn-toolbar-center m-t">
                    <button type="button" ng-click="create_dummy_user()" class="btn btn-primary btn-block btn-lg" ng-if="Settings['m31'] == 1">
                        Continue
                    </button>
                    
                    <button type="button" ng-click="create_dummy_user()" class="btn btn-primary btn-block btn-lg" ng-if="Settings['m31'] == 0">
                        Save
                    </button>
                    
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
          <h4 class="modal-title" id="myModalLabel">UPLOAD PROFILE PICTURE</h4>
        </div>
        <div class="modal-body">
            <div class="default-scroll scrollbar portfolio-item">
                <!-- <div class="portfolio-item"> -->
                <ul>
                    <li ng-repeat="pp in previousPictures"> <a href="javascript:void(0);" onclick="$('.profile-cropper-loader').show(); $('#photo6-small,#photo6-large').hide();" ng-click="changeCropBG('<?php echo IMAGE_SERVER_PATH ?>upload/profile/'+pp.ImageName,pp.MediaGUID);" data-toggle="modal" data-target="#croperUpdate"> <img ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{pp.ImageName}}"   err-SRC="<?php echo site_url() ?>assets/img/profiles/user_default.jpg" /> </a> </li>
                </ul>
                <span ng-hide="previousPictures.length" ng-cloak class="text-center"> No previous profile picture(s) found. </span>
                <!-- </div> -->
            </div>
        </div>
        <div class="modal-footer text-center custom-sapce"> 
            <a style="display:none" onclick="beforeCropperStarts();" class="show-modal" data-toggle="modal" data-target="#croperUpdate">Show Modal</a> 
            <a href="javascript:void(0);" class="takepic select-image-btn" ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
            <!--<a href="javascript:void(0);" class="takepic select-image-btn" id="uploadNewPhoto" onclick="$('#uploadProfilePic').trigger('click');">-->
            <!--<a href="javascript:void(0);" class="takepic select-image-btn" id="uploadNewPhoto" onclick="$('#uploadProfilePic').trigger('click');">-->
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
  
    <div class="modal-dialog  modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
          <h4 class="modal-title" id="myModalLabel">CROP PHOTO</h4>
        </div>
        <div class="modal-body">
          <div class="croper-block coper">
            <div class="cmn-loader cropper-loader">
                <span class="loader"></span>
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

<!-- Post Start -->
<div class="modal fade" tabindex="-1" role="dialog" id="addPost">
    <div class="modal-dialog" role="document">
        <div class="modal-content">             
            <div class="modal-body no-padding">
                <?php $this->load->view('admin/users/post') ?>
            </div>
        </div>
    </div>
</div>
<!-- Post Ends -->

<!-- Interest Popup Start -->
<div class="modal fade" tabindex="-1" role="dialog" id="selectInterest">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-lg">What’re you into?</h4>
                <span class="">Tell us what you like then we will get you the good stuff.</span>
            </div>
            <div class="modal-body custom-scroll  scroll-md"> 
                <div id="divLoader2" class="hide">
                    <img id="spinner" ng-src="<?php echo ASSET_BASE_URL .'admin/img/loader.gif';?>">
                    <span id="loadertext"><?php echo lang('Loading'); ?></span>
                </div>
                <ul class="list-thumbnail thumbnail-grid-5 ">
                    <li class="thumbnail-item" ng-repeat="interest in all_interests">
                        <div class="thumbnail thumbnail-image">
                            <div class="block" ng-class="{'selected':$index == 2}">
                                <figure ng-class="(interest.IsInterested=='1') ? 'selected' : '' ;" class="interest-list" id="f-{{interest.CategoryID}}">     
                                    <input name="interest[]" type="checkbox" id="i-{{interest.CategoryID}}" ng-value="interest.CategoryID" class="hidden" autocomplete="off">
                                    <a class="img-block img-check">
                                    <span class="icn"><i class="ficon-check"></i></span>    
                                    <!-- <img src="assets/img/thumb-blank-136x-136.png" width="136" height="136" class="img-full" > -->
                                    <img ng-src="{{'<?php echo ASSET_BASE_URL ?>admin/img/thumb-blank-136x-136.png'}}" width="136" height="136" class="img-full" >
                                    <img err-src="{{'<?php echo ASSET_BASE_URL ?>img/Interest-default.jpg'}}" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/category/220x220/'+interest.ImageName}}" width="136" height="136" class="img-full main-img"  id="item1">
                                    </a>
                                </figure>
                                <div class="caption">                                       
                                    <h5 class="title" ng-bind="interest.Name"></h5>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul> 
            </div>
            <div class="modal-footer modal-sticky">
                <h4 class="sticky-text">
                <span class="text hide">Select 3 more</span>
                <a class="" ng-click="save_interest()" data-dismiss="modal">I’m done</a>
                </h4>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Interest Popup Ends -->