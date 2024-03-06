<div class="custom-modal" <?php if (!empty($AlbumGUID) && $albumMod == 'edit') { ?> ng-init="getAlbumDetails('<?php echo $AlbumGUID ?>', true); IsEdit = true" <?php } ?>>	
    <div class="title-row">
        <h4 class="label-title secondary-title">
            <div class="back-arrow-block" <?php if (!empty($AlbumGUID) && $albumMod == 'edit') { ?> ng-click="redirectToSlug('<?php echo $AlbumGUID ?>')" <?php } else { ?>  ng-click="redirectToSlug('')" <?php } ?>>
                <i class="icon-md-back-arrow"></i> {{::lang.a_back_caps}}  
            </div> 
        </h4>
    </div>
    <div class="row">
        <aside class="col-md-12 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <aside class="create-album-block">
                    <div class="create-album-left">
                        <div class="create-ablum-left-header">
                            <label><?php echo $albumHeading; ?></label>
                            <!--<div ng-init="initFineUploader('addAlbumMedia');" id="addAlbumMedia" class="btn  btn-default btn-sm btn-icon pull-right">-->
                            <div ngf-select="uploadAlbumMedias($files, $invalidFiles, 'addAlbumMedia')" multiple ngf-validate-async-fn="validateFileSize($file);" class="btn  btn-default btn-sm btn-icon pull-right">
                                <i class="icon-md-plus"></i> <?php echo $albumAddMedia; ?>
                            </div>
                        </div>
                        <ul class="create-listing" id="albummediaul">
                            <!--<li id="addAlbumMediaBtn" ng-init="initFineUploader('addAlbumMediaBtn');">-->
                            <li ngf-select="uploadAlbumMedias($files, $invalidFiles, 'addAlbumMedia')" multiple ngf-validate-async-fn="validateFileSize($file);">
                                <div class="image-holder"> </div>

                                <div class="create-album"> 
                                    <i class="icon-md-addphoto"></i> 
                                    <span ng-bind="lang.a_add_media"></span>
                                </div>
                                <div class="create-list-footer">&nbsp;</div>
                            </li>
                            
                            <li class="MediaImages" ng-repeat="( mediaKey, albumMedia ) in media track by $index" id="file-{{albumMedia.mediaIndex}}" data-rel="file-{{albumMedia.mediaIndex}}">
                                
<!--                                <div ng-hide="albumMedia.progress" class="loader" style="display: block;"></div>-->
                                
                                
                                <div ng-hide="albumMedia.progress" class="active image-holder">
<!--                                    <div class="loader loader-attach-file" style="display:block"></div>-->
                                    <div ng-if="albumMedia.progressPercentage && albumMedia.progressPercentage < 101" data-percentage="{{albumMedia.progressPercentage}}" upload-progress-bar-cs></div>
                                </div>
                                
                                
                                
                                <div ng-show="albumMedia.progress" class="remove-button-media" ng-click="removeAlbumMedia(albumMedia.mediaIndex)" data-rel="{{albumMedia.mediaIndex}}">x</div>                                       
                                <span class="media-video" ng-show="albumMedia.progress" ng-if="albumMedia.data.MediaType == 'VIDEO'"><i class="ficon-video"></i></span>
                                <div ng-show="albumMedia.progress" class="image-holder" ng-if="albumMedia.data.MediaType == 'PHOTO'" style="background-image:url('<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.data.MediaSectionAlias}}/220x220/{{albumMedia.data.ImageName}}')">
                                    <div class="action-album">
                                        <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown">
                                            <i class="icon-vbullets"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li data-type="subDropdown" data-rel="editLocation" id="loc-{{albumMedia.mediaIndex}}"><a href="javascript:void(0);" ng-bind="lang.a_add_location"></a></li>
                                            <li><a href="javascript:void(0);" class="makeCoveras" ng-if="albumMedia.data.IsCoverMedia != '1'" ng-click="setAlbumCover(mediaKey)" ng-bind="lang.a_set_album_cover"></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div ng-show="albumMedia.progress" class="image-holder" ng-if="albumMedia.data.MediaType == 'VIDEO'" ng-class="{'videoprocess':albumMedia.data.ConversionStatus != 'Finished'}" style="background-image:url('<?php echo IMAGE_SERVER_PATH ?>upload/{{albumMedia.data.MediaSectionAlias}}/220x220/{{albumMedia.data.FileName}}.jpg')">
                                    <div class="action-album">
                                        <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown">
                                            <i class="icon-vbullets"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li data-type="subDropdown" data-rel="editLocation"  id="loc-{{albumMedia.mediaIndex}}"><a href="javascript:void(0);" ng-bind="lang.a_add_location"></a></li>
                                            <li ng-if="albumMedia.data.MediaType == 'PHOTO'"><a href="javascript:void(0);" class="makeCoveras" ng-click="setAlbumCover(mediaKey)" ng-bind="lang.a_set_album_cover"></a></li>
                                        </ul>
                                    </div>
                                </div> 


                                <div ng-show="albumMedia.progress" class="create-list-footer">
                                    <textarea placeholder="Say something about this..." ng-model="albumMedia.data.Caption"></textarea>
                                    <div class="spec-sep Locations" id="location{{albumMedia.mediaIndex}}">
<!--                                        <span ng-show="( albumMedia.Location && albumMedia.Location.FormattedAddress )">
                                            -at <a class="tag"><span>{{albumMedia.Location.FormattedAddress}}</span> <i class="icon-remove" ng-click="removeMediaLocation(albumMedia.mediaIndex)"></i></a>
                                        </span>-->
                                        <span ng-if="!isEmpty(albumMedia.Location.FormattedAddress)">
                                            -at <a class="tag"><span>{{albumMedia.Location.FormattedAddress}}</span> <i class="icon-remove" ng-click="removeMediaLocation(albumMedia.mediaIndex)"></i></a>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ul>

                    </div>
                    <!--  create-album-right -->
                    <div class="create-album-right">
                        <form ng-submit="SubmitCreateAlbumForm()" method="post" id="formAlbum" name="formAlbum" class="">

                            <div class="create-album-right-inner">
                                <div class="form-group">
                                    <label ng-bind="lang.a_album_title"></label>
                                    <div data-error="hasError" class="text-field">
                                        <input id="albumnamefieldCtrlID" value="" 
                                               data-controltype="general" data-mandatory="true" 
                                               data-msglocation="errorAlbumname" 
                                               ng-model="modalbum.AlbumName"
                                               ng-blur="checkBlank()" 
                                               maxlength="50" 
                                               data-requiredmessage="Please enter album name." type="text" placeholder=""/>
                                        <label class="error-block-overlay" id="errorAlbumname"></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label ng-bind="lang.description"></label>
                                    <div data-error="hasError" class="textarea-field">
                                        <textarea 
                                            data-controltype="general" 
                                            data-mandatory="true" 
                                            data-msglocation="errorAlbumDescription" 
                                            data-requiredmessage="Please enter album description." 
                                            ng-model="modalbum.Description" 
                                            ng-blur="checkBlank()" 
                                            id="AlbumDescription" 
                                            name="AlbumDescription" 
                                            rows="5" ng-keypress="updateAlbumLen('AlbumDescription')" 
                                            maxcount="200"
                                            maxlength="200" 
                                            placeholder="Say something about this album..."></textarea>
                                        <label class="error-block-overlay" id="errorAlbumDescription"></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label ng-bind="lang.a_select_location"></label>
                                    <div data-error="hasError" class="text-field location">
                                        <i class="icon-location ficon-location f-md">&nbsp;</i>
                                        <input 
                                            id="AlbumLocation" 
                                            name="AlbumLocation" 
                                            ng-model="modalbum.Location.FormattedAddress"
                                            type="text" placeholder=""/>
                                        <label class="error-block-overlay"></label>
                                    </div>
                                </div>  
                            </div>
                            <div class="modal-footer">
                                <div class="pull-right wall-btns">
                                    <button type="button" class="btn btn-default btn-icon btn-onoff" ng-click="setIsCommentable()" ng-class="{'on':modalbum.CommentsAllowed == 1}" title="{{modalbum.CommentsAllowed==1?'Turn Comment Off':'Turn Comment On'}}" id="commentableAlbum">
                                        <i ng-class="{'icon-on':CommentsAllowed == 1,'icon-off':CommentsAllowed == 0}"  class="ficon-comment f-lg"></i>
                                    </button>
                                    <?php if ($moduleSection == 'user') { ?>
                                        <div class="btn-group custom-icondrop">
                                            <button type="button" class="btn btn-default dropdown-toggle drop-icon" data-toggle="dropdown" aria-expanded="false"> 
                                                <i ng-class="{'ficon-globe':modalbum.Visibility == '1','icon-follwers':modalbum.Visibility == '2','ficon-friends':modalbum.Visibility == '3','ficon-user':modalbum.Visibility == '4'}"></i> 
                                                <span class="caret"></span> 
                                            </button>

                                            <ul class="dropdown-menu pull-bottom-left dropdown-withicons" role="menu">
                                                <li><a href="javascript:void(0);" ng-click="setVisibility(1)"><span class="mark-icon"><i class="ficon-globe"></i></span>{{::lang.a_everyone}}</a></li>
                                                <!-- <li><a href="javascript:void(0);" ng-click="setVisibility(2)"><span class="mark-icon"><i class="icon-follwers"></i></span>Friends of Friends</a></li> -->
                                                <li><a href="javascript:void(0);" ng-click="setVisibility(3)"><span class="mark-icon"><i class="ficon-friends"></i></span>{{::lang.a_friends}}</a></li>
                                                <li><a href="javascript:void(0);" ng-click="setVisibility(4)"><span class="mark-icon"><i class="ficon-user"></i></span>{{::lang.a_only_me}}</a></li>
                                            </ul>

                                        </div>
                                    <?php } ?>
                                    <button id="createalbumbtn" class="btn btn-primary" ng-disabled=" ( modalbum.AlbumName == '' ) || ( modalbum.Description == '' ) || isAlbumMediaUploading" type="submit">{{::lang.a_save}}</button>
                                </div>
                            </div> 

                        </form>
                    </div> 
                </aside> 
            </div>
        </aside>
    </div>
</div>