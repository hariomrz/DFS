 <!--Bread crumb-->
 <div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a target="_self" href="<?php echo base_url('admin/media') ?>"><?php echo lang('Media_Content'); ?></a></li>
                    <li>/</li>
                    <li><span><?php echo lang('Media_Media'); ?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">

<div  ng-controller="mediaCtrl" id="mediaCtrl" class="container">                
<!--Info row-->
<div class="info-row row-flued">
    <h2><span id="spnh2"><?php echo lang('Media_Dashboard'); ?> <b>({{totalRecords}})</b></span></h2>
    <div class="info-row-right">
        <ul class="sub-nav matop10 media_right_filter">
            <li><a href="javascript:void(0);" ng-click="sortMedia('CreatedDate',CreatedDateOrder);" class="selected"><?php echo lang('Media_MostRecent'); ?></a></li>
            <li><a href="javascript:void(0);" ng-click="sortMedia('Size',SizeOrder);"><?php echo lang('Media_Largest'); ?></a></li>
            <li><a href="javascript:void(0);" ng-click="sortMedia('AbuseCount',AbuseCountOrder);"><?php echo lang('Media_MostFlagged'); ?></a></li>
        </ul>
    </div>
    
 </div>
<!--/Info row-->


<div class="row-flued" id="media_div">
    <div class="tabcontent">

        <div class="row-flued clearfix">
            <div class="subcategory row-flued filter-block">
                <div class="filter-region">
                    <div class="filter-tag selected-approve" ng-click="searchBy('IsAdminApproved', 1,'selected-approve');
                    getSearchBox();" 
                    ng-class="approveAct">
                        <label><?php echo lang('Media_Approved'); ?> </label><span>{{mediaSummary.totalApproved}}</span>
                    </div>

                    <div class="filter-tag selected-reject selected" ng-click="searchBy('IsAdminApproved', 0,'selected-reject');
                    getSearchBox();"
                    ng-class="unApproveAct">
                        <label><?php echo lang('Media_YetToApproved'); ?> </label><span>{{mediaSummary.totalUnapproved}}</span>
                    </div>  
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
                                <li  ng-show="showapprovebtn"><a href="javascript:void(0);" ng-click="updateMultipleMedia('approve')"><?php echo lang('Media_Approve'); ?></a></li>
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
                        <i class="icon-removed" ng-click="removeFromCriteria(criteria, $index)">&nbsp;</i>
                    </label>
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
                            <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.image_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                            </div>
                        </div>
                        
                        <div class="filter-result-list">
                            <label class="label">Video Extension</label>
                            <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.video_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
                                <label>{{extension.Name}} </label><span>{{extension.counts}}</span>
                            </div>
                        </div>
                        
                        <div class="filter-result-list">
                            <label class="label">Youtube Extension</label>
                            <div class="filter-tag selected-extensions" ng-repeat="extension in searchBox.youtube_extensions" id="extension-{{extension.MediaExtensionID}}" ng-init="extension.selected=false;" ng-click="extension.selected= !extension.selected;addToSearch('MediaExtensionID', extension,extension.selected, $index,'media_extensions');" ng-class="{'selected':extension.selected}">
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

    <div class="panel m-t">
        <div class="panel-body">
        
        <div class="row-flued">
            
            <ul class="view-listing">                
                <li ng-repeat="media in filteredMedia = (mediaList | filter:filt | orderBy:sortOrder)" id="media-{{media.MediaID}}" ng-class="{selected : isSelected(media)}" ng-init="media.indexArr=$index">
                    <img ng-src="{{media.ThumbUrl}}" alt="{{media.ImageName}}">
                    <div class="image-title">{{media.ImageName}}</div>
                    
                    <div class="category-desc" ng-click="selectCategory(media);"  ng-class="{selected : isSelected(media)}">
                        <?php if(in_array(getRightsId('media_view_event'), getUserRightsData($this->DeviceType))){ ?>
                            <a ng-if="media.MediaTypeId == <?php echo IMAGE_MEDIA_TYPE_ID; ?>" href="{{media.ImageUrl}}" class="icon-zoomlist">&nbsp;</a>
                            <a class="icon-videomedia" ng-if="media.MediaTypeId == <?php echo VIDEO_MEDIA_TYPE_ID; ?> || media.MediaTypeId == <?php echo YOUTUBE_MEDIA_TYPE_ID; ?>" href="javascript:;" ng-click="playVideo(media);">&nbsp;</a>
                        <?php } ?>
                        <i class="icon-selectlist">&nbsp;</i>
                        <p>
                            <span>{{media.MediaSection}}</span>
                            <span>
                                <a ng-click="viewUserProfile(media.UserGUID);" href="javascript:void(0);">{{media.UserName}}</a>
                            </span>

                            <span class="media-date">{{media.MediaDate}}</span>
                            <span class="media-size">{{media.MediaExtension | uppercase}} / {{media.MediaSize}}</span>
                        </p>
                        
                        <div class="desc-footer">
                            <?php if(in_array(getRightsId('media_delete_event'), getUserRightsData($this->DeviceType))){ ?>
                                <a href="javascript:void(0);" ng-click="updateMedia(media, 'delete');$event.stopPropagation();"><?php echo lang('Media_Delete'); ?></a>
                            <?php } ?>
                            <?php if(in_array(getRightsId('media_approve_event'), getUserRightsData($this->DeviceType))){ ?>
                                <a href="javascript:void(0);" ng-click="updateMedia(media, 'approve');$event.stopPropagation();" ng-show="media.IsAdminApproved==0"><?php echo lang('Media_Approve'); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                    <i class="icon-selected" ng-show="media.IsAdminApproved==1"> </i>
                </li>
                <li ng-show="shownomediarecord" class="nomediali">
                    <div class="no-media">
                        <div class="no-content text-center">
                            <p><?php echo lang('ThereIsNoHistoricalDataToShow'); ?></p>
                        </div>
                    </div>
                </li>
            </ul>     
            <div class="media_loader">
                <img id="spinner" src="<?php echo base_url(); ?>assets/admin/img/loader.gif">
                Loading...
            </div>
            <div class="popup animated " id="mediaImagePopup">
                <div class="popup-title"><i onclick="closePopDiv('mediaImagePopup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                <div class="popup-content">
                    <img ng-src="{{popup.ImageUrl}}" alt="{{popup.ImageName}}"/>
                </div>
            </div>
            <div class="popup animated " id="mediaVideoPopup">
                <div class="popup-title">Video Player Box<i onclick="closePopDiv('mediaVideoPopup', 'bounceOutUp');" class="icon-close">&nbsp;</i></div>
                <div class="popup-content">
                    <div style="height: 200px; width: 100%; border: #ccc solid 1px;">
                        <video ng-if="videoMediaStatus==1" width="100%" height="100%" controls="" class="object">
                            <source type="video/mp4" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{videoMedia.MediaSectionAlias}}/{{videoMedia.ImageName}}.mp4"></source>
                            <source type="video/ogg" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{videoMedia.MediaSectionAlias}}/{{videoMedia.ImageName}}.ogg"></source>
                            <source type="video/webm" src="" dynamic-url dynamic-url-src="<?php echo IMAGE_SERVER_PATH ?>upload/{{videoMedia.MediaSectionAlias}}/{{videoMedia.ImageName}}.webm"></source>
                           Your browser does not support HTML5 video.
                      </video>
                    </div>
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
<input type="hidden" name="mediaPageName" id="mediaPageName" value="media"/>
</div>
</section>
