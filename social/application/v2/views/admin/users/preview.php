<div class="post-preview" ng-show="ShowPreview=='1'" ng-cloak>
    <div class="modal-header">
        <button type="button" class="close" ng-click="backEditMode();"> <span><i class="icon-close"></i></span> </button>
        <h4 class="modal-title">Post Preview</h4>
    </div>
    <div class="news-feed-listing">
        <div class="feed-body">
            <div class="feed-header">
                <a class="thumb-48"><img id="PreviewImage" err-name="{{postasuser.FirstName+' '+postasuser.LastName}}" src="assets/img/dummythm1.jpg"  /></a>
                <div class="user-info">
                    <a href=""  id="PreviewName"></a>
                    <ul class="list-activites">
                        <li ng-bind="getCurrentTime()"></li>
                        <li><span class="icn"><i class="ficon-earth" data-toggle="tooltip" data-placement="top" title="Public">&nbsp;</i></span></li>
                    </ul>
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
                            <svg height="10px" width="10px" class="svg-icons">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#closeIcn"></use>
                            </svg>
                        </a>
                        <div class="network-media-block networkmediaList">
                            <div class="slider-wrap">
                                <ul class="networkmedia" data-uix-bxslider="mode: 'horizontal', pager: false, controls: true, minSlides: 1, maxSlides:1, slideWidth: 170, slideMargin:0, infiniteLoop: false, hideControlOnEnd: false" ng-show="parseLink.Thumbs.length>0">
                                    <li ng-repeat="img in parseLink.Thumbs" notify-when-repeat-finished>
                                        <img err-SRC="{{SiteURL}}assets/img/profiles/user_default.jpg" ng-src="{{'<?php echo site_url() ?>'+img}}"  />
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="network-media-detail">
                            <div ng-class="(showEditable.Title=='1') ? 'edit-mode' : '' ;" class="network-title">
                                <a ng-dblclick="showEditableVal('Title',1)" ng-show="showEditable.Title==0" class="name" href="javascript:void(0);" ng-bind="parseLink.Title"></a>
                                <input ng-keypress="enterUrl($event)" ng-show="showEditable.Title==1" type="text" class="form-control" ng-model="parseLink.Title">
                                <a ng-click="showEditable.Title=0;" class="removeChoice">
                                    <svg height="10px" width="10px" class="svg-icons">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#closeIcn"></use>
                                    </svg>
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
                                        <svg height="10px" width="10px" class="svg-icons">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#closeIcn"></use>
                                        </svg>
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
            <div class="feed-content mediaPost" ng-class="get_img_class(medias,1)">
                <figure class="media-thumbwrap" ng-repeat="media in medias">
                    <a class="mediaThumb" image-class="{{get_img_class(medias,1)}}"><img ng-src="{{media.data.ImageServerPath+'/'+media.data.ImageName}}" ></a>
                </figure>
            </div>
            <div class="feed-content">
                <ul class="attached-files">
                    <li ng-repeat="file in files" ng-click="hitToDownload(file.data.MediaGUID);">
                        <span class="file-type" ng-class="file.data.MediaExtension">
                            <svg class="svg-icon" width="26px" height="28px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#fileIcon"></use>
                            </svg>
                            <span ng-bind="'.'+file.data.MediaExtension"></span>
                        </span>
                        <span class="file-name" ng-cloak searchfieldid="advancedSearchKeyword" make-content-highlighted="file.data.OriginalName" ng-bind-html="file.data.OriginalName"></span>
                        <!--<a href="{{NewsFeedList.baseUrl}}home/download/{{wallMedia.MediaGUID}}">-->
                        <!--<a class="dwonload hover" ng-href="{{baseURL}}home/download/{{file.MediaGUID}}/wall">-->
                        <a class="dwonload icon hover">
                            <svg class="svg-icons" width="20px" height="20px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#dwonloadIcon"></use>
                            </svg>
                        </a>
                    </li>
                    <li ng-repeat="file in edit_files" ng-click="hitToDownload(file.data.MediaGUID);">
                        <span class="file-type" ng-class="file.data.MediaExtension">
                            <svg class="svg-icon" width="26px" height="28px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#fileIcon"></use>
                            </svg>
                            <span ng-bind="'.'+file.data.MediaExtension"></span>
                        </span>
                        <span class="file-name" ng-cloak searchfieldid="advancedSearchKeyword" make-content-highlighted="file.data.OriginalName" ng-bind-html="file.data.OriginalName"></span>
                        <!--<a href="{{NewsFeedList.baseUrl}}home/download/{{wallMedia.MediaGUID}}">-->
                        <!--<a class="dwonload hover" ng-href="{{baseURL}}home/download/{{file.MediaGUID}}/wall">-->
                        <a class="dwonload icon hover">
                            <svg class="svg-icons" width="20px" height="20px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="assets/img/sprite.svg#dwonloadIcon"></use>
                            </svg>
                        </a>
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
                <button type="button" class="btn btn-default btn-link pull-left" ng-click="backEditMode()">Back to Editing</button>
                <div class="pull-right">
                    <div class="btn-group">
                        <button ng-hide="edit_post && singleActivity.StatusID=='2'" type="button" ng-click="saveDraft();backEditMode();" class="btn btn-default">Save as draft</button>
                        <button type="button" ng-disabled=" ( isWallAttachementUploading || noContentToPost || summernoteBtnDisabler) " ng-click="SubmitWallpost();backEditMode();" class="btn btn-primary">Post</button>
                        <span class="loader" ng-if="SubmitWallpostLoader"> &nbsp; </span>
                    </div>
                    
                </div>
            </div>
    </div>
</div>