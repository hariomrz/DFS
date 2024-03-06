<div class="post-comment post-detail" id="cmt-div-{{list.ActivityGUID}}">
    <figure class="thumb-md">
        <a><img err-src="{{list.ImageServerPath + 'upload/profile/' + LoggedInProfilePicture}}" ng-src="{{list.ImageServerPath + 'upload/profile/220x220/' + LoggedInProfilePicture}}" class="img-circle"  ></a>
    </figure>
    <div class="comment-on-post">
        <div class="textarea" ng-init="list.comment = ''; list.showeditor = false;">
            <textarea ng-if="LoginSessionKey=='' && !list.showeditor && show_comment_box != list.ActivityGUID" ng-click="loginRequired();" placeholder="{{(list.PostType == '2') ? 'Write an answer...' : 'Write a comment...'}}" class="form-control"></textarea>
            <!-- {{' - '+LoginSessionKey+' - '}}
            {{' - '+list.showeditor+' - '}}
            {{' - '+list.Comments.length+' - '}}
            {{' - '+show_comment_box+' - '}}
            {{' - '+list.ActivityGUID+' - '}} -->
            <textarea ng-if="LoginSessionKey!='' && (!list.showeditor || list.Comments.length>0) && show_comment_box != list.ActivityGUID" ng-click="postCommentEditor(list.ActivityGUID, FeedIndex)" placeholder="{{(list.PostType == '2') ? 'Write an answer...' : 'Write a comment...'}}" class="form-control"></textarea>
            <!-- <button class="btn btn-default btn-md quote-btn" ng-if="show_comment_box == list.ActivityGUID && list.PostContent !== ''" ng-click="insert_to_editor(list.ActivityGUID, list.PostContent, FeedIndex)">Quote Text</button> -->
            <summernote ng-if="show_comment_box == list.ActivityGUID" on-keyup="breakquote(evt); checkEditorData(evt, FeedIndex);" data-posttype="Comment" data-guid="{{list.ActivityGUID}}" on-image-upload="imageUpload(files)" id="cmt-{{list.ActivityGUID}}" config="commentOptions" placeholder="Write a comment" on-focus="focus(evt)" editable="editable" editor="editor" on-blur="CheckBlur(list.ActivityGUID)" on-change="saveRange(list.ActivityGUID);"></summernote>
            <span class="absolute loader commentEditorLoader" style="top: 30%; display: none;">&nbsp;</span>
        </div>
        <div ng-if="show_comment_box == list.ActivityGUID" class="attached-list clearfix" id="attachments-cmt-{{ list.ActivityGUID}}" ng-cloak ng-show="ratingList[FeedIndex].commentMediaCount > 0">
            <ul class="attache-listing"> 
                <li ng-repeat=" ( mediaIndex, media ) in ratingList[FeedIndex].medias">
                    <img ng-show="media.progress" ng-show="media.progress" ng-src="{{media.list.ImageServerPath}}/220x220/{{media.list.ImageName}}" err-src="{{media.list.ImageServerPath}}/{{media.list.ImageName}}" > 
                    <i ng-show="media.progress" class="ficon-cross" ng-click="removeAttachement('media', mediaIndex, FeedIndex);"></i>
<!--                    <span ng-hide="media.progress" class="loader" style="display: block;"></span>-->
                    <div ng-if="media.progressPercentage && media.progressPercentage < 101" data-percentage="{{media.progressPercentage}}" upload-progress-bar-cs></div>                    
                </li>
            </ul>
        </div>
        <div class="post-file-list" ng-cloak ng-show="ratingList[FeedIndex].commentFileCount > 0">
            <ul class="attache-file-list">
                <li ng-repeat="(fileKey, file) in ratingList[FeedIndex].files"> 
<!--                    <div ng-hide="file.progress" class="loader" style="display: block;"></div>-->
                    <div ng-if="file.progressPercentage && file.progressPercentage < 101" data-percentage="{{file.progressPercentage}}" upload-progress-bar-cs></div>
                    <i  class="ficon-file-type" ng-class="file.list.MediaExtension || file.ext"><span ng-bind="'.' + (file.list.MediaExtension || file.ext )"></span></i>
                    <span  class='file-name' ng-bind="file.list.OriginalName || file.name"></span>
                    <i class="ficon-cross" ng-show="file.progress" ng-click="removeAttachement('file', fileIndex, FeedIndex);"></i>
                </li>
            </ul>
        </div>
        <div ng-if="show_comment_box == list.ActivityGUID" class="post-footer">
            <div class="post-footer-inner">
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="post-buttons">                            
                            <li class="attachment">
                                <button class="btn btn-default" ngf-select="uploadFiles($files, $invalidFiles, list.ActivityGUID, FeedIndex)" multiple ngf-validate-async-fn="validateFileSize($file);" type="button" onclick="$('#fileAttach').trigger('click');"><i class="ficon-attachment"></i></button>
                            </li>
                            <li>
                                <div class="btn-group">
                                    <button ng-disabled="noContentToPost" id="PostBtn-{{list.ActivityGUID}}" 
                                        data-ng-click="submitComment($event, list.RatingGUID, list.IsOwner, list.CreatedBy.ModuleID, RatingIndex, list.ActivityGUID)"  
                                        type="button" class="btn btn-primary" >Post</button>
                                    <span class="loader" ng-if="list.postingCommentsStatus"> &nbsp; </span>
                                </div>
                            </li>
                            <li>
                                <button class="btn btn-default btn-md " ng-click="cancelPostComment(list); list.showeditor=false;">
                                    Cancel
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>