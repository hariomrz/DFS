<div class="panel panel-primary" init-scroll-fix="scrollFixRight">
    <span ng-if="userPostDetailLoader" class="loader text-lg" style="display:block;">&nbsp;</span>
    <div ng-if="( !userPostDetailLoader && userPostDetail.UserDetails )" class="panel-body user-sm-info">
        <ul class="list-group list-group-thumb md">
            <li class="list-group-item">
                <div class="list-group-body">
                    <!-- <div class="btn-toolbar btn-toolbar-right dropdown">
                        <a class="btn btn-xs btn-default btn-icn" ng-click="SetUserFromDashboard(userPostDetail.UserDetails);" data-toggle="dropdown" role="button"><span class="icn"><i class="ficon-dots"></i></span></a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a data-toggle="modal" ng-click="reset_popup_notes();" data-target="#addNotes">Add Notes</a></li>
                            <li><a ng-click="getUserPersonaDetail();">User Persona</a></li>
                            <li><a ng-click="openNewsletterGroups(userPostDetail.UserDetails.UserID);">Add To Newsletter Group</a></li>
                            <li><a ng-click="$emit('openMsgModalPopup', { Name: userPostDetail.UserDetails.Name, ModuleID: userPostDetail.UserDetails.ModuleID, ModuleEntityID: userPostDetail.UserDetails.UserID });">Send Message</a></li>
                        </ul>
                    </div>
                    -->
                    <figure class="list-figure">
                        <a class="" entitytype="user" entityguid="{{userPostDetail.UserDetails.UserGUID}}">
                            <img ng-if="( ( userPostDetail.UserDetails.ProfilePicture !== '' ) && ( userPostDetail.UserDetails.ProfilePicture !=='user_default.jpg' ) )"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + userPostDetail.UserDetails.ProfilePicture}}">
                            <span ng-if="( ( userPostDetail.UserDetails.ProfilePicture == '' ) || ( userPostDetail.UserDetails.ProfilePicture =='user_default.jpg' ) ) && activityData.activity.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(userPostDetail.UserDetails.Name)"></span></span>
                        </a>

                    </figure>
                    <div class="list-group-content">
                        <h4 class="list-group-item-heading lg">
                            <a  ng-bind="userPostDetail.UserDetails.Name"></a> <!-- ng-click="SetUserFromDashboard(userPostDetail.UserDetails); getUserPersonaDetail();" -->
                            <!-- <span ng-if="userPostDetail.UserDetails.Gender > 0" ng-bind="( ( userPostDetail.UserDetails.Gender == 1 ) ? '(M)' : (( userPostDetail.UserDetails.Gender == 2 ) ? '(F)' : '(O)') )" ></span>
                            <a uib-tooltip="VIP User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsVIP == 1 )" class="icn circle-icn circle-primary">
                                <i class="ficon-check"></i>
                            </a>
                            <a uib-tooltip="Association User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsAssociation == 1 )" class="icn circle-icn circle-primary">
                                <i class="ficon-check"></i>
                            </a>
                             <span class="icn f-13" uib-tooltip-template="'browsingContributionTooltip.html'">
                                <i class="ficon-trending"></i>
                            </span>
                            <script type="text/ng-template" id="browsingContributionTooltip.html">
                                <span class="tip-title"><i class="ficon-browsing-home"></i> Browsing : {{ userPostDetail.UserDetails.BrowsingAverageText }}</span>
                                <span class="tip-title"><i class="ficon-contiburtion"></i> Contribution : {{ userPostDetail.UserDetails.ContributionAverageText }}</span>
                            </script>
                            <span class="icn f-13" uib-tooltip-template="'socialInfoTooltip.html'">
                                <i class="ficon-network"></i>
                            </span>
                            <script type="text/ng-template" id="socialInfoTooltip.html">
                                <span class="tip-title social-icon"><i class="ficon-facebook"></i> {{ userPostDetail.UserDetails.NoOfFollowersFB }} Followers</span>
                                <span class="tip-title social-icon"><i class="ficon-twitter"></i> {{ userPostDetail.UserDetails.NoOfFollowersTw }} Followers</span>
                                <span class="tip-title social-icon"><i class="ficon-vsocial"></i> {{ userPostDetail.UserDetails.NoOfFollowCSocial }} Followers</span>
                            </script> -->
                        </h4>
                        <span class="text-base block" ng-if="userPostDetail.UserDetails.About">
                            <span>{{userPostDetail.UserDetails.About}}</span>
                        </span>
                       <!--  <span class="text-base block" ng-if="userPostDetail.UserDetails.Occupation">
                            <span>{{userPostDetail.UserDetails.Occupation}}</span>
                        </span>
                        <span class="text-base block" >
                            <span ng-if="userPostDetail.UserDetails.Locality.Name">{{userPostDetail.UserDetails.Locality.Name}}, {{userPostDetail.UserDetails.Locality.WName}} (Ward {{userPostDetail.UserDetails.Locality.WNumber}})</span>
                        </span>
                       <span class="text-base block" >
                            <span ng-if=" ( userPostDetail.UserDetails.Age > 0 ) " ng-bind="userPostDetail.UserDetails.Age + ' Year(s)' + ( ( userPostDetail.UserDetails.IncomeLevel > '0') ? ', ' : '' )"></span>

                            <span  ng-if="userPostDetail.UserDetails.IncomeLevel > '0'"  >
                                <span class="text" ng-bind=" (userPostDetail.UserDetails.IncomeLevel==1 ? 'Income Level: Low' : (userPostDetail.UserDetails.IncomeLevel==2 ? 'Income Level: Medium' : 'Income Level: High'))"></span>
                            </span>
                        </span>
                        <span ng-if="( userPostDetail.UserDetails.MartialStatus && userPostDetail.UserDetails.MartialStatusTxt )" class="text-sm-off bold">
                            <span ng-bind="userPostDetail.UserDetails.MartialStatusTxt"></span>
                            <span ng-if="( ( userPostDetail.UserDetails.MartialStatus == 2 ) || ( userPostDetail.UserDetails.MartialStatus == 3 ) )">
                                <span ng-if=" userPostDetail.UserDetails.RelationWithName"> with <a ng-bind="(userPostDetail.UserDetails.RelationWithName)"></a></span>
                            </span>
                            <span ng-if="( userPostDetail.UserDetails.MartialStatus == 4 )">
                                <span ng-if="userPostDetail.UserDetails.RelationWithName"> to <a ng-bind="(userPostDetail.UserDetails.RelationWithName)"></a></span>
                            </span>
                        </span> -->

                    </div>
                </div>
            </li>
            <li class="socialCountLi">
                <ul class="user-info-list xcollapse m-t-md" id="userInfoToggle">
                    <li class="row socialCount">
                        <div class="col-xs-4 bdrRight">
                            <label class="label-text"><span class="text" ng-bind="userPostDetail.UserDetails.TotalFollowers"></span>Followers</label>
                        </div>
                        <div class="col-xs-4 bdrRight">
                            <label class="label-text"><span class="text" ng-bind="userPostDetail.UserDetails.TotalFollowing"></span>Following</label>
                        </div>

                        <div class="col-xs-4">
                            <label class="label-text"><span class="text" ng-bind="userPostDetail.UserDetails.PostCount"></span>Posts</label>
                        </div>
                        <!-- <div class="col-xs-3">
                            <label class="label-text">Responses <span class="text" ng-bind="': ' + userPostDetail.UserDetails.CommnetCount"></span></label>
                        </div> -->
                    </li>
                    <li class="row xtext-right socialMemSince" ng-if="userPostDetail.UserDetails.MemberSince">
                        <div class="col-xs-12"><small ng-bind="createDateObj(userPostDetail.UserDetails.MemberSince) | date : ' \'Member Since : \' dd \'-\' MMM yyyy'"></small></div>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- <a class="ficon-arrow-down feed-expand collapsed" data-toggle="collapse" href="#userInfoToggle"></a> -->
    </div>
    <div ng-if="( !userPostDetailLoader && userPostDetail.UserDetails )" class="panel-body custom-scroll scroll-sms" style="height: 330px;">

       <!-- <div ng-if="userPostDetail.IsCommentView" class="form-group no-bordered">
            <div class="p-v">
                <div class="row p-b-sm">
                    <div class="col-xs-12">
                        <label class="checkbox">
                            <input ng-checked="userPostDetail.CommentDetails.IsAmazing == 1" ng-click="is_amazing(userPostDetail.CommentDetails)" id="cia"  type="checkbox" class="check-content-filter">
                            <span class="label bold">Amazing Comment</span>
                        </label>
                    </div>
                </div>

                <div class="row p-b-sm">
                    <div class="col-xs-12">
                        <label class="checkbox">
                            <input ng-checked="userPostDetail.CommentDetails.IsPointAllowed == 1" ng-click="point_allowed(userPostDetail.CommentDetails)" id="cpnt"  type="checkbox" class="check-content-filter">
                            <span class="label bold">No points need to be given for this comment</span>
                        </label>
                    </div>
                </div>
                <div ng-if="activityDataList[currentActivityIndex].activity.PostType==2" class="row ">
                    <div class="col-xs-4">
                        <label class="checkbox">
                            <input ng-checked="userPostDetail.CommentDetails.Solution == 1" ng-click="set_solution(userPostDetail.CommentDetails, 1)" id="sol_1"  type="checkbox" class="check-content-filter">
                            <span class="label bold">Possible Solution</span>
                        </label>
                    </div>
                    <div class="col-xs-4">
                        <label class="checkbox">
                            <input ng-checked="userPostDetail.CommentDetails.Solution == 2" ng-click="set_solution(userPostDetail.CommentDetails, 2)" id="sol_2" type="checkbox" class="check-content-filter">
                            <span class="label bold">Solution</span>
                        </label>
                    </div>
                    <div class="col-xs-4">
                    </div>
                </div>
            </div>
        </div>


        <div ng-if="userPostDetail.ActivityVisibility" class="form-group no-bordered">
            <label class="control-label bolder">POST VISIBILITY</label>
            <span class="action pull-right">
                <a class="ficon-edit mrgn-l-20" ng-click="setCurrentWardList();" uib-tooltip="Edit Visibility" tooltip-append-to-body="true" data-toggle="modal"></a>
            </span>
            <ul class="tags-list clearfix">
                <li ng-repeat="visibility in userPostDetail.ActivityVisibility">
                    <span>{{visibility.WID == 1 ? 'All Ward' : 'Ward '+visibility.WNumber}}</span>
                </li>
            </ul>
        </div>
        <div ng-if="!userPostDetail.IsCommentView" class="form-group no-bordered">
            <label class="control-label bolder">MARK AS STORY</label>
            <span class="action pull-right">
                <a ng-if="userPostDetail.StoryVisibility.length > 0" class="ficon-bin" ng-click="removeStory();" uib-tooltip="Remove From Story" tooltip-append-to-body="true"></a>
                <a class="ficon-edit mrgn-l-20" ng-click="setCurrentStoryWardList();" uib-tooltip="Mark as Story" tooltip-append-to-body="true" data-toggle="modal"></a>
            </span>
            <ul class="tags-list clearfix">
                <li ng-repeat="visibility in userPostDetail.StoryVisibility">
                    <span>{{visibility.WID == 1 ? 'All Ward' : 'Ward '+visibility.WNumber}}</span>
                </li>
            </ul>
        </div>


        <div ng-if="userPostDetail.ActivityTags.Question" class="form-group no-bordered">
            <label class="checkbox">
                <input ng-checked="userPostDetail.ActivityTags.Question.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Question, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'que', activityDataList[currentActivityIndex].activity.ActivityGUID)" id="que_{{activityDataList[currentActivityIndex].activity.ActivityID}}" value="{{userPostDetail.ActivityTags.Question.TagID}}" type="checkbox" class="check-content-filter">
                <span class="label bold">Question</span>
            </label>
            <div class="input-icon" ng-if="userPostDetail.ActivityTags.Question.IsExist == 2">
                <i class="ficon-price-tag"></i>
                    <tags-input
                        ng-model="userPostDetail.ActivityTags.Question.CCategory"
                        display-property="Name"
                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Question, activityDataList[currentActivityIndex].activity.ActivityID)"
                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Question, activityDataList[currentActivityIndex].activity.ActivityID)"
                        placeholder="Add question category"
                        readonly="readonly"
                        replace-spaces-with-dashes="false"
                        add-from-autocomplete-only="true"
                        template="qtag">
                        <auto-complete source="loadTagCategories($query, activityDataList[currentActivityIndex].activity.ActivityID, 'que')" load-on-focus="true" min-length="0"></auto-complete>
                    </tags-input>
                    <script type="text/ng-template" id="qtag">
                        <div class="tag-template added-by-admin">
                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                        </div>
                    </script>
            </div>
        </div>
        <div ng-if="userPostDetail.ActivityTags.Custom" class="form-group no-bordered">
            <label class="checkbox">
                <input ng-checked="userPostDetail.ActivityTags.Custom.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Custom, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'cla', activityDataList[currentActivityIndex].activity.ActivityGUID)" id="cla_{{activityDataList[currentActivityIndex].activity.ActivityID}}" value="{{userPostDetail.ActivityTags.Custom.TagID}}" type="checkbox" class="check-content-filter">
                <span class="label bold">Classified</span>
            </label>
            <div class="input-icon" ng-if="userPostDetail.ActivityTags.Custom.IsExist == 1">
                <i class="ficon-price-tag"></i>
                    <tags-input
                        ng-model="userPostDetail.ActivityTags.Custom.CCategory"
                        display-property="Name"
                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Custom, activityDataList[currentActivityIndex].activity.ActivityID)"
                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Custom, activityDataList[currentActivityIndex].activity.ActivityID)"
                        placeholder="Add classified category"
                        readonly="readonly"
                        replace-spaces-with-dashes="false"
                        add-from-autocomplete-only="true"
                        template="ctag">
                        <auto-complete source="loadTagCategories($query, activityDataList[currentActivityIndex].activity.ActivityID, 'cla')" load-on-focus="true" min-length="0"></auto-complete>
                    </tags-input>
                    <script type="text/ng-template" id="ctag">
                        <div class="tag-template added-by-admin">
                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                        </div>
                    </script>
            </div>
        </div>

        <div ng-if="!userPostDetail.IsCommentView">
            <div  class="form-group no-bordered">
                <span ng-if="activityDataList[currentActivityIndex].activity.IsCityNews == 0">
                    <a class="btn btn-xs btn-icn btn-default" ng-click="move_to_city_news(activityDataList[currentActivityIndex].activity)" >
                        <span class="icn " ng-cloak>
                                <i class="ficon-newspaper"></i>
                            </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="move_to_city_news(activityDataList[currentActivityIndex].activity)"> Move to city news </span>
                </span>
                <span ng-if="activityDataList[currentActivityIndex].activity.IsCityNews == 1">
                    <a class="btn btn-xs btn-icn btn-default "
                        ng-click="remove_city_news(activityDataList[currentActivityIndex].activity)" >
                            <span class="icn gfill" ng-cloak>
                                <i class="ficon-newspaper"></i>
                            </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="remove_city_news(activityDataList[currentActivityIndex].activity)"> Remove from city news </span>
                </span>
            </div>

            <div  class="form-group no-bordered">
                <span ng-if="activityDataList[currentActivityIndex].activity.IsIdea == 0">
                    <a class="btn btn-xs btn-icn btn-default"   ng-click="idea_for_better_indore(activityDataList[currentActivityIndex].activity)" >
                        <span class="icn " ng-cloak>
                            <i class="ficon-idea"></i>
                        </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="idea_for_better_indore(activityDataList[currentActivityIndex].activity)"> Move to Idea for a better Indore </span>
                </span>
                <span ng-if="activityDataList[currentActivityIndex].activity.IsIdea == 1">
                    <a class="btn btn-xs btn-icn btn-default "  ng-click="idea_for_better_indore(activityDataList[currentActivityIndex].activity)" >
                        <span class="icn gfill"  ng-cloak>
                            <i class="ficon-idea"></i>
                        </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="idea_for_better_indore(activityDataList[currentActivityIndex].activity)"> Remove from Idea for a better Indore </span>
                </span>
            </div>

            <div  class="form-group no-bordered">
                <span ng-if="activityDataList[currentActivityIndex].activity.IsRelated == 0">
                    <a class="btn btn-xs btn-icn btn-default" ng-click="related_to_indore(activityDataList[currentActivityIndex].activity)" >
                        <span class="icn " ng-cloak>
                            <i class="ficon-rt"></i>
                        </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="related_to_indore(activityDataList[currentActivityIndex].activity)"> Move for Related to Indore </span>
                </span>
                <span ng-if="activityDataList[currentActivityIndex].activity.IsRelated == 1">
                    <a class="btn btn-xs btn-icn btn-default" ng-click="related_to_indore(activityDataList[currentActivityIndex].activity)" >
                        <span class="icn gfill" ng-cloak>
                            <i class="ficon-rt"></i>
                        </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="related_to_indore(activityDataList[currentActivityIndex].activity)"> Remove from Related to Indore </span>
                </span>
            </div>
            <div class="form-group no-bordered">
                <span>
                    <a class="btn btn-xs btn-icn btn-default" ng-click="view_similar_posts(userPostDetail.ActivityTags.Normal)">
                        <span class="icn gfill" ng-cloak>
                            <i class="ficon-search"></i>
                        </span>
                    </a>
                    <span class="label bold cursor-pointer" ng-click="view_similar_posts(userPostDetail.ActivityTags.Normal)"> Similar posts </span>
                </span>
            </div>
</div>
-->
        <div ng-if="userPostDetail.ActivityTags" class="form-group no-bordered">
            <label class="control-label bolder">POST TAGS</label>
            <div class="input-icon tag-suggestions">
                <!-- <i class="ficon-price-tag"></i> -->
                <i class="ficon-plus tagIcon"></i>
                <tags-input
                    class="tagBox"
                    ng-model="userPostDetail.ActivityTags.Normal"
                    display-property="Name"
                    on-tag-added="addMemberTags('ACTIVITY', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    on-tag-removed="removeMemberTags('ACTIVITY', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    placeholder="Add more tags"
                    replace-spaces-with-dashes="false"
                    add-from-autocomplete-only="true"
                    template="tag1">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'ACTIVITY', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag1">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>

       <!--     <div class="form-group no-bordered">
            <label class="control-label bolder">USER TAGS</label>
            <div class="input-icon tag-suggestions">
                <i class="ficon-price-tag"></i>
                <tags-input
                    ng-model="userPostDetail.UserTags.User_ReaderTag"
                    display-property="Name"
                    on-tag-added="addMemberTags('USER', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    on-tag-removed="removeMemberTags('USER', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    placeholder="Add user type"
                    replace-spaces-with-dashes="false"
                    add-from-autocomplete-only="true"
                    template="tag7">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].subject_user.UserID, 3, 'USER', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag7">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>

           < !-- <div class="input-icon">
                <i class="ficon-profession"></i>
                <tags-input
                    ng-model="userPostDetail.UserTags.UserProfession"
                    display-property="Name"
                    on-tag-added="addMemberTags('PROFESSION', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    on-tag-removed="removeMemberTags('PROFESSION', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    placeholder="Add more profession"
                    replace-spaces-with-dashes="false"
                    add-from-autocomplete-only="true"
                    template="tag5">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].subject_user.UserID, 3, 'PROFESSION', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag5">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div> -- >

        </div> -->

       <!-- <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-happy"></i>
                <tags-input
                    ng-model="userPostDetail.ActivityTags.ActivityMood"
                    display-property="Name"
                    on-tag-added="addMemberTags('MOOD', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    on-tag-removed="removeMemberTags('MOOD', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    placeholder="Add your mood"
                    template="tag2">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'MOOD', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag2">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>
        <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-classification"></i>
                <tags-input
                    ng-model="userPostDetail.ActivityTags.ActivityClassification"
                    display-property="Name"
                    on-tag-added="addMemberTags('CLASSIFICATION', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    on-tag-removed="removeMemberTags('CLASSIFICATION', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    placeholder="Admin classification"
                    template="tag3">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'CLASSIFICATION', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag3">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>
        <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-nature-reader"></i>
                <tags-input
                    ng-model="userPostDetail.ActivityTags.User_ReaderTag"
                    display-property="Name"
                    on-tag-added="addMemberTags('READER', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    on-tag-removed="removeMemberTags('READER', $tag, activityDataList[currentActivityIndex].activity.ActivityGUID, 0)"
                    placeholder="Add nature of reader"
                    template="tag4">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'READER', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag4">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>

        <div class="form-group no-bordered">
            <label class="control-label bolder">MEMBER TAGS</label>

        </div>

        <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-interest"></i>
                <tags-input
                    ng-model="userPostDetail.UserDetails.Interests"
                    display-property="Name"
                    on-tag-added="updateMemberInterest($tag, activityDataList[currentActivityIndex].subject_user.UserID)"
                    on-tag-removed="removeMemberInterest($tag, activityDataList[currentActivityIndex].subject_user.UserID)"
                    placeholder="Add more interests"
                    template="tag6" key-property="ModuleEntityCount">
                    <auto-complete source="loadMemberInterest($query)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag6">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>
        <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-brand"></i>
                <tags-input
                    ng-model="userPostDetail.UserTags.Brand"
                    display-property="Name"
                    on-tag-added="addMemberTags('BRAND', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    on-tag-removed="removeMemberTags('BRAND', $tag, activityDataList[currentActivityIndex].subject_user.UserGUID, 3)"
                    placeholder="Add Brand"
                    template="tag8">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].subject_user.UserID, 3, 'BRAND', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag8">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div> -->
    </div>
</div>

<!-- <input type="hidden" value="<?php echo isset($UserStatus) ? $UserStatus : 2; ?>" id="hdnUserStatus"> -->
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>

<?php //$this->load->view('admin/users/persona/add_note_popup') ?>
<?php $this->load->view('admin/users/persona/user_persona')?>
<?php $this->load->view('admin/ward/ward_visibility')?>
<div class="modal fade" tabindex="-1" role="dialog" id="communicate_single_user" ng-controller="messageCtrl">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true"><i class="icon-close"></i></span>
              </button>

              <h4 class="modal-title"><?php echo lang('User_Index_Communicate'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="user-detial-block">
                    <a class="user-thmb" href="javascript:void(0);">
                        <img ng-if="user.ProfilePicture" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/'+user.ProfilePicture}}" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser">
                        <img ng-if="!user.ProfilePicture" src="<?php echo IMAGE_SERVER_PATH ?>upload/blank-profile.jpg" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser">
                    </a>
                    <div class="overflow">
                        <a class="name-txt" href="javascript:void(0);" id="lnkUserName">{{user.Name}} </a>
                        <div class="dob-id">
                            <span id="spnProcessDate">Member Since: {{user.MemberSince}} </span><br>
                            <a id="lnkUserEmail" href="javascript:void(0);">{{user.Email}} </a>
                        </div>
                    </div>
                </div>
                <div class="communicate-footer row-flued">
                    <div class="form-group">
                        <label for="subjects" class="label">Subject</label>
                            <input type="text" class="form-control" value="" name="Subject" id="emailSubject" >
                        <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorMessage}}</div>
                    </div>
                    <div class="text-msz editordiv">
                        <?php //echo $this->ckeditor->editor('description', @$default_value); ?>
                        <textarea id="description" name="description" placeholder="Description" class="message text-editor" rows="10"></textarea>
                        <div class="error-holder" ng-show="showMessageError" style="color: #CC3300;">{{errorBodyMessage}}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button ng-click="sendEmail(user,'users')" class="btn btn-primary pull-right" type="submit" id="btnCommunicateSingle"><?php echo lang('Submit'); ?></button>
            </div>
         </div>
     </div>
</div>


<div class="modal fade" id="city_news" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                    <span aria-hidden="true"><i class="icon-close"></i></span>
                </button>
                <h4>Move to city news</h4>
            </div>
            <div class="modal-body custom-scroll scroll-md">
            	<div class="popup-content" style="padding: 0;">
                    <ul>
                    	<li>
                    		<label class="checkbox checkbox-inline checkbox-block" ng-click="select_city_news(1);">
                            <!-- <label class="checkbox checkbox-inline checkbox-block"> -->
                                <input type="checkbox" ng-model="is_city_news" id="IsCityNews">
                                <span class="label"></span>
							</label>&nbsp;
                    		<p style="display: inline-block;">Show this in City news</p>
						</li>
                        <li ng-show="is_city_news">
                    		<!-- <label  class="checkbox checkbox-inline checkbox-block" ng-click="select_city_news(2);"> -->
                            <label class="checkbox checkbox-inline checkbox-block">
                                <input type="checkbox" ng-model="is_show_on_news_feed" id="IsShowOnNewsFeed">
                                <span class="label"></span>
							</label>&nbsp;
                    		<p style="display: inline-block;">Show on news feed as well</p>
						</li>
                    </ul>
                </div>
            	<button class="button btn pull-right EditTag city-news-save-btn" ng-click="saveCityNews();">Save</button>
            </div>
        </div>
    </div>
</div>

<div ng-include="newsletter_group_view"></div>
