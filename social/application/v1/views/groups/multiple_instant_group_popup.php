<div class="modal fade" id="multipleInstantGroupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
    <div class="modal-dialog post-inGroup">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4 class="modal-title" id="myModalLabel3">Multiple groups found for your selection, Please select a group in which you want to post.</h4>
            </div>
            <div class="modal-body listing-space non-footer">
                <div class="default-scroll scrollbar">
                    <ul class="list-group removed-peopleslist ">
                        <li class="list-group-item" ng-repeat="Group in multipleInstantGroupData">
                            <figure class="m-user-thmb" ng-class="{'group-thumb': group_user_tags.length>2, 'group-thumb-two':group_user_tags.length==2}">
                                <span ng-repeat="list in group_user_tags">
                                    <img data-ng-if="list.ProfilePicture==''" class="mCS_img_loaded"  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/group-no-img.jpg' ?>">
                                    <img data-ng-if="list.ProfilePicture!=''" class="mCS_img_loaded"  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}">
                                </span>
                            </figure>
                            <div class="description">
                                <a target="_self" href="javascript:void(0);" class="name">
                                    <span ng-repeat="Member in group_user_tags"><span ng-bind="Member.name" ng-if="$index<=2"></span><span ng-if="$index<2 && group_user_tags.length>=3">,</span><span ng-if="$index<(group_user_tags.length-1) && group_user_tags.length<3">,</span> </span>
                                </a>
                                <span class="location"  ng-bind-html="textToLink(Group.LastPostContent);"></span>
                                <div class="radio radioChecked">
                                    <input id="g{{Group.GroupGUID}}" type="radio" name="group" class="multi_group" ng-model="multi_group" value="{{Group.GroupGUID}}" >
                                    <label for="g{{Group.GroupGUID}}">&nbsp;</label>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-sm pull-right" id="post_multiple_group" type="button" data-toggle="modal" ng-click="post_multiple_group();">
                POST
                <span class="btn-loader" style="display: none;"> <span class="spinner-btn">&nbsp;</span> </span>
                </button>
            </div>
        </div>
    </div>
</div>