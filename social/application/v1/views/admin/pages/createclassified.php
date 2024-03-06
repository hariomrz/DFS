<div class="row-flued"  data-ng-controller="PageListCtrl" id="PageListCtrl">
    <h2><?php
        if (!empty($page_guid)) {
            echo lang('classified_edit');
        } else {
            echo lang('classified_create');
        }
        ?></h2>
    <style>
        .weekday-table thead tr th{text-align: left; padding: 5px 10px; font-weight: normal;}
        .weekday-table thead tr th:first-child{text-align: right;}
        .weekday-table tr td{padding: 5px 10px;}
        .text-field-cover{width: 580px;}
    </style>
    <div class="add-category mTop35"  ng-init="classifiedcreate_view = 1;">
        <div class="category-left">
            <form id="crtPageBusiness" >
                <table class="addcategory-table rolestable" ng-init="initialize();packages();
                <?php
                if (!empty($page_guid)) {
                    echo "detail('$page_guid')";
                }
                ?>">
                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('location'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input ng-focus="LocationInitialize('LocationID');" class="form-control location-field" type="text" placeholder="Enter Location" 
                                           ng-model='PageData.Location' name="Location" id="LocationID" 
                                           data-msglocation="errorLocationID" data-mandatory="true" data-controltype="" data-requiredmessage="<?php echo lang('Location_required'); ?>" >

                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorLocationID"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('category'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field-select" data-error="hasError" ng-init="PageCategories('', 'ParentCategory', 'CreateClassified')">
                                    <select name="CategoryID" id="CategoryID" chosen ng-options="cat.category_id as cat.name for cat in PCategoryData" 
                                            ng-model="PageData.CategoryID"
                                            ng-change="PageCategories(PageData.CategoryID, 'SubCategory', 'CreateClassified');"
                                            data-mandatory="true" data-msglocation="errorCategoryID"  data-placeholder="Select Category" data-controltype="general" data-requiredmessage="<?php echo lang('Category_required'); ?>">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorCategoryID"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('sub_category'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field-select from-subject large">
                                    <select name="SubCategoryID" id="SubCategoryID" chosen ng-options="cat.category_id as cat.name for cat in SubCategoryData" ng-model="PageData.SubCategoryID"
                                            data-mandatory="true" data-msglocation="errorSubCategoryID"  data-placeholder="Select Subcategory" data-controltype="general" data-requiredmessage="<?php echo lang('Subcategory_required'); ?>">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorSubCategoryID"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="valign">
                            <label class="label">Package<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field-select" data-error="hasError">
                                    <select name="PackageID" id="PackageID" chosen ng-options="package.PackageID as package.Title for package in PackageData" 
                                            ng-model="PageData.PackageID"
                                            data-mandatory="true" data-msglocation="errorPackageID"  data-placeholder="Select Package" data-controltype="general" data-requiredmessage="Please select package.">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorPackageID"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('Title'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  type="text" placeholder="Enter Title" ng-model='PageData.Title' name="Title" id="Title" 
                                            data-req-maxlen="50" maxlength="50"
                                            data-msglocation="errorTitle" data-mandatory="true" data-controltype="" data-requiredmessage="<?php echo lang('title_required'); ?>" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorTitle"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('Description'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            
                                <div class="form-controll" data-ng-controller="SummernoteController" data-error="hasError">
                                    <textarea ng-model="PageData.Description" id="Description"  rows="10" placeholder="Enter <?php echo lang('Description'); ?>" data-placeholder="Enter <?php echo lang('Description'); ?>" 
                                              data-req-minlen="40" data-summernote="" 
                                              data-msglocation="errorDescription" data-mandatory="true" data-controltype="" data-requiredmessage="<?php echo lang('page_desc_required'); ?>" ></textarea>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorDescription"></div>
                                <div class="clearfix">&nbsp;</div>
                            
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-r checkd">
                            <input type="checkbox" value="1" id="ShowPhone" ng-model="PageData.ShowPhone" ng-true-value="1" ng-false-value="0">
                            <label class="label check" for="ShowPhone">Show Always</label>
                        </td> 
                    </tr>
                    <tr>

                        <td class="valign">
                            <label class="label"><?php echo lang('phone_number'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  type="text" class="usaphonenumber" ng-model='PageData.Phone' name="Phone" id="Phone" 
                                            data-msglocation="errorPhone" data-mandatory="true" data-controltype="usaphonenumber" placeholder="Enter Phone Number"  data-requiredmessage="<?php echo lang('Phone_number_required'); ?>">
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorPhone"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-r checkd">
                            <input type="checkbox" value="1" id="ShowAddressLine1" ng-model="PageData.ShowAddressLine1" ng-true-value="1" ng-false-value="0">
                            <label class="label check" for="ShowAddressLine1">Show Always</label>

                        </td> 
                    </tr>

                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('address_line_1'); ?><span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  type="text" placeholder="Enter Address Line 1" ng-model='PageData.AddressLine1' name="AddressLine1" id="AddressLine1" 
                                            data-req-maxlen="100" maxlength="100"
                                            data-msglocation="errorAddress" data-mandatory="true" data-controltype="" data-requiredmessage="<?php echo lang('address_required'); ?>" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorAddress"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text-r checkd">
                            <input type="checkbox" value="1" id="ShowAddressLine2" ng-model="PageData.ShowAddressLine2" ng-true-value="1" ng-false-value="0">
                            <label class="label check" for="ShowAddressLine2">Show Always</label>

                        </td> 
                    </tr>
                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('address_line_2'); ?></label> 
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  type="text" placeholder="Enter Address Line 2" ng-model='PageData.AddressLine2' name="AddressLine2" id="AddressLine2" 
                                            data-req-maxlen="100" maxlength="100">
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="valign">
                            <label class="label"><?php //echo lang('location');   ?>Your Location<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  ng-focus="LocationInitialize('MyLocationID');" class="form-control location-field" type="text" placeholder="Enter Your Location" 
                                            ng-model='PageData.MyLocation' name="MyLocation" id="MyLocationID" 
                                            data-msglocation="errorMyLocationID" data-mandatory="true" data-controltype="" data-requiredmessage="<?php echo lang('MyLocation_required'); ?>" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorMyLocationID"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('zip_code'); ?></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-type="focus">
                                    <input  type="text" placeholder="Enter Zip Code" ng-model='PageData.PostalCode' name="PostalCode" id="PostalCode" 
                                            data-msglocation="errorPostalCode" data-mandatory="" data-controltype="zipcodenumeric" data-requiredmessage="" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorPostalCode"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>                   

                    <tr >
                        <td class="valign">
                            <label class="label"><?php echo lang('cover_image'); ?>
                            </label>
                        </td>
                        <td>
                            <div class="form-control" ng-init="initializeCover();">
                                <div class="text-field-cover from-subject">

                                    <div style="position:relative; padding:0; margin:0; height:30px;">
                                        <input type="file" id="fileInput" style="opacity:1; position: static; width:300px;" />
                                    </div>
                                    <span style="color:gray;">(Please upload image of minimum width 1200 px, for better results)</span>
                                    <input type="hidden" name="newCoverImagePath" data-ng-model="PageData.CoverImagePath"  value="" id="newCoverImagePath">
                                    <input type="hidden" name="newCoverImageName" data-ng-model="PageData.CoverImageName"  value="" id="newCoverImageName">

                                    <div class="coverImgThumb" style="display: none;">
                                        <img id="coverImgThumbSrc" ng-src="PageData.newCoverImagePath" alt="cover image" 
                                             style="width: 100%;" />
                                    </div>

                                    <div class="error-holder"><span>Error</span></div>

                                    <div id="cropArea">

                                        <img-crop   image="myImage" 
                                                    result-image="myCroppedImage" 
                                                    area-type="rectangle"  

                                                    result-width="myCroppedImageW"
                                                    result-height="myCroppedImageH"


                                                    result-x="myCroppedImageX"
                                                    result-y="myCroppedImageY"

                                                    original-width="myOriginalW"
                                                    original-height="myOriginalH"

                                                    original-crop-x="myOriginalX"
                                                    original-crop-y="myOriginalY"

                                                    original-crop-width="myCroppedOriginalW"
                                                    original-crop-height="myCroppedOriginalH"
                                                    dimension-valid-flag = "dimensionValidFlag"
                                                    result-image-size='[{w: 980,h: 380}]'
                                                    >

                                        </img-crop>

                                    </div>
                                    <div id="cropLabel" style="display:none">Cropped Image:</div>
                                    <div><img id="CroppedImgData" ng-src="{{myCroppedImage}}" style="" /></div> 
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('banner_image'); ?>
                            </label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field-cover from-subject">
                                    <div class="button"><div id="page_banner_photo"><?php echo lang('Upload'); ?></div></div>
                                </div>
                                <div class="attached-media-banner">

                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr ng-init="initialize();"> 
                        <td class="valign">
                            <label class="label"><?php echo lang('add_photos'); ?></label>
                        </td>
                        <td>
                            <div class="media-upload">
                                <ul>
                                    <li>
                                        <div class="upload-panel">
                                            <div class="media-wrap">
                                                <div class="upload-icon"><img src="<?php echo base_url(); ?>assets/admin/img/upload-img.png"></div>
                                                <div class="upload-name">
                                                    <span class="bold-text"><?php echo lang('choose_photo'); ?></span>
                                                    <span><?php echo lang('upload_photo_desc'); ?></span>
                                                </div>
                                                <div class="button btn-upload"><div id="page_photo"><?php echo lang('Upload'); ?></div></div>
                                            </div>

                                        </div>
                                    </li> 
                                    <li>
                                        <ul class="attached-media">

                                        </ul>
                                    </li>

                                </ul> 
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            <div class="error-holder usrerror" id="errorPhoto"></div>
                            <div class="clearfix">&nbsp;</div>
                        </td>
                    </tr>

                    <!-- job-class-field START -->


                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field" ng-init="load_validation_controll();">
                        <td class="valign">
                            <label class="label" for="EmploymentType">Employment Type<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control" ng-init='EmploymentTypeOptions =<?php echo json_encode($this->config->item('EmploymentType')); ?>'>
                                <div class="text-field-select" data-error="hasError">
                                    <select class="" chosen data-placeholder="Please Select" id="EmploymentType"
                                            ng-options="ETKey as ETVal for (ETKey, ETVal) in  EmploymentTypeOptions" 
                                            ng-model="PageData.EmploymentType"
                                            data-mandatory="true" data-msglocation="errorEmploymentType" data-controltype="general" data-requiredmessage="Employment Type is required">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorEmploymentType"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                            <div ng-if="PageData.EmploymentType == 4" ng-init="load_validation_controll();" class="form-group" >
                                <div class="text-field large" data-error="hasError" data-type="focus">
                                    <input type="text" class="form-control" id="EmploymentTypeOther" placeholder="Enter Other Employment Type" 
                                           ng-model='PageData.EmploymentTypeOther' name="EmploymentTypeOther" 
                                           data-msglocation="errorEmploymentTypeOther" data-mandatory="true" data-controltype="" data-requiredmessage="Other employment type is required" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorEmploymentTypeOther"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field" ng-init="load_validation_controll();">
                        <td class="valign">
                            <label class="label" id="Remuneration">Compensation<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control">
                                <div class="text-field large" data-error="hasError" data-type="focus">
                                    <input type="text" class="form-control" id="Remuneration" placeholder="Enter Compensation" 
                                           ng-model='PageData.Remuneration' name="Remuneration" 
                                           data-msglocation="errorRemuneration" data-mandatory="true" data-controltype="" data-requiredmessage="Compensation is required" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorRemuneration"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field" ng-init="load_validation_controll();">
                        <td class="valign">
                            <label class="label" for="Experience">Experience<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control" ng-init='ExperienceOptions =<?php echo json_encode($this->config->item('Experience')); ?>'>
                                <div class="text-field large" data-error="hasError">
                                    <!--select class="" chosen data-placeholder="Please Select" id="Experience"
                                            ng-options="ExpKey as ExpVal for (ExpKey, ExpVal) in ExperienceOptions" 
                                            ng-model="PageData.Experience"
                                            data-mandatory="true" data-msglocation="errorExperience" data-controltype="general" data-requiredmessage="Experience is required">
                                        <option value=""></option>
                                    </select-->
                                    <input type="text" class="form-control" id="Experience" placeholder="Enter Experience" 
                                               ng-model='PageData.Experience' name="Experience" 
                                               data-msglocation="errorExperience" data-mandatory="true" data-controltype="" data-requiredmessage="Experience is required" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorExperience"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field" ng-init="load_validation_controll();">
                        <td class="valign">
                            <label class="label" id="Qualification">Qualification<span class="required">*</span></label>
                        </td>
                        <td>
                            <div class="form-control" ng-init='QualificationOptions =<?php echo json_encode($this->config->item('Qualification')); ?>'>
                                <div class="text-field large" data-error="hasError">
                                    <!--select class="" chosen data-placeholder="Please Select" id="Qualification"
                                            ng-options="QKey as QVal for (QKey, QVal) in QualificationOptions" 
                                            ng-model="PageData.Qualification"
                                            data-mandatory="true" data-msglocation="errorQualification" data-controltype="general" data-requiredmessage="Qualification is required">
                                        <option value=""></option>
                                    </select-->
                                    <input type="text" class="form-control" id="Qualification" placeholder="Enter Qualification" 
                                               ng-model='PageData.Qualification' name="Qualification" 
                                               data-msglocation="errorQualification" data-mandatory="true" data-controltype="" data-requiredmessage="Qualification is required" >
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror" id="errorQualification"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>


                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field">
                        <td class="valign">
                            <label class="label" for="JobResponsibility"> Job Responsibilities </label>
                        </td>
                        <td>
                            <div class="form-control" >
                                <div class="text-field large" data-type="focus">
                                    <input type="text" class="form-control" id="JobResponsibility" placeholder="Enter Job Responsibilities" 
                                           ng-model="PageData.JobResponsibility">
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <tr ng-if="PageData.CategoryID == JobCategoryID" class="job-class-field">
                        <td class="valign">
                            <label class="label" for="KeySkill"> Key Skills </label>
                        </td>
                        <td>
                            <div class="form-control" >
                                <div class="text-field large" data-type="focus">
                                    <input type="text" class="form-control" id="KeySkill" placeholder="Enter Key Skills" 
                                           ng-model="PageData.KeySkill">
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="error-holder usrerror"></div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </td>
                    </tr>

                    <!-- .job-class-field END-->

                    <tr>
                        <td class="valign">
                            <label class="label"><?php echo lang('add_tags') . '<br> (' . lang('max_5_tags') . ')'; ?></label>
                        </td>
                        <td>
                            <div class="form-control" ng-init="InvalidMaxTag=false;">
                                <tags-input class="form-control"
                                            ng-model="PageData.Tags"
                                            on-tag-adding="page_tag_adding()" 
                                            on-tag-added="page_tag_added()" 
                                            on-tag-removed="page_tag_added()"
                                            placeholder="<?php echo lang('add_tags'); ?>"
                                            min-length="1"
                                            max-length="20"
                                            replace-spaces-with-dashes="false"
                                            remove-tag-symbol="" display-property="Name">
                                    <auto-complete source="FetchTagsMaster($query)" min-length="0" debounce-delay="0" max-results="10"></auto-complete>
                                </tags-input>
                                <span class="float-right" style="color:gray;">Max. tag character length: 20</span>
                                <div class="error-holder usrerror" style="width: auto;">{{Error.error_pagedata_tag}}</div>
                                <div class="clearfix">&nbsp;</div>
                                
                            </div>

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>

                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <?php
                            ?>
                            <div class="float-right  m-r10 relative">
                                <div class="btnloader hide">
                                    <div id="ImageThumbLoader" class="uplaodLoader">
                                        <img src="<?php echo base_url(); ?>assets/admin/img/loading22.gif" id="spinner">
                                    </div>
                                </div>
                                <button ng-if="PageData.StatusID == 10" type="submit" id="DraftClassified" class="" ng-click="DraftClassified();">Draft</button>
                                <button type="submit" id="CreatePage" class="" ng-click="SaveClassified();">Publish</button>
                            </div>
                            <a href="<?php echo base_url() ?>admin/pages/classifieds" class="cancel-link float-right m-r10"><?php echo lang('Cancel'); ?></a> 
                        </td>
                    </tr>
                </table>
            </form> 
        </div>
        <div class="clearfix"></div>
    </div>
</div>
