<?php
$album_guid = ($_GET['album_guid']) ? $_GET['album_guid'] : '';
?>
<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Album Detail</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
    <div class="container admin-album" ng-controller="AlbumPhotoListCtrl" id="AlbumPhotoListCtrl">
        <!--Info row-->
        <div class="info-row row-flued rbox-pd">
            <h2 class="max-wid-overflow"><span id="spnh2">{{albumPrevData.AlbumName}}</span></h2>

            <div class="info-row-right rightdivbox sm">
                <div class="row">
                    <div  class="col-sm-6">
                    <select   data-disable-search="true"  data-chosen="" ng-change="sortBY();" ng-options="o.value as o.label for o in sortingList" data-ng-model="sortByOption" ng-init=" sortByOption = sortingList[0].value"  >
                                <option value=""></option>
                            </select> 
                    </div>
                    <div class="col-sm-6">

                        <div class="btn-toolbar btn-toolbar-right">

                            <!-- <span id="CheckBtnShow" class="xinput-group-addon" style="margin-left:10px" template="commentTemplate" fine-uploader upload-destination="api/upload_image" unique-id="1" image-type="album" section-type="album" upload-extensions="jpeg,jpg,gif,png,JPEG,JPG,GIF,PNG" title="Add Photo"></span> -->
                            
                            <!-- <button class="btn btn-primary" ng-click="AddPhotoPopUp()">Add Photo</button> -->
                            <a class="btn btn-primary" ngf-select="uploadWallFiles($files, $invalidFiles)" multiple ngf-validate-async-fn="validateFileSize($file);" class="btn btn-default"  accept="image/*">Add Photo</a>
                            <button class="btn btn-default" onClick="window.history.back();">Back</button>
                            <!-- <div class="info-row-right"><a href="javascript:void(0);" class="btn-link" ><span><?php echo lang('Back'); ?></span></a> -->

                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!--/Info row-->

        <div class="row-flued" ng-cloak>

            <!-- Pagination -->
            <!--     <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
             -->
            <!-- Pagination -->
            <!---------image list box ----------------->
            <div class="album-container-bhpu" infinite-scroll="getAlbumMediaList();" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">
                <div class="album-container-bhpu">
                    <div class="row" ng-repeat="(key,album) in AlbumMediaDataList">
                        <div class="panel panel-secondary col-sm-8">
                            <div class="panel-body">

                                <div class="album-detail-left-box">
                                    <div class="ad-left-box-header smd">
                                        <div class="btn-toolbar btn-toolbar-right">



                                            <a uib-tooltip="Send Notification" tooltip-append-to-body="true" ng-click="sendNotificationAlbum(album.MediaGUID)" class="btn btn-xs btn-icn btn-default btn-mr">
                                                <span class="icn">
                                                    <i class="ficon-notification"></i>
                                                </span>
                                            </a>

                                            <a uib-tooltip="Delete Media" tooltip-append-to-body="true" ng-click="deleteAlbumConfrim(album.MediaGUID)" class="btn btn-xs btn-icn btn-default btn-mr">
                                                <span class="icn">
                                                    <i class="ficon-bin"></i>
                                                </span>
                                            </a>
                                            <a class="btn btn-xs btn-icn btn-default verify-btn btn-mr" uib-tooltip="Verify" tooltip-append-to-body="true" ng-if="album.Verified == 0" ng-click="VerifyAlbum(album.MediaGUID,album.Verified,key)">

                                                <span class="icn"><i class="ficon-doubletick "></i></span>
                                            </a>
                                            <a class="btn btn-xs btn-icn btn-default btn-mr verify-btn active" uib-tooltip="Unverify" tooltip-append-to-body="true" ng-if="album.Verified != 0" ng-click="VerifyAlbum(album.MediaGUID,album.Verified,key)">

                                                <span class="icn"><i class="ficon-doubletick "></i></span>
                                            </a>


                                        </div>
                                    </div>
                                    <div class="bhpou-albm-detail-picard-blur-wrap">

                                        <div class="bhpou-albm-detail-picard-blur" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.ImageName}})'}">


                                        </div>
                                        <div class="bhpou-albm-detail-picard" ng-style="{'background-image':'url(<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.ImageName}})'}">
                                        </div>
                                    </div>

                                    <!-- <img src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.ImageName}}"> -->
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-secondary col-sm-4">
                            <div class="panel-body">
                                <div class="album-detail-right-box">
                                    <div class="right-box-head">
                                        <img src="<?php echo IMAGE_SERVER_PATH; ?>upload/profile/{{album.ProfilePicture}}">
                                        <div class="album-detail-right-box-desc">
                                            <a>
                                                {{album.FirstName}} {{album.LastName}}
                                            </a>
                                            <div class="album-media-desc">
                                                {{album.Description | limitTo: 150 }}  
                                                <a ng-click="ReadMoreAlbum(album)" ng-show="album.Description.length>151" class="read-more-alb-desc">Read More...</a>
                                                
                                            </div>
                                        </div>





                                    </div>
                                    <div class="right-box-detail">

                                    </div>
                                    <div class="right-box-bottom-action">
                                        <span class="right-box-album-button pointer" ng-click="ChangeAlbumPopUp(album)">Change Album</span>
                                        <span class="right-box-album-button pointer" ng-click="ChangeLocationPopUp(album)">Update Description / Location</span>
                                        <span class="right-box-album-button pointer" ng-if="album.IsCoverMedia != 0" ng-click="SetCoverMedia(album.MediaGUID, album)">Remove as Album Cover Picture</span>
                                        <span class="right-box-album-button pointer" ng-if="album.IsCoverMedia == 0" ng-click="SetCoverMedia(album.MediaGUID, album)">Set as Album Cover Picture</span>
                                        <span class="right-box-album-button pointer" ng-click="setPersonaData(album.UserID, album.UserGUID, album.FirstName+''+album.LastName);">User Persona</span>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <img src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{album.ImageName}}">
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
                    <div class="album-image-text-title">{{album.AlbumName}}</div>
                    <div class="album-image-text-desc">{{album.Description}}</div> -->
                    </div>
                </div>
                <div ng-if="questionDataListLoader" class="panel panel-primary">
                    <div class="panel-body extra-block">
                        <span class="loader text-lg" style="display:block;">&nbsp;</span>
                    </div>
                </div>
                <!---------image list box ------------------->

                <div id="ipdenieddiv"></div>
                <!-- Pagination -->
                <!--   <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
             -->
                <!-- Pagination -->





                <!--Actions Dropdown menu-->
                <ul class="dropdown-menu  dropdown-menu-right userActiondropdown" style="display: none;">
                    <li id="ActionChangePwd" ng-hide="currentData.IsFeatured == 1">
                        <a href="javascript:void(0);" ng-click="markFeature();">
                            Delete Photo
                        </a>
                    </li>

                    <li id="ActionBlock" ng-hide="currentUserRoleId.indexOf('<?php echo ADMIN_ROLE_ID; ?>') > -1">
                        <a ng-click="EditDetailsPopUp()" href="javascript:void(0);">
                            Change Album
                        </a>
                    </li>

                    <li id="ActionChangePwd" ng-hide="currentData.Visibility == 1">
                        <a href="javascript:void(0);" ng-click="setVisibility(1);">
                            Location
                        </a>
                    </li>
                    <li id="ActionChangePwd" ng-hide="currentData.Visibility == 2">
                        <a href="javascript:void(0);" ng-click="setVisibility(2);">
                            Set as album cover
                        </a>
                    </li>

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


            <!--Popup for change album media -->
            <div class="popup communicate animated" id="addIpPopup">
                <div class="popup-title">
                    <span>Change Album</span>
                    <i class="icon-close" onClick="closePopDiv('addIpPopup', 'bounceOutUp');">&nbsp;</i>
                </div>
                <div class="popup-content loader_parent_div move-album">
                    <div class="row">
                        <div class="col-sm-12">
                            <i class="loader_ip btn_loader_overlay"></i>
                            <div class="left-box-create sm">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <img src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{albumPrevData.CoverMedia}}">
                                    </div>
                                </div>
                                <!--------------------->

                            </div>
                            <div class="communicate-footer row-flued cus-class right-box-create">
                                <input type="hidden" name="commission_guid" id="commission_guid" ng-model="currentData.album_id" />

                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="" class="label">Current Album </label>
                                        <div class="text-field">
                                            <input type="text" name="album_name" maxlength="100" id="album_namea" placeholder="Enter Album Name" ng-model="albumPrevData.AlbumName" ng-disabled="true">
                                        </div>
                                        <div class="error-holder" ng-show="showAlbumNameError" style="color: #CC3300;">{{errorAlbumNameMessage}}</div>
                                    </div>


                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="left-box-create sm">
                                <img src="<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{SelectedAlbumData.CoverMedia}}">
                            </div>



                            <div class="xcommunicate-footer row-flued cus-class right-box-create">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="" class="label">Move to album </label>
                                    </div>
                                </div>
                                <!-- <tags-input ng-model="PostedByLookedMore" max-tags="1" display-property="AlbumName" key-property="AlbumGUID" placeholder="Search Album" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="removeOwnershipInfoById($tag.ModuleEntityID);" on-tag-added="addOwnershipInfoById(mayank);" on-tag-adding="addOwnershipInfoById(mayank);" multiple="false">
                                <auto-complete source="loadSearchUsers($query)" min-length="0" max-length="1" load-on-focus="true" load-on-empty="false" max-results-to-show="10"></auto-complete>
                            </tags-input> -->
                                <div class="mv-alb-ngtags">
                                    <tags-input ng-disabled="isEmptyAlbum" ng-model="PostedByLookedMore" display-property="AlbumName" on-tag-added="getSelectedAlbum($tag)" on-tag-removed="removeSelectedAlbum()" placeholder="Search Album" addOnEnter="false" template="tag7124" ng-class="isEmptyAlbum ? 'remove-place': ''">
                                        <auto-complete source="loadSearchAlbum($query)" load-on-focus="true" min-length="0" max-length="1" max-results-to-show="150"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="tag7124">
                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                <span ng-if="data.AlbumName != ''" ng-bind="data.AlbumName"></span>
                                <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                </div>
                            </script>
                                    <a ng-if="isEmptyAlbum" class="remove-cus-alb" ng-click="removeSelectedAlbum()"><i class="icon-close"></i></a>
                                </div>

                            </div>
                        </div>
                    </div>


                    <button ng-click="ChangeAlbumMedia()" ng-disabled="isMoveAlbum" class="button float-right" type="submit" id="btnSaveIp">Move</button>
                    <button class="button wht float-right" ng-click="resetPopupAlbum();" onclick="closePopDiv('addIpPopup', 'bounceOutUp');">
                        <?php echo lang('Cancel'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!--Popup end change album media -->

            <!--Popup for change location media -->
            <div class="popup communicate animated" id="locationPopup">
                <div class="popup-title">
                    <span>Update Description / Location</span>
                    <i class="icon-close" onClick="closePopDiv('locationPopup', 'bounceOutUp');">&nbsp;</i>
                </div>
                <div class="popup-content loader_parent_div">

                    <div class="communicate-footer row-flued cus-class xright-box-create">
                        <input type="hidden" name="commission_guid" id="commission_guid" ng-model="currentData.album_id" />
                        <div class="row">
                            <div class="col-sm-12">

                                <label for="" class="label"><?php echo lang('Description'); ?> </label>
                                <div class="text-field">
                                    <textarea style="height:75px" name="description" id="description" placeholder="<?php echo lang('Description'); ?>" ng-model="currentData.Description"></textarea>
                                </div>
                                <div class="error-holder" ng-show="showDescriptionError" style="color: #CC3300;">{{errorDescriptionMessage}}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 mB20">
                                <label for="" class="label">Location </label>
                                <div class="text-field">
                                    <input type="text" name="album_name" maxlength="100" id="owner" placeholder="Enter Location" ng-model="currentData.Location">
                                </div>
                                <div class="error-holder" ng-show="showAlbumNameError" style="color: #CC3300;">{{errorAlbumNameMessage}}</div>
                            </div>


                        </div>



                        <div class="clearfix"></div>
                    </div>
                    <button ng-click="ChangeLocationMedia()" class="button float-right" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
                    <button class="button wht float-right" ng-click="resetPopup();" onclick="closePopDiv('locationPopup', 'bounceOutUp');">
                        <?php echo lang('Cancel'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!--Popup end change location media -->
            <!--Popup for add photo -->
            <div class="modal fade" id="addPhotoAlbum" ng-cloak data-backdrop="static">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close dis-cret-m" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true"><i class="icon-close" ng-click="resetPhotoPopup();"></i></span>
                            </button>
                            <h4 class="mB0">Add Photo</h4>

                        </div>
                        <div class="modal-body">
                            <div class="popup-content mdm loader_parent_div bhpu-alb-mdl ">

                                <div class="left-box-create mdm">
                                    <div class="row">
                                        <div class="col-sm-12">

                                            <ul class="attached-album-list" id="attached-media-1">
                                                <li id='cat_img_{{value.MediaGUID}}' ng-repeat="value in SelectedPhotoList">
                                                    <figure>
                                                        <img width='98px' class='img-category-full' media_guid='{{value.MediaGUID}}' media_name='value.ImageName' media_type='IMAGE' ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{value.ImageName}}'>
                                                    </figure>
                                                    <div class="albm-pic-detail">

                                                        <div>
                                                            <label for="" class="label"><?php echo lang('Description'); ?> </label>
                                                            <div class="text-field">
                                                                <textarea style="height:75px" name="description" id="description{{$index}}" placeholder="<?php echo lang('Description'); ?>" ng-model="value.Description"></textarea>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label for="" class="label">Location </label>
                                                            <div class="text-field">
                                                                <input type="text" name="album_location" maxlength="100" id="owner{{$index}}" placeholder="Enter Location" ng-model="value.Location">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul class="attached-media full-loader" id="attached-media-1" ng-if="isWallAttachementUploading">
                                                <li>
                                                    <div class='loader-box'>
                                                        <div id='ImageThumbLoader' class='uplaodLoader'><img src='../../assets/admin/img/loading22.gif' id='spinner'></div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>

                                <button ng-click="AddEditAlbum()" class="button float-right dis-cret-m" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
                                <button class="button wht float-right dis-cret-m" ng-click="resetPhotoPopup();" onclick="closePopDiv('addphotoPopup', 'bounceOutUp');">
                                    <?php echo lang('Cancel'); ?>
                                </button>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
             <!--Popup for Read more text -->
             <div class="popup communicate animated" id="ReadMorePopup">
                <div class="popup-title">
                    <span>{{ReadMoreText.FirstName}} {{ReadMoreText.LastName}}</span>
                    <i class="icon-close" onClick="closePopDiv('ReadMorePopup', 'bounceOutUp');">&nbsp;</i>
                </div>
                <div class="popup-content loader_parent_div">
                <div class="read-more-wraping">
                {{ReadMoreText.Description}}
                </div>
                   
                    
                </div>
            </div>
            <!--Popup end Read more text -->

            <!-- <div class="popup communicate animated" id="addphotoPopup">
                <div class="popup-title">
                    <span>Add Photo</span>
                    <i class="icon-close" ng-click="resetPhotoPopup();" onClick="closePopDiv('addphotoPopup', 'bounceOutUp');">&nbsp;</i>
                </div>
                <div class="popup-content mdm loader_parent_div bhpu-alb-mdl ">

                    <div class="left-box-create mdm">
                        <div class="row">
                            <div class="col-sm-12">

                                <ul class="attached-album-list" id="attached-media-1">
                                    <li id='cat_img_{{value.MediaGUID}}' ng-repeat="value in SelectedPhotoList">
                                        <figure>
                                            <img width='98px' class='img-category-full' media_guid='{{value.MediaGUID}}' media_name='value.ImageName' media_type='IMAGE' ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/album/{{value.ImageName}}'>
                                        </figure>
                                        <div class="albm-pic-detail">

                                            <div>
                                                <label for="" class="label"><?php echo lang('Description'); ?> </label>
                                                <div class="text-field">
                                                    <textarea style="height:75px" name="description" id="description{{$index}}" placeholder="<?php echo lang('Description'); ?>" ng-model="value.Description"></textarea>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="" class="label">Location </label>
                                                <div class="text-field">
                                                    <input type="text" name="album_location" maxlength="100" id="owner{{$index}}" placeholder="Enter Location" ng-model="value.Location">
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <ul class="attached-media full-loader" id="attached-media-1" ng-if="isWallAttachementUploading">
                                    <li>
                                        <div class='loader-box'>
                                            <div id='ImageThumbLoader' class='uplaodLoader'><img src='../../assets/admin/img/loading22.gif' id='spinner'></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <button ng-click="AddEditAlbum()" class="button float-right" type="submit" id="btnSaveIp"><?php echo lang('Submit'); ?></button>
                    <button class="button wht float-right" ng-click="resetPhotoPopup();" onclick="closePopDiv('addphotoPopup', 'bounceOutUp');">
                        <?php echo lang('Cancel'); ?>
                    </button>
                    <div class="clearfix"></div>
                </div>
            </div> -->
            <!--Popup end add photo -->




        </div>
        <?php $this->load->view('admin/crm/users_options_models'); ?>

    <div ng-controller="UserListCtrl" id="UserListCtrl">
    <?php $this->load->view('admin/users/persona/user_persona'); ?>
    </div>
</section>
<input type="hidden" name="album_guid" id="album_guid" value="<?php echo $album_guid; ?>" />