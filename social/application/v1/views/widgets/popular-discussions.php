<div class="panel panel-widget"  ng-cloak ng-init="getPopularDiscussions();" ng-if="popular_discussions.length > 0">
    <div class="panel-heading">
        <h3 class="panel-title"><span class="text" ng-bind="lang.w_popular_discussions"></span></h3>
    </div>
    <div class="panel-body no-padding">
        <ul class="list-group thumb-50 sepration-list" ng-cloak>
            <li ng-repeat="discussion in popular_discussions">
                <!-- {{discussion}} -->
                <h5 ng-if="discussion.PostTitle!='' ">
                    <a target="_self" class="a-link" href="{{discussion.ActivityURL}}" ng-bind-html="discussion.PostTitle"></a>
                </h5>
                <div class="m-t-5" ng-bind-html="textToLink(discussion.PostContent)"></div>
                <div class="feed-content mediaPost" ng-class="{'single-image':discussion.Album[0].Media.length == 1,'two-images':discussion.Album[0].Media.length > 1}" ng-if="discussion.PostTitle=='' && discussion.Album.length > 0 ">
                    <figure class="media-thumbwrap" ng-repeat="Media in discussion.Album[0].Media" >
                        <a target="_self" class="mediaThumb" image-class="{{ ( discussion.Album[0].Media.length > 1 ) ? 'two-images' : 'single-image' }}  ><img ng-src="{{ImageServerPath+'upload/wall/750x500/'+Media.ImageName}}" ></a>
                    </figure> 
                </div>
                <div class="feed-content" ng-if="discussion.PostTitle=='' && discussion.Album.length == 0 && discussion.Files.length > 0 ">
                    <ul class="attached-files">
                        <li ng-repeat="File in discussion.Files" ng-click="hitToDownload(File.MediaGUID);">
                            <span class="file-type pdf">
                            <svg class="svg-icon" width="26px" height="28px">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#fileIcon'}}"></use>
                            </svg> 
                            <span ng-bind=" '.'+File.MediaExtension"></span>
                            </span>
                            <span class="file-name" ng-bind="File.OriginalName"></span>
                            <i class="dwonload icon hover">
                                <svg class="svg-icons" width="20px" height="20px">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#dwonloadIcon'}}"></use>
                                </svg>
                            </i>
                        </li>

                    </ul>
                </div>
                <div class="feed-post-activity">
                    <ul class="feed-like-nav">
                        <li>
                            <svg height="18px" width="18px" class="svg-icon">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnComment'}}"></use>
                            </svg>
                        </li>
                        <li class="view-count" ng-bind="discussion.NoOfComments"></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>    
</div>