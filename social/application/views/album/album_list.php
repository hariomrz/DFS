  	<div class="custom-modal fadeInDown" ng-init="albumListing('PHOTO');">	           
        <?php if(isset($ForumMedia)){ ?>
            <div class="pages-block">
                <div class="pages-head">
                    <h4>
                        {{::lang.media_caps}}
                    </h4>
                    <button ng-if="config_detail.IsAdmin && Settings.m13 == '1'" ng-click="redirectToSlug('create')" type="button" class="btn  btn-default btn-sm btn-icon pull-right">
                        <i class="icon-md-plus"></i> {{::lang.a_create_album_caps}} 
                    </button>
                </div>
            </div>
        <?php } else { ?>
            <div class="title-row">
                <h4 class="label-title secondary-title">
                    {{::lang.media_caps}}
                    <button ng-if="config_detail.IsAdmin && Settings.m13 == '1'" ng-click="redirectToSlug('create')" type="button" class="btn  btn-default btn-sm btn-icon pull-right">
                        <i class="icon-md-plus"></i> {{::lang.a_create_album_caps}} 
                    </button>
                </h4>
            </div>
        <?php } ?>
        <div class="row">
            <aside class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="album-header">
                        <ul class="album-tab-nav" role="tablist" id="albumTabs">
                            <li class="active" ng-if="Settings.m13 == '1'">
                                <a href="#albums"   data-toggle="tab">{{::lang.a_album}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="media-wrapper">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane" id="photosOfyou">
                                <ul class="albums-listing">
                                    <li>
                                        <figure><div class="image-view"><img src="<?php echo ASSET_BASE_URL ?>img/dummy6.jpg" ></div></figure> 
                                        <div class="media-list-footer">
                                            <div class="album-like-comment">
                                                <div class="lke-cmnt-inner">
                                                    <i class="icon-md-like"></i>
                                                    <span class="count-view">5</span>
                                                </div>
                                                <div class="lke-cmnt-inner">
                                                    <i class="icon-md-comment"></i>
                                                    <span class="count-view">5</span>
                                                </div>
                                            </div>
                                            <div class="action-album">
                                                <button type="button" class="btn btn-info dropdown-toggle btn-post-action" data-toggle="dropdown">
                                                <i class="icon-vbullets"></i></button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a data-toggle="modal" data-target="#croperUpdate">{{::lang.a_set_profile_picture}}</a></li>
                                                    <li><a>{{::lang.a_set_cover_photo}}</a></li>
                                                    <li><a>{{::lang.a_delete}}</a></li>
                                                </ul>
                                            </div> 
                                        </div>
                                    </li>               
                               </ul>
                            </div>
                            <div role="tabpanel" class="tab-pane active" id="albums">
                                <ul class="albums-listing"> 
                                    <?php if(!empty($IsAdmin) || !empty($IsCreator)){ ?>
                                        <li class="create-albums" ng-if="config_detail.IsAdmin && Settings.m13 == '1'" ng-click="redirectToSlug('create')">
                                            <figure></figure>
                                            <div class="create-album">
                                                <img src="<?php echo ASSET_BASE_URL ?>img/create-media.png" > 
                                                <span>{{::lang.a_create_album}}</span> 
                                            </div> 
                                        </li>
                                    <?php } ?>

                                    <li ng-repeat="album in AlbumList" ng-click="redirectToAlbumDetails(album.AlbumGUID)">
                                        <figure ng-show="album.CoverMedia==''" ></figure> 
                                        <i ng-show="album.CoverMedia==''" ng-class="album.AlbumName=='<?php echo DEFAULT_WALL_ALBUM ?>'?'icon-md-wallpost': (album.AlbumName=='<?php echo DEFAULT_PROFILE_ALBUM ?>'?'icon-md-profilephoto': (album.AlbumName=='<?php echo DEFAULT_PROFILECOVER_ALBUM ?>'?'icon-md-coverphoto':'icon-md-untitled'))"></i>

                                        <figure ng-show="album.CoverMedia!=''"> 
                                            <div class="image-view" style=" background-image:url('<?php echo IMAGE_SERVER_PATH;?>upload/{{album.MediaSectionAlias}}/220x220/{{getAlbumCover(album.CoverMedia)}}');"></div>                 
                                        </figure> 

                                        <div class="media-list-footer">
                                            <div class="media-list-f-content">
                                                <div class="album-title ellipsis" ng-bind="album.AlbumName"></div>
                                                <span ng-if="album.AlbumName!='<?php echo DEFAULT_WALL_ALBUM ?>'"><span ng-bind="album.MediaCount"></span> {{::lang.media}}</span> 
                                                <span ng-if="album.AlbumName=='<?php echo DEFAULT_WALL_ALBUM ?>'">&nbsp;</span> 
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>  
                    </div>  
                </div>
            </aside>
        </div>
    </div>
