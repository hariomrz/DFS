<div class="post-preview-popup" ng-show="ShowPreview=='1'" ng-cloak>
    <div class="modal-header">
        <button type="button" class="close" ng-click="backEditMode();"> <span><i class="ficon-cross"></i></span> </button>
        <h4 class="modal-title">Post Preview</h4>
    </div>
    <div class="news-feed-listing">
        <div class="feed-body"> 
            <div class="list-items-sm">
               <div class="list-inner">
                    <figure class="thumb-48"><img id="PreviewImage" err-name="{{LoggedInFirstName+' '+LoggedInLastName}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{LoggedInPicture}}"  /></figure>
                    <div class="list-item-body">
                    <div class="user-info">
                        <a class="user-name" id="PreviewName"></a>
                        <div>

                            <ul class="sub-navigation">
                            <li ng-bind="getCurrentTime()"></li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 0 ) && !previewInGroupWall && ( showPrivacyPreview == '1' ) )" ng-cloak>
                                <i class="ficon-globe" data-toggle="tooltip" data-placement="top" data-original-title="{{ selectedPrivacyTooltip }}">&nbsp;</i>
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 0 ) && !previewInGroupWall && ( showPrivacyPreview == '2' ) )" ng-cloak>
                                <i class="icon-follwers" data-toggle="tooltip" data-placement="top" data-original-title="{{ selectedPrivacyTooltip }}">&nbsp;</i>
                                
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 0 ) && !previewInGroupWall && ( showPrivacyPreview == '3' ) )" ng-cloak>
                                <i class="icon-frnds" data-toggle="tooltip" data-placement="top" data-original-title="{{ selectedPrivacyTooltip }}">&nbsp;</i>
                                
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 0 ) && !previewInGroupWall && ( showPrivacyPreview == '4' ) )" ng-cloak>
                                <i class="ficon-user" data-toggle="tooltip" data-placement="top" data-original-title="{{ selectedPrivacyTooltip }}">&nbsp;</i>
                                
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 1 ) && ( tagsto[0].ModuleID == 1 ) && ( tagsto[0].Privacy < 2 ) && !previewInGroupWall )" ng-cloak>
                                <i class="ficon-globe" data-toggle="tooltip" data-placement="top" data-original-title="Visible to: Everyone">&nbsp;</i>
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && previewInGroupWall && ( GroupDetails.IsPublic < 2 ) )" ng-cloak>
                                <i class="ficon-globe" data-toggle="tooltip" data-placement="top" data-original-title="Visible to: Everyone">&nbsp;</i>
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length == 1 ) && ( ( tagsto[0].ModuleID == 3 ) || ( ( tagsto[0].ModuleID == 1 ) && ( tagsto[0].Privacy == 2 ) ) ) && !previewInGroupWall )">
                                <span data-toggle="tooltip" data-placement="top" data-original-title="Visible to: only members of {{ ( ( tagsto[0].ModuleID == 1 ) && ( tagsto[0].Type == 'FORMAL' ) ) ? tagsto[0].name : 'this group' }}">
                                    <i class="ficon-member-group"></i>
                                </span>
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && previewInGroupWall && ( GroupDetails.IsPublic == 2 ) )">
                                <span data-toggle="tooltip" data-placement="top" data-original-title="Visible to: only members of {{ ( GroupDetails.Type == 'FORMAL' ) ? GroupDetails.GroupName : 'this group'; }}">
                                    <i class="ficon-member-group"></i>
                                </span>
                            </li>
                            <li ng-if="(config_detail.ModuleID!='14' && ( tagsto.length > 1 ) && !previewInGroupWall )">
                                <span data-toggle="tooltip" data-placement="top" data-original-title="Visible to: only members of this group">
                                    <i class="ficon-member-group"></i>
                                </span>
                            </li>
                            </ul>
                        </div>
                    </div> 
                   </div> 
                </div> 
            </div>  
            <div class="feed-content">
                <div id="PostTypeTitle" class="post-type-title"></div>
                <span id="PostTypeContent"></span>
            </div>
            <div class="network-wrapper network-scroll mCustomScrollbar" ng-if="parseLinks.length>0" ng-cloak>
                <div class="network-view">
                    <div ng-repeat="parseLink in parseLinks" repeat-done="callScrollBar();" class="network-block clearfix">
                        <a ng-click="removeParseLink(parseLink.URL)" class="removeNerwork">
                            <i class="ficon-cross"></i>
                        </a>
                        <div class="network-media-block networkmediaList">
                            <div class="slider-wrap">
                                <ul class="networkmedia" data-uix-bxslider="mode: 'horizontal', pager: false, controls: true, minSlides: 1, maxSlides:1, slideWidth: 170, slideMargin:0, infiniteLoop: false, hideControlOnEnd: false" ng-show="parseLink.Thumbs.length>0">
                                    <li ng-repeat="img in parseLink.Thumbs" notify-when-repeat-finished>
                                        <img err-SRC="{{AssetBaseUrl}}img/profiles/user_default.jpg" ng-src="{{'<?php echo site_url() ?>'+img}}"  />
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="network-media-detail">
                            <div ng-class="(showEditable.Title=='1') ? 'edit-mode' : '' ;" class="network-title">
                                <a ng-dblclick="showEditableVal('Title',1)" ng-show="showEditable.Title==0" class="name" href="javascript:void(0);" ng-bind="parseLink.Title"></a>
                                <input ng-keypress="enterUrl($event)" ng-show="showEditable.Title==1" type="text" class="form-control" ng-model="parseLink.Title">
                                <a ng-click="showEditable.Title=0;" class="removeChoice">
                                    <i class="ficon-cross"></i>
                                </a>
                            </div>
                            <span class="network-url" ng-bind="parseLink.URL"></span>
                            <div ng-class="(showEditable.Tags=='1') ? 'edit-mode' : '' ;" class="tag-option">
                                <a ng-click="showEditableVal('Tags',1)" ng-show="showEditable.Tags==0" class="name" href="javascript:void(0);">Add Tags</a>
                                <div ng-show="showEditable.Tags==1" class="networkTag">
                                    <tags-input ng-model="linktagsto[parseLink.URL]" key-property="Name" display-property="Name" placeholder="add tags separated by commas" replace-spaces-with-dashes="false">
                                        <auto-complete source="loadLinkTags($query)" min-length="0" load-on-focus="false" load-on-empty="true" max-results-to-show="3"></auto-complete>
                                    </tags-input>
                                    <a ng-click="showEditable.Tags=0;" class="removeChoice">
                                        <i class="ficon-cross"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="network-subinfo">
                                <span class="mediaCount" ng-if="parseLink.Thumbs.length>1" ng-bind="parseLink.Thumbs.length+' Images'"></span>
                                <div class="checkbox check-primary">
                                    <input ng-model="showEditable.HideThumb" type="checkbox" value="" id="NoThumbnail">
                                    <label for="NoThumbnail">No Thumbnail</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="feed-content mediaPost" ng-class="layoutClass(medias)">
                <figure class="media-thumbwrap" ng-repeat="media in medias|limitTo:4" ng-if="$index<4">
                    <a class="mediaThumb" image-class="{{layoutClass(medias)}}">
                        <img ng-src="{{media.data.ImageServerPath+'/'+media.data.ImageName}}" >
                        <div ng-if="$index=='3' && get_len(medias)>4" class="more-content">
                            <span ng-bind="'+'+(get_len(medias)-4)"></span>
                        </div>
                        <div class="t"></div>
                        <div class="r"></div>
                        <div class="b"></div>
                        <div class="l"></div>
                    </a>
                </figure>
            </div>
            <div class="feed-content mediaPost" ng-class="layoutClass(edit_medias)">
                <figure class="media-thumbwrap" ng-repeat="media in edit_medias|limitTo:4" ng-if="$index<4">
                    <a class="mediaThumb" image-class="{{layoutClass(medias)}}">
                        <img ng-src="{{media.data.ImageServerPath+'/'+media.data.ImageName}}" >
                        <div ng-if="$last && edit_medias.length>4" class="more-content">
                            <span ng-bind="'+'+(edit_medias.length-4)"></span>
                        </div>
                        <div class="t"></div>
                        <div class="r"></div>
                        <div class="b"></div>
                        <div class="l"></div>
                    </a>
                </figure>
            </div>
            <div class="feed-content">
                
                <ul class="attached-files">
                    <li ng-repeat="file in files" ng-click="hitToDownload(file.data.MediaGUID)">
                        <i ng-class="'ficon-file-type '+file.data.MediaExtension"><span ng-bind="'.'+file.data.MediaExtension"></span></i>
                        <span ng-bind="file.data.OriginalName"></span>
                    </li>
                    <li ng-repeat="file in edit_files" ng-click="hitToDownload(file.data.MediaGUID)">
                        <i ng-class="'ficon-file-type '+file.data.MediaExtension"><span ng-bind="'.'+file.data.MediaExtension"></span></i>
                        <span ng-bind="file.data.OriginalName"></span>
                    </li>
                </ul>
            </div> 
            <div class="tag-post" ng-cloak>
                <ul class="tag-group">
                    <li class="tag-item" ng-repeat="tag in postTagList">{{tag.Name}}
                    </li>
                </ul>
            </div>

        </div> 
        <div class="modal-footer">
                <a class="text-link text-sm btn" ng-click="backEditMode()">Back to Editing</a>
                <div class="pull-right">
                    <div class="btn-group">
                        <button ng-hide="edit_post && singleActivity.StatusID=='2'" type="button" ng-click="saveDraft();backEditMode();" class="btn btn-default">Save as draft</button>
                        <button type="button" ng-disabled=" ( isWallAttachementUploading || noContentToPost || summernoteBtnDisabler) " ng-click="SubmitWallpost();backEditMode();" class="btn btn-primary m-l-sm">Post</button>
                        <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                    </div>
                    
                </div>
            </div>
    </div>
</div>
