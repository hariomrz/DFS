    <!--Bread crumb-->

         <div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/media') ?>"><?php echo lang('Media_Content'); ?></a></li>
                    <li>/</li>
                    <li><span><a href="<?php echo base_url('admin/media'); ?>"><?php echo lang('Media_Media'); ?></a></span></li>
                    <li>/</li>
                    <li><span><?php echo lang('Media_Abused'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div> 

    
    <!--/Bread crumb-->
<section class="main-container"> 

<div ng-controller="mediaAbuseCtrl" class="container">
    <!--Info row-->
    <div class="info-row row-flued clearfix">
        <h2><?php echo lang('Media_AbusedMedia'); ?> <b>({{totalAbuseMedia}})</b></h2>
        <div class="info-row-right">
            <ul class="sub-nav matop10">
                <li><a href="javascript:void(0);" ng-click="sortMedia('CreatedDate',CreatedDateOrder);" class="selected"><?php echo lang('Media_MostRecent'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="sortMedia('Size',SizeOrder);"><?php echo lang('Media_Largest'); ?></a></li>
                <li><a href="javascript:void(0);" ng-click="sortMedia('AbuseCount',AbuseCountOrder);"><?php echo lang('Media_MostFlagged'); ?></a></li>
            </ul>
            
            <div class="total-flagged">
                <ul id="ulFlagDetail" class="flagged-list">
                    <li class="blue"><label>{{mediaSummary}}</label><span><?php echo lang('AbusedMedia_NoOfFlaggedImages'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
    <!--/Info row-->
    <div class="panel">
        <div class="panel-body">
            <div class="row-flued" id="media_div">
                <div class="tabcontent">
                    <div class="row-flued">
                        <div class="subcategory row-flued filter-block">
                            <div class="filter-region">
                                <a href="javascript:void(0);" rel="Hide Advanced Filter" id="showHidefilter"><?php echo lang('Media_ShowAdvanceFilters'); ?></a>
                            </div>
                            <div class="info-row-right">
                                <div>
                                    <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType)) || in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                        <div id="selectallbox" class="text-field selectbox">
                                            <span>
                                                <input type="checkbox" id="selectAll" class="globalCheckbox" ng-checked="showButtonGroup" ng-click="globalCheckBox();">
                                            </span>
                                            <label for="selectAll"><?php echo lang('Select_All'); ?></label>
                                        </div>
                                    <?php } ?>
                                    <ul class="button-list items-counter marright10" id="buttonGroup">
                                        <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <li><a href="javascript:void(0);" ng-click="updateMultipleMedia('approve')"><?php echo lang('Media_Approve'); ?></a></li>
                                        <?php } ?>
                                        <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <li><a href="javascript:void(0);" ng-click="updateMultipleMedia('delete')"><?php echo lang('Media_Delete'); ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="filter-view">
                            <div class="filter-title">
                                <label ng-repeat="criteria in criteriaList">{{criteria.Name}}
                                    <i class="icon-removed" ng-click="removeFromCriteria(criteria, $index)">&nbsp;</i></label>
                            </div>
                            
                            <div class="filter-content">
                                <div class="filter-list">
                                    
                                    <div class="filter-result-list">
                                        <label class="label">Upload Devices</label>
                                        <div class="filter-tag selected-devices" ng-repeat="device in searchBox.upload_devices" id="device-{{device.DeviceID}}" ng-init="device.selected=false;" ng-click="device.selected= !device.selected;addToSearch('DeviceID', device, device.selected, $index, 'upload_devices');" ng-class="{'selected':device.selected}">
                                            <label>{{device.Name}} </label><span>{{device.counts}}</span>
                                        </div>
                                    </div>
                                    

                                    <div class="filter-result-list">
                                        <label class="label">Image Extension</label>
                                        <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.media_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                            <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="filter-result-list">
                                        <label class="label">Uploaded From</label>
                                        <div class="filter-tag selected-source" ng-repeat="mediasource in searchBox.media_source" id="mediasource-{{mediasource.SourceID}}" ng-init="mediasource.selected=false;" ng-click="mediasource.selected= !mediasource.selected;addToSearch('SourceID', mediasource, mediasource.selected, $index,'media_source');" ng-class="{'selected':mediasource.selected}">
                                            <label>{{mediasource.Name}} </label><span>{{mediasource.counts}}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="filter-list marl30">
                                    
                                    <div class="filter-result-list">
                                        <label class="label">Type of Image</label>
                                        <div class="filter-tag selected-sections" ng-repeat="sections in searchBox.media_sections" id="sections-{{sections.MediaSectionID}}" ng-init="sections.selected=false;" ng-click="sections.selected= !sections.selected;addToSearch('MediaSectionID', sections, sections.selected, $index,'media_sections');" ng-class="{'selected':sections.selected}">
                                            <label>{{sections.Name}} </label><span>{{sections.counts}}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="filter-result-list">
                                        <label class="label">Media Size</label>
                                        <div class="filter-tag selected-sizes" ng-repeat="mediasize in searchBox.media_size" id="mediasize-{{mediasize.MediaSizeID}}" ng-init="mediasize.selected=false;" ng-click="mediasize.selected= !mediasize.selected;addToSearch('MediaSizeID', mediasize, mediasize.selected, $index,'media_size');" ng-class="{'selected':mediasize.selected}">
                                            <label>{{mediasize.Name}} </label><span>{{mediasize.counts}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-footer">
                                    <input type="button" value="<?php echo lang('Media_Search'); ?>" class="button float-right" ng-click="loadMediaWithFilter()">
                                    <input type="button" value="<?php echo lang('Media_Reset'); ?>" class="button wht float-right" ng-click="resetFilter()">
                                </div>
                            </div>
                        </div>
                    </div> 
                    
                    
                    <div class="row-flued">
                        <ul class="view-listing">
                            
                            <li ng-repeat="media in filteredMedia = (mediaList | orderBy:sortOrder)" id="media-{{media.MediaID}}" ng-class="{selected : isSelected(media)}" ng-init="media.indexArr=$index">
                                
                                <img ng-src="{{media.ThumbUrl}}" alt="{{media.ImageName}}">
                                <div class="image-title">{{media.ImageName}}</div>
                                <div class="total-in"><a href="<?php echo base_url(); ?>admin/media/media_abused_detail/media_id/{{media.MediaID}}">{{media.AbuseCount}}</a></div>
                                
                                <div class="category-desc" ng-click="selectCategory(media);"  ng-class="{selected : isSelected(media)}">
                                    <?php if(in_array(getRightsId('media_view_event'), getUserRightsData($this->DeviceType))){ ?>
                                        <a href="{{media.ImageUrl}}" class="icon-zoomlist">&nbsp;</a>
                                    <?php } ?>
                                    <i class="icon-selectlist">&nbsp;</i>
                                    <p>
                                        <span>{{media.MediaSection}}</span>
                                        <span>
                                            <a ng-click="viewUserProfile(media.UserGUID);" href="javascript:void(0);">{{media.UserName}}</a>
                                        </span>
                                        <span class="media-date">{{media.AbuseDate}}</span>
                                        <span class="media-size">{{media.MediaExtension | uppercase}} / {{media.MediaSize}}</span>
                                    </p>
                                    
                                    <div class="desc-footer">
                                        <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <a href="javascript:void(0);" ng-click="updateMedia(media, 'delete');$event.stopPropagation();"><?php echo lang('Media_Delete'); ?></a>
                                        <?php } ?>
                                        <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                                            <a href="javascript:void(0);" ng-click="updateMedia(media, 'approve');$event.stopPropagation();"><?php echo lang('Media_Approve'); ?></a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </li>
                            <li ng-hide="filteredMedia.length" class="nomediali">
                                <div class="no-media">
                                    <div class="no-content text-center">
                                        <p><?php echo lang('ThereIsNoHistoricalDataToShow'); ?></p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                                        
                        <div class="popup animated " id="mediaImagePopup">
                            <div class="popup-title"><i onclick="closePopDiv('mediaImagePopup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                            <div class="popup-content">
                                <img ng-src="{{popup.ImageUrl}}" alt="{{popup.ImageName}}"/>
                            </div>
                        </div>
                        
                        <div class="popup confirme-popup animated" id="confirmeMediaPopup">
                            <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMediaPopup', 'bounceOutUp');">&nbsp;</i></div>
                            <div class="popup-content">
                                <p class="text-center">{{confirmationMessage}}</p>
                                <div class="communicate-footer text-center">
                                    <button class="button wht" onclick="closePopDiv('confirmeMediaPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                                    <button class="button" ng-click="setStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
                                </div>
                            </div>
                        </div>


                    </div>  
                </div>
            </div>
    </div>
    </div>

</div>
</section>