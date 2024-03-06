<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Album</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
    <div class="container admin-album" ng-controller="AlbumCtrl" id="AlbumCtrl">
        <!--Info row-->
        <div class="info-row row-flued rbox-pd">
            <h2><span id="spnh2">Manage Album </span></h2>

            <div class="info-row-right rightdivbox">
                <div class="row">
                    <div class="col-sm-4">

                    </div>
                    <div class="col-sm-4">
                        <!-- <a  ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                            <span class="space-icon"><i class="ficon-upload"></i></span >{{::lang.upload_new}}
                        </a> -->
                    </div>
                    <div class="col-sm-4">

                        <div class="btn-toolbar btn-toolbar-right">

                            <button class="btn btn-primary" ng-click="AddDetailsPopUp()">Create Album</button>

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
                    <!--     <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
             -->
                    <!-- Pagination -->


                    <!---------image list box2 ----------------->
                    <div class="album-type-head bhpu-album-head" ng-show="listData2.length>0"><a ng-href="album/album_list?album_type=featured">Featured</a> <a class="view-all" ng-show="listData2.length>=10" ng-href="album/album_list?album_type=featured">View All</a></div>
                    <div class="album-container bhpu-album">

                        <div class="album-box pointer" ng-click="GotoDetail(album.AlbumGUID)"  ng-repeat="album in listData2" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}})'}">
                            <!-- <img ng-if="album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}}"> -->
                            <img ng-if="!album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/default.png">
                            <div class="album-image-text-action">

                                <div class="action-auto-width-height">
                                    <div class="btn-toolbar btn-toolbar-right dropdown">
                                        <a class="btn btn-xs btn-default btn-icn user-action" data-toggle="dropdown" data-target=".userActiondropdown" role="button" aria-expanded="false" ng-click="SetDetail(album);" onClick="userActiondropdown()">
                                            <span class="icn">...</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="album-image-text-title"><a ng-href="album/album_detail?album_guid={{album.AlbumGUID}}">{{album.AlbumName}}</a></div>
                            <div class="album-image-text-desc">{{(album.MediaCount)?album.MediaCount:0}} Photos</div>
                        </div>
                    </div>
                    <!---------image list box2 ------------------->
                    <!---------image list box3 ----------------->
                    <div class="album-type-head bhpu-album-head" ng-show="listData3.length>0"><a ng-href="album/album_list?album_type=popular">Popular</a> <a class="view-all" ng-show="listData3.length>=10" ng-href="album/album_list?album_type=popular">View All</a></div>
                    <div class="album-container bhpu-album">

                        <div class="album-box pointer" ng-click="GotoDetail(album.AlbumGUID)" ng-repeat="album in listData3" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}})'}">
                            <!-- <img ng-if="album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}}"> -->
                            <img ng-if="!album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/default.png">
                            <div class="album-image-text-action">

                                <div class="action-auto-width-height">
                                    <div class="btn-toolbar btn-toolbar-right dropdown">
                                        <a class="btn btn-xs btn-default btn-icn user-action" data-toggle="dropdown" data-target=".userActiondropdown" role="button" aria-expanded="false" ng-click="SetDetail(album);" onClick="userActiondropdown()">
                                            <span class="icn">...</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="album-image-text-title"><a ng-href="album/album_detail?album_guid={{album.AlbumGUID}}">{{album.AlbumName}}</a></div>
                            <div class="album-image-text-desc">{{(album.MediaCount)?album.MediaCount:0}} Photos</div>
                        </div>
                    </div>
                    <!---------image list box3 ------------------->
                    <!---------image list box ----------------->
                    <div ng-show="listData.length>0" class="album-type-head bhpu-album-head"><a ng-href="album/album_list?album_type=new">All </a> <a class="view-all" ng-show="listData.length>=10" ng-href="album/album_list?album_type=all">View All </a></div>
                    <div class="album-container bhpu-album">

                        <div class="album-box pointer" ng-click="GotoDetail(album.AlbumGUID)" ng-repeat="album in listData" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}})'}">
                            <!-- <img ng-if="album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}}"> -->
                            <img ng-if="!album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/default.png">

                            <div class="album-image-text-action">

                                <div class="action-auto-width-height">
                                    <div class="btn-toolbar btn-toolbar-right dropdown">
                                        <a class="btn btn-xs btn-default btn-icn user-action" data-toggle="dropdown" data-target=".userActiondropdown" role="button" aria-expanded="false" ng-click="SetDetail(album);" onClick="userActiondropdown()">
                                            <span class="icn">...</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="album-image-text-title"><a ng-href="album/album_detail?album_guid={{album.AlbumGUID}}">{{album.AlbumName}}</a></div>
                            <div class="album-image-text-desc">{{(album.MediaCount)?album.MediaCount:0}} Photos</div>
                        </div>
                    </div>
                    <!---------image list box ------------------->
                    <!---------image list box4 ----------------->
                    <!-- <div class="album-type-head"><a ng-href="album/album_list?album_type=all">All</a> <a class="view-all" ng-href="album/album_list?album_type=all">View All</a></div>
            <div class="album-container">
                    
                <div class="album-box" ng-repeat="album in listData4" > 
                    <img ng-if="album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.CoverMedia}}">
                    <img ng-if="!album.CoverMedia" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/default.png">
                    <div class="album-image-text-action">
                        
                        <div class="action-auto-width-height">
                           <div class="btn-toolbar btn-toolbar-right dropdown">
                                <a class="btn btn-xs btn-default btn-icn user-action" 
                                    data-toggle="dropdown" 
                                    data-target=".userActiondropdown"
                                    role="button" aria-expanded="false" 
                                    ng-click="SetDetail(album);" onClick="userActiondropdown()">
                                    <span class="icn">...</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="album-image-text-title"><a ng-href="album/album_detail?album_guid={{album.AlbumGUID}}">{{album.AlbumName}}</a></div>
                    <div class="album-image-text-desc">{{(album.MediaCount)?album.MediaCount:0}} Photos</div>
                </div>
            </div> -->
                    <!---------image list box4 ------------------->

                    <div id="ipdenieddiv"></div>
                    <!-- Pagination -->
                    <!-- <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
             -->
                    <!-- Pagination -->

                </div>
            </div>



            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu  dropdown-menu-right userActiondropdown useralbum-dropdwn" style="display: none;">
                <li id="ActionChangePwd" ng-hide="currentData.IsFeatured == 1">
                    <a href="javascript:void(0);" ng-click="markFeature();">
                        Set Featured
                    </a>
                </li>
                <li id="ActionChangePwd" ng-hide="currentData.IsFeatured == 0">
                    <a href="javascript:void(0);" ng-click="removeFeature();">
                        Remove Featured
                    </a>
                </li>
                <li id="ActionBlock" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1">
                    <a ng-click="EditDetailsPopUp()" href="javascript:void(0);">
                        Edit Name & Description
                    </a>
                </li>

                <li id="ActionChangePwd" ng-class="{'active': currentData.Visibility == 1}">
                    <a href="javascript:void(0);" ng-click="setVisibility(1);">
                        Visible to All
                    </a>
                </li>
                <li id="ActionChangePwd" ng-class="{'active': currentData.Visibility == 2}">
                    <a href="javascript:void(0);" ng-click="setVisibility(2);">
                        Visible to only admin
                    </a>
                </li>
                <li id="ActionChangePwd" ng-class="{'active': currentData.Visibility == 3}">
                    <a href="javascript:void(0);" ng-click="setVisibility(3);">
                        Visible to none
                    </a>
                </li>
                <li id="ActionChangePwd">
                    <a href="javascript:void(0);" ng-click="DeleteAlbum();">
                        Delete Album
                    </a>
                </li>



            </ul>
            <!--/Actions Dropdown menu-->

            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
                <?php if (in_array(getRightsId('category_edit_event'), getUserRightsData($this->DeviceType))) {
                ?>
                    <li id="ActionEdit"><a ng-click="EditDetailsPopUp()" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>
                <?php } ?>
                <?php if (in_array(getRightsId('category_active_inactive_event'), getUserRightsData($this->DeviceType))) {
                ?>
                    <!--<li id="ActionInactive" data-ng-show="currentData.status_id==2"><a ng-click="SetStatus(4);" href="javascript:void(0);"><?php echo lang('MakeInactive'); ?></a></li>
                    <li id="ActionActive" data-ng-show="currentData.status_id==4"><a ng-click="SetStatus(2);" href="javascript:void(0);"><?php echo lang('MakeActive'); ?></a></li>-->
                <?php } ?>
                <?php if (in_array(getRightsId('category_delete_event'), getUserRightsData($this->DeviceType))) {
                ?>
                    <li id="ActionDelete"><a ng-click="SetStatus(3);" href="javascript:void(0);"><?php echo lang('Delete'); ?></a></li>
                <?php } ?>
            </ul>
            <!--/Actions Dropdown menu-->

        </div>

        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>


        <style>
            .cus-class .from-subject {
                width: 50%;
                padding: 7px 0 0 19px;
                float: left;
            }
        </style>


        <!--Popup for add/edit IP details -->
        

        <div class="modal fade" id="addIpPopup" ng-cloak data-backdrop="static">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close dis-cret-m" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-close" ng-click="CloseAlbumPopup();"></i></span></button>
                        <h4 ng-if="currentData.AlbumGUID">Edit Album</h4>
                        <h4 ng-if="!currentData.AlbumGUID">Create Album</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup-content loader_parent_div bhpu-alb-mdl">
                            <i class="loader_ip btn_loader_overlay"></i>
                            <div class="left-box-create" ng-class="{'hauto': currentData.AlbumGUID}">

                                <!---add cover iage -->
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="" class="label" ng-if="!currentData.AlbumGUID">Add Cover Photo</label>
                                        <label for="" class="label" ng-if="currentData.AlbumGUID">Change Cover Photo </label>
                                        <img ng-if="currentData.CoverMedia" class="view-curn-img" src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{currentData.CoverMedia}}">
                                        <div class="upload-image bhopu-upload" id="uploadimageBTN">
                                            <div class="button-wrapper">
                                            <a class="btn btn-primary"  ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                                                Upload
                                            </a>
                                                <!-- <span class="input-group-addon" template="commentTemplate" fine-uploader upload-destination="api/upload_image" unique-id="1" image-type="album" section-type="album" upload-extensions="jpeg,jpg,gif,png,JPEG,JPG,GIF,PNG" title="Attach a Photo"></span> -->
                                            </div>

                                        </div>
                                        <ul class="attached-media" id="attached-media-1" ng-if="isLoadingImage">
                                            <li><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='../assets/admin/img/loading22.gif'  id='spinner'></div></div></li>
                                        </ul>
                                        <ul class="attached-media" id="attached-media-1" ng-if="CreateAlbumData !== ''">
                                            <li>
                                                <a ng-click="delete_album_image()" class='smlremove'></a>
                                                <figure>
                                                    <img width='98px' class='img-category-full'   ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{CreateAlbumData.ImageName}}'>
                                                </figure>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!--------------------->

                            </div>
                            <div class="communicate-footer row-flued cus-class right-box-create">
                                <input type="hidden" name="commission_guid" id="commission_guid" ng-model="currentData.album_id" />

                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="" class="label">Album Name </label>
                                        <div class="text-field">
                                            <input type="text" name="album_name" maxlength="100" id="owner" placeholder="Enter Album Name" ng-model="AlbumNewName">
                                        </div>
                                        <div class="error-holder" ng-show="showAlbumNameError" style="color: #CC3300;">{{errorAlbumNameMessage}}</div>
                                    </div>


                                </div>
                                <div class="row">
                                    <div class="col-sm-12">

                                        <label for="" class="label"><?php echo lang('Description'); ?> </label>
                                        <div class="text-field">
                                            <textarea style="height:75px" maxlength="500" data-req-maxlen="500" name="description" id="description" placeholder="<?php echo lang('Description'); ?>" ng-model="currentData.Description" maxlength="500"></textarea>
                                        </div>
                                        <div class="error-holder" ng-show="showDescriptionError" style="color: #CC3300;">{{errorDescriptionMessage}}</div>
                                    </div>
                                </div>

                                <div class="clearfix"></div>
                            </div>
                            <button ng-click="AddEditAlbum()" class="button float-right dis-cret-m" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
                            <button class="button wht float-right dis-cret-m" ng-click="CloseAlbumPopup();">
                                <?php echo lang('Cancel'); ?>
                            </button>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
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
                            <img height="14" width="14" class="svg" src="{{image_path + 'album/220x220/' + currentData.ImageName}}">
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
                    <b>Remove Category </b>
                    <span>with all its sub-categories and skills</span>
                </a>
            </div>
        </div>

        <!--Popup for change ip status -->

        <div ng-include="category_upload_view"></div>
    </div>
</section>