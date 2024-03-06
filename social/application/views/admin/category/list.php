<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span><?php echo lang('Category'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="CategoryCtrl" id="CategoryCtrl">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2">{{pageHeading}} </span> ({{totalResults}})</h2>
        
        <div class="info-row-right rightdivbox">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="label">Module</label>
                            </div>
                            <div class="col-sm-9">
                                <select  data-chosen="" ng-change="filter_module();" data-disable-search="true"  ng-options="POptions.MKey as POptions.Name for POptions in ListPrivacyOptions" data-ng-model="category.filter_module">
                                <option value=""></option>
                            </select>
                            </div> 
                        </div>
                    </div>
                    <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-3">
                                    <label class="label">Locality</label>
                                </div>
                                <div class="col-sm-9">
                                    <select  data-chosen="" ng-change="filter_module(true);" ng-options="LOptions.MKey as LOptions.Name for LOptions in LocalityOptions" data-ng-model="localityID" data-disable-search="true">
                                    <option value=""></option>
                                </select>
                                </div> 
                            </div>                         
                    </div>
                    <div class="col-sm-4">                    
                        
                        <div data-type="focus" class="text-field search-field">
                            <div class="search-block">
                                <input type="text" id="CategorySearchField" value="" ng-model="SearchKeyword" enter-press="DataList()">
                                <div class="search-remove">
                                    <i id="clearSearch" ng-click="clearCategorySearch()" class="icon-close10">&nbsp;</i>
                                </div>
                            </div> 
                            <input type="button" class="icon-search search-btn selected openClose" id="CategorySearchButton" ng-click="DataList('search')">
                        </div>
                        
                        <div class="btn-toolbar btn-toolbar-right" >
                            <button class="btn btn-default" ng-click="openCategoryListModal()">
                                <span class="icn"><i class="ficon-upload"></i></span>
                                <span class="text">Upload List</span>
                            </button>
<?php 
            if (in_array(getRightsId('category_add_event'), getUserRightsData($this->DeviceType))) {
?>
                            <button class="btn btn-default" ng-click="AddDetailsPopUp()" ng-show="userList.length != 0"><i class="ficon-plus"></i> <?php echo lang('Add'); ?></button> 
<?php
            }
?>                    
                        </div>
                    </div>
                </div>
            </div>
        
        
    </div>
    <!--/Info row-->

    <div class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover ips_table">
                <tr>
                    <th id="Name" class="ui-sort selected" ng-click="orderByField = 'C.Name';
                            reverseSort = !reverseSort;
                            sortBY('C.Name')">                           
                <div class="shortdiv sortedDown"><?php echo lang('Category'); ?><span class="icon-arrowshort">&nbsp;</span></div>
                </th>
                <!-- <th id="parent_name" class="ui-sort" ng-click="orderByField = 'parent_name'; reverseSort = !reverseSort; sortBY('parent_name')">
                    <div class="shortdiv"><?php echo lang('ParentCategory'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th> -->
                <!--<th id="Status" class="ui-sort" ng-click="orderByField = 'Status'; reverseSort = !reverseSort; sortBY('Status')">
                    <div class="shortdiv"><?php echo lang('Status'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>-->
                <th id="PCategory" class="ui-sort" ng-click="orderByField = 'P.Name';
                        reverseSort = !reverseSort;
                        sortBY('P.Name')">
                <div class="shortdiv">Parent Category<span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>

                <th id="ModuleName" class="ui-sort" ng-click="orderByField = 'ModuleName';
                        reverseSort = !reverseSort;
                        sortBY('ModuleName')">
                <div class="shortdiv"><?php echo lang('Module'); ?><span class="icon-arrowshort hide">&nbsp;</span></div>
                </th>
                <th><?php echo lang('Actions'); ?></th>
                </tr>

                <tr ng-repeat="dataList in listData[0].ObjIP|orderBy:orderByField:reverseSort">
                    <td>
                        <p data-ng-bind="dataList.name"></p>
                    </td>
                    <!-- <td>
                        <p data-ng-bind="dataList.parent_name "></p>
                    </td> -->
                    <!--<td>
                        <span data-ng-show="dataList.status_id==2"><?php echo lang('Active') ?></span>
                        <span data-ng-show="dataList.status_id!=2">Inactive</span>
                    </td>-->
                    <td>
                        <p data-ng-bind="dataList.parent_name"></p>
                    </td>
                    <td>
                        <p data-ng-bind="dataList.ModuleName"></p>
                    </td>
                    <td>
                        <a href="javascript:void(0);" ng-click="SetDetail(dataList);" class="smtp_action" onClick="smtpActionDropdown()">
                            <i class="icon-setting">&nbsp;</i>
                        </a>
                    </td>
                </tr>
            </table>
            <div id="ipdenieddiv"></div>
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">  
                <?php if (in_array(getRightsId('category_edit_event'), getUserRightsData($this->DeviceType)))
                {
                    ?>
                    <li id="ActionEdit"><a ng-click="EditDetailsPopUp()" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>
                <?php } ?>
<?php if (in_array(getRightsId('category_active_inactive_event'), getUserRightsData($this->DeviceType)))
{
    ?>
                    <!--<li id="ActionInactive" data-ng-show="currentData.status_id==2"><a ng-click="SetStatus(4);" href="javascript:void(0);"><?php echo lang('MakeInactive'); ?></a></li>
                    <li id="ActionActive" data-ng-show="currentData.status_id==4"><a ng-click="SetStatus(2);" href="javascript:void(0);"><?php echo lang('MakeActive'); ?></a></li>-->
                <?php } ?>
                <?php if (in_array(getRightsId('category_delete_event'), getUserRightsData($this->DeviceType)))
                {
                    ?>
                    <li id="ActionDelete"><a ng-click="SetStatus(3);" href="javascript:void(0);"><?php echo lang('Delete'); ?></a></li>
<?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->

        </div>

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>


    <style>
        .cus-class .from-subject{
                width: 50%;
                padding: 7px 0 0 19px;
                float: left;
        }

    </style>


    <!--Popup for add/edit IP details -->
    <div class="popup communicate animated" id="addIpPopup">
        <div class="popup-title">
            <span ng-if="currentData.category_id"><?php echo lang('EditCategory'); ?> </span>
            <span ng-if="!currentData.category_id"><?php echo lang('AddCategory'); ?> </span>
            <i class="icon-close" onClick="closePopDiv('addIpPopup', 'bounceOutUp');">&nbsp;</i>
        </div>
        <div class="popup-content loader_parent_div">
            <i class="loader_ip btn_loader_overlay"></i>
            <div class="communicate-footer row-flued cus-class">
                <input type="hidden" name="commission_guid" id="commission_guid" ng-model="currentData.category_id"/>
                <div class="row">
                <div class="col-sm-6">
                    <label for="" class="label"><?php echo lang('Module'); ?> </label>
                    <div for="" class="label" ng-if="currentData.category_id != ''">
                        <span ng-if="currentData.module_id == '1'">Group</span>
                        <span ng-if="currentData.module_id == '18'">Pages</span>
                        <span ng-if="currentData.module_id == '14'">Event</span>
                        <span ng-if="currentData.module_id == '29'">Skills</span>
                        <span ng-if="currentData.module_id == '45'">Utility & Emergency</span>
                        <span ng-if="currentData.module_id == '46'">Business & Handyman</span>
                    </div>
                    <select ng-if="currentData.category_id == ''" data-chosen="" data-disable-search="true"  ng-options="POptions.MKey as POptions.Name for POptions in PrivacyOptions" data-ng-model="currentData.module_id">
                        <option value="">Select Module</option>
                    </select>
                    <div class="error-holder" ng-show="showModuleError" style="color: #CC3300;">{{errorModuleMessage}}</div>
                </div>

                <div class="col-sm-6">
                    <label for="" class="label">Locality </label>
                    <!-- <div for="" class="label" ng-if="currentData.locality_id != ''">
                        <span ng-if="currentData.locality_id == '1'">Tulsi Nagar</span>
                        <span ng-if="currentData.locality_id == '2'">Mahalaxmi Nagar</span>
                        <span ng-if="currentData.locality_id == '14'">Event</span>
                        <span ng-if="currentData.locality_id == '29'">Skills</span>
                    </div> -->
                    <select data-chosen="" data-disable-search="true"  ng-options="LOptions.MKey as LOptions.Name for LOptions in LocalityOptions" data-ng-model="currentData.locality_id">
                    </select>
                    <div class="error-holder" ng-show="showLocalityError" style="color: #CC3300;">{{errorLocalityMessage}}</div>
                </div>
                </div>    
                <div class="row">
                <div class="col-sm-6">
                    <label for="" class="label"><?php echo lang('Category'); ?> </label>
                    <div class="text-field">
                        <input type="text" name="category" maxlength="200" data-req-maxlen="200" id="category" placeholder="<?php echo lang('EnterCategory'); ?>"  ng-model="currentData.name" maxlength="200">
                    </div>
                    <div class="error-holder" ng-show="showCategoryError" style="color: #CC3300;">{{errorCategoryMessage}}</div>
                </div>

                <div class="col-sm-6">
                    <label for="" class="label"><?php echo lang('ParentCategory'); ?> </label>
                    <div class="">
<!--                        <select class="chosen" name="parent_id" ng-model="currentData.parent_id">
                            <option value="0"><?php// echo lang('SelectParentCategory') ?></option>
                            <option ng-repeat="cat in allCategories" value="{{cat.category_id}}">{{cat.name | htmlString}}</option>
                        </select>-->{{currentData.parent_ida}}
                        <select class="w160" chosen data-disable-search="true" ng-options="cat.name for cat in allCategories track by cat.category_id" ng-model="currentData.parent_id"></select>
                    </div>
                </div>
                </div>
                <div class="row">
                <div class="col-sm-6">
                    <label for="" class="label">Address </label>
                    <div class="text-field">
                        <input type="text" name="address" maxlength="200" data-req-maxlen="200" id="address" placeholder="Enter Address" ng-model="currentData.address">
                    </div>
                    <div class="error-holder" ng-show="showAddressError" style="color: #CC3300;">{{errorAddressMessage}}</div>
                </div>

                <div class="col-sm-6">
                    <label for="" class="label">Phone Number </label>
                    <div class="text-field">
                        <!-- <input type="text" name="phone_number" maxlength="10" id="phone_number" placeholder="Enter Phone Number"  ng-model="currentData.phone_number"> -->
                        <input type="text" name="phone_number" maxlength="10" id="phone_number" placeholder="Enter Phone Number"  ng-model="currentData.phone_number" spellcheck="false" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                    </div>
                    <div class="error-holder" ng-show="showPhoneNumberError" style="color: #CC3300;">{{errorPhoneNumberMessage}}</div>
                </div>
                </div>
                <div class="row">
                <div class="col-sm-6">
                    <label for="" class="label">Owner </label>
                    <div class="text-field">
                        <input type="text" name="owner" maxlength="100" id="owner" placeholder="Enter Owner"  ng-model="currentData.owner">
                    </div>
                    <div class="error-holder" ng-show="showOwnerError" style="color: #CC3300;">{{errorOwnerMessage}}</div>
                </div>

                <div class="col-sm-6">
                    <label for="" class="label">Miscellaneous </label>
                    <div class="text-field">
                        <input type="text" name="miscellaneous" maxlength="200" id="miscellaneous" placeholder="Enter Miscellaneous"  ng-model="currentData.miscellaneous">
                    </div>
                </div>
                </div>
                <div class="row">
                <div class="col-sm-12">

                    <label for="" class="label"><?php echo lang('Description'); ?> </label>
                    <div class="text-field">
                        <textarea style="height:75px" maxlength="500" data-req-maxlen="500" name="description" id="description" placeholder="<?php echo lang('Description'); ?>"  ng-model="currentData.description" maxlength="500"></textarea>
                    </div>
                    <div class="error-holder" ng-show="showDescriptionError" style="color: #CC3300;">{{errorDescriptionMessage}}</div>
                </div>
               </div>
                <div class="row">
                <div class="col-sm-12" ng-if="!currentData.media">
                    <label for="" class="label"><?php echo lang('CategoryImage'); ?> </label>

                    <div class="upload-image">
                        <div class="button-wrapper">

                            <span class="input-group-addon" template="commentTemplate" fine-uploader upload-destination="api/upload_image" unique-id="1" image-type="category" section-type="category" upload-extensions="jpeg,jpg,gif,png,JPEG,JPG,GIF,PNG" title="Attach a Photo"></span>
                        </div> 

                    </div>
                    <ul class="attached-media" id="attached-media-1">
                        <li id='cat_img_{{value.MediaGUID}}' ng-repeat="value in currentData.media">
                            <a id='{{value.MediaGUID}}' ng-click="delete_cat_image(value)" class='smlremove' ></a>
                            <figure>
                                <img  width='98px' class='img-category-full' media_guid='{{value.MediaGUID}}' media_name='value.ImageName' media_type='IMAGE' ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{value.ImageName}}'>
                            </figure>
                            <span class='radio'></span>
                            <input type='hidden' name='MediaGUID[]' value="{{value.MediaGUID}}"/>
                        </li>
                    </ul> 
                </div>
                </div>
                <div class="row">
                <div class="col-sm-12" ng-if="currentData.media">
                    <label for="" class="label"><?php echo lang('CategoryImage'); ?> </label>

                    <div class="upload-image">
                        <div class="button-wrapper">

                            <span class="input-group-addon" template="commentTemplate" fine-uploader upload-destination="api/upload_image" unique-id="1" image-type="category" section-type="category" upload-extensions="jpeg,jpg,gif,png,JPEG,JPG,GIF,PNG" title="Attach a Photo"></span>
                        </div> 

                    </div>
                    <ul class="attached-media" id="attached-media-1">
                        <li id='cat_img_{{value.MediaGUID}}' ng-repeat="value in currentData.media">
                            <a id='{{value.MediaGUID}}' ng-click="delete_cat_image(value)" class='smlremove' ></a>
                            <figure>
                                <img  width='98px' class='img-category-full' media_guid='{{value.MediaGUID}}' media_name='value.ImageName' media_type='IMAGE' ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/category/{{value.ImageName}}'>
                            </figure>
                            <span class='radio'></span>
                            <input type='hidden' name='MediaGUID[]' value="{{value.MediaGUID}}"/>
                        </li>
                    </ul> 
                </div>
                </div>
                <div class="clearfix"></div>
            </div>        
            <button ng-click="AddEditCategory()" class="button float-right" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
            <button class="button wht float-right" ng-click="resetPopup();" onclick="closePopDiv('addIpPopup', 'bounceOutUp');">
<?php echo lang('Cancel'); ?>
            </button>
            <div class="clearfix"></div>
        </div>
    </div>
    <!--Popup end add/edit IP details -->

    <!--Popup for change ip status -->
    <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">{{confirmationMessage}}</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>   



        <div class="popup popup-sm animated removeCategory" id="removeCategoryPopup">
        <div class="popup-title">
            <i class="icon-close" onClick="closePopDiv('removeCategoryPopup', 'bounceOutUp');">&nbsp;</i>
            <div class="skill-cir">
                <span class="icn-holder ">
                    <span class="endorse-item-icon" ng-if="currentData.ImageName != ''">
                        <img height="14" width="14"   class="svg" src="{{image_path + 'category/220x220/' + currentData.ImageName}}">
                    </span>
                </span>       
            </div>
            <span class="text" ng-bind="currentData.name"></span>
        </div>
        <div class="popup-content">
            <p>You are about to remove the category, with its  
                <span> <b ng-bind="RemoveCategoryData.SubCategoryCount"></b> <span ng-if="RemoveCategoryData.SubCategoryCount <= 1">subcategory</span><span ng-if="RemoveCategoryData.SubCategoryCount >1">subcategories</span>.</span> 
                <span> <b ng-bind="RemoveCategoryData.SkillCount"></b> <span ng-if="RemoveCategoryData.SkillCount <= 1">skill</span><span ng-if="RemoveCategoryData.SkillCount >1">skills</span>.</span> 
                <span> <b ng-bind="RemoveCategoryData.EndorsementsCount"></b> <span ng-if="RemoveCategoryData.EndorsementsCount <= 1">endorsement</span><span ng-if="RemoveCategoryData.EndorsementsCount >1">endorsements</span>.</span> 
            </p>
            <p> All users will lose the skills and endorsements associated to their profile. They will receive a notification regarding this change.</p>

            <a class="remove-btn max-w266" ng-click="DaleteSkillCategory();">
                <b >Remove Category </b>
                <span>with all its sub-categories and skills</span>
            </a>    
        </div>
    </div>

    <!--Popup for change ip status -->
    
    <div ng-include="category_upload_view"></div>
</div>
</section>


