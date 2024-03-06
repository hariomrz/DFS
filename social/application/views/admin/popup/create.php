<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Tools</span></li>
                    <li>/</li>
                    <li><span>Manage Popups</span></li>
                    <li>/</li>
                    <li><span>Create New Popup</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">

    <div class="container add-new-skill" id="PopupController" ng-controller="PopupController as PopupCtrl"  ng-init=""> 

        <div class="page-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <h2 class="page-title">Create New Popup</h2>
                    </div>
                </div>
        </div>
        
        <div class="panel panel-secondary">
            <div class="panel-body">
            
                <form name="createPopupForm" ng-submit="PopupCtrl.savePopup(createPopupForm);" novalidate>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Title<span class="required"> *</span></label> 
                        <div data-error="hasError" ng-cloak ng-class="{'hasError' : (createPopupForm.$submitted && createPopupForm.Popup.$error.maxlength && createPopupForm.Popup.$error.required) }">

                            <input type="text" class="form-control" name="Popup" ng-model="PopupCtrl.createPopup.PopupTitle" ng-maxlength="200" required>

                            <label class="required" ng-if="(createPopupForm.$submitted && createPopupForm.Popup.$error.required)">Please enter Popup Title.</label>
                            <label class="required" ng-if="( createPopupForm.Popup.$error.maxlength)">Max 200 characters are allowed.</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="radio radio-inline m-b-xs">
                            <input type="radio" ng-value="1" ng-checked="PopupCtrl.createPopup.PopupContentRadio" name="content_radio" ng-model="PopupCtrl.createPopup.PopupContentRadio"
                             >
                            <span class="label">Paste your HTML</span>
                        </label>
                        <div ng-if="(PopupCtrl.createPopup.PopupContentRadio==1)" data-error="hasError" ng-cloak ng-class="{'hasError' : (createPopupForm.$submitted && createPopupForm.Popup.$error.required) }">

                            <textarea   class="form-control" id="PopupContent" name="PopupContent" ng-disabled="(PopupCtrl.createPopup.PopupContentRadio==2)" ng-model="PopupCtrl.createPopup.PopupContent" required></textarea>

                            <label class="required" ng-if="((createPopupForm.$submitted && createPopupForm.PopupContent.$error.required) )">Please enter Popup Content.</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="radio radio-inline m-b-xs">
                            <input type="radio" ng-value="2" name="content_radio" ng-model="PopupCtrl.createPopup.PopupContentRadio" ng-checked="( PopupCtrl.createPopup.PopupContentRadio == 2 )">
                            <span class="label">Upload Image<span class="required"></span></span>
                        </label>
                        <div ng-if="(PopupCtrl.createPopup.PopupContentRadio==2)">
                            <div class="form-group">
                                <button type="button" class="btn btn-default" ngf-select="PopupCtrl.uploadPopupPicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="PopupCtrl.validateFileSize($file);" ng-disabled="((PopupCtrl.createPopup.PopupContentRadio==2) && PopupCtrl.isPopupPicUploading)" >
                                        <span class="icn">
                                            <svg class="svg-icons" width="18px" height="18px">
                                            <use xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#cameraIco" ng-href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#cameraIco"/>
                                            </svg>
                                        </span>
                                        <span class="text" ng-if="!(PopupCtrl.isPopupPicUploading)"> Choose a photo</span>
                                        <span class="text" ng-if="PopupCtrl.isPopupPicUploading"> Uploading...</span>
                                    </button>
                                    <span class="text" ng-if="PopupCtrl.OriginalName" ng-bind="PopupCtrl.OriginalName"></span>
                                    <a href="javascript:void(0);" class="icn" ng-click="PopupCtrl.removeImage()" ng-if="PopupCtrl.OriginalName">
                                            <img src="<?php echo base_url(); ?>assets/admin/img/cross-icon.png" >
                                        
                                        </a>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Popup Redirect URL. Please start URL with http:// or https://. This URL will open in new tab, when user clicks on the popup image.</label> 
                                <div data-error="hasError" ng-cloak ng-class="{'hasError' : (createPopupForm.$submitted && createPopupForm.Popup.$error.maxlength && createPopupForm.Popup.$error.required) }">

                                    <input type="url" class="form-control" name="ImageLink" ng-model="PopupCtrl.createPopup.ImageLink"  >

                                    <!-- <label class="required" ng-if="(createPopupForm.$submitted && createPopupForm.Popup.$error.required)">Please enter Popup Image URL.</label> -->
                                    <label class="required" ng-if="(createPopupForm.ImageLink.$error.url)">Invalid URL.</label>
                                </div>                            
                            </div>
                        </div>
                    </div>
                    <!-- <div class="">
                        
                    </div> -->
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-1">
                                <label class="control-label">Status</label>
                            </div>
                            <div class="col-xs-3">
                                <div class="radio-list">
                                    <label class="radio radio-inline">
                                        <input type="radio" ng-value="2" ng-checked="PopupCtrl.createPopup.Status" name="status" ng-model="PopupCtrl.createPopup.Status">
                                        <span class="label">Active</span>
                                    </label>
                                    <label class="radio radio-inline">
                                        <input type="radio" ng-value="1" name="status" ng-model="PopupCtrl.createPopup.Status">
                                        <span class="label">Inactive</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-8">
                            </div>
                        </div>
                    </div>
                    <div class="btn-toolbar btn-toolbar-left ">
                        
                        <a class="btn btn-default" href="<?php echo base_url('admin/popup'); ?>" >CANCEL</a>
                        <button class="btn btn-primary" type="button" data-target="#AnnouncementPopup" data-toggle="modal" ng-disabled="!(((PopupCtrl.createPopup.PopupContent && PopupCtrl.createPopup.PopupContentRadio==1) || (PopupCtrl.OriginalName && PopupCtrl.createPopup.PopupContentRadio==2 && !createPopupForm.ImageLink.$error.url)) && PopupCtrl.createPopup.PopupTitle)" ng-click="PopupCtrl.savePopup(createPopupForm,true)">
                                <use xlink:href="<?php echo base_url(); ?>assets/admin/img/sprite.svg#plusIco"></use></svg></span>
                            <span class="text">PREVIEW </span>
                        </button> 

                        <button type="submit" class="btn btn-primary" ng-disabled="!(((PopupCtrl.createPopup.PopupContent && PopupCtrl.createPopup.PopupContentRadio==1) || (PopupCtrl.OriginalName && PopupCtrl.createPopup.PopupContentRadio==2 && !createPopupForm.ImageLink.$error.url)) && PopupCtrl.createPopup.PopupTitle)" >SAVE</button>
                    </div>
                </div>
                </form>
            </div>
        </div> 

        <!-- Model Popups -->
    <div class="modal fade" id="AnnouncementPopup" >
        <div class="modal-dialog  announcement-popup">
            <div class="modal-content">
                <div class="modal-header white-bg">
                    <button type="button" class="close" ng-click="closePopup()" aria-label="Close" data-dismiss="modal" > <span aria-hidden="true"><span class="icon-close"></span></span> </button>
                    <h4 class="modal-title" id="myModalLabel" ng-bind-html="PopupCtrl.sanitizeMe(PopupCtrl.createPopup.PopupTitle,true)"></h4>
                </div>
                <div class="modal-body">
                     <div class="content-inner">
                        <div ng-class="(PopupCtrl.createPopup.PopupContentRadio=='1') ? 'post-content' : 'text-center'" id="PopupContentDiv">
                             <!-- <h2 ng-bind-html="PopupTitle"></h2> -->
                             <p ng-bind-html="PopupCtrl.sanitizeMe(PopupCtrl.createPopup.PopupContent,false)" ></p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>   
    </div> 

        
</div>