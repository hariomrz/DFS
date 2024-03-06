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
                        <a class="loadbusinesscard" entitytype="user" entityguid="{{userPostDetail.UserDetails.UserGUID}}">
                            <img ng-if="( ( userPostDetail.UserDetails.ProfilePicture !== '' ) && ( userPostDetail.UserDetails.ProfilePicture !=='user_default.jpg' ) )"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + userPostDetail.UserDetails.ProfilePicture}}">
                            <span ng-if="( ( userPostDetail.UserDetails.ProfilePicture == '' ) || ( userPostDetail.UserDetails.ProfilePicture =='user_default.jpg' ) ) && activityData.activity.PostType !== '7'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(userPostDetail.UserDetails.Name)"></span></span>
                        </a>
                        <a ng-if="( userPostDetail.UserDetails.Verified == 1 )" class="icn circle-icn circle-primary">
                            <i class="ficon-check"></i>
                        </a>
                    </figure>
                    <div class="list-group-content">
                        <h4 class="list-group-item-heading lg">                                               
                            <a ng-click="SetUserFromDashboard(userPostDetail.UserDetails); getUserPersonaDetail();" ng-bind="userPostDetail.UserDetails.Name"></a>
                            <!-- <span class="icn f-13" uib-tooltip-template="'browsingContributionTooltip.html'">
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
                        <span class="text-base block" ><span ng-if="userPostDetail.UserDetails.Locality.Name" ng-bind="userPostDetail.UserDetails.Locality.Name "></span></span> 
                      <!--  <span class="text-base block" ><span ng-if=" ( userPostDetail.UserDetails.Age > 0 ) " ng-bind="userPostDetail.UserDetails.Age + ' Y'">30</span><span ng-if="userPostDetail.UserDetails.Gender" ng-bind="( ( userPostDetail.UserDetails.Age > 0 ) ? ', ' : '' ) + ( ( userPostDetail.UserDetails.Gender == 1 ) ? 'M' : 'F' )" uib-tooltip="{{ ( userPostDetail.UserDetails.Gender == 1 ) ? 'Male' : 'Female' }}"></span><span ng-if="userPostDetail.UserDetails.City" ng-bind=" ', ' + userPostDetail.UserDetails.City "></span></span> 
                        <span ng-if="( userPostDetail.UserDetails.MartialStatus && userPostDetail.UserDetails.MartialStatusTxt )" class="text-sm-off bold">
                            <span ng-bind="userPostDetail.UserDetails.MartialStatusTxt"></span>
                            <span ng-if="( ( userPostDetail.UserDetails.MartialStatus == 2 ) || ( userPostDetail.UserDetails.MartialStatus == 3 ) )">
                                <span ng-if=" userPostDetail.UserDetails.RelationWithName"> with <a ng-bind="(userPostDetail.UserDetails.RelationWithName)"></a></span>
                            </span>
                            <span ng-if="( userPostDetail.UserDetails.MartialStatus == 4 )">
                                <span ng-if="userPostDetail.UserDetails.RelationWithName"> to <a ng-bind="(userPostDetail.UserDetails.RelationWithName)"></a></span>
                            </span>
                        </span> -->
                        <ul class="user-info-list collapse" id="userInfoToggle">
                            <li class="row">
                                <div class="col-xs-6">
                                    <label class="label-text">Posts <span class="text" ng-bind="': ' + userPostDetail.UserDetails.PostCount"></span></label>
                                </div>
                                <div class="col-xs-6">
                                    <label class="label-text">Comments <span class="text" ng-bind="': ' + userPostDetail.UserDetails.CommnetCount"></span></label>
                                </div>
                            </li>
                            <li class="row text-right" ng-if="userPostDetail.UserDetails.MemberSince">
                                <div class="col-xs-12"><small ng-bind="createDateObj(userPostDetail.UserDetails.MemberSince) | date : ' \'Member Since : \' dd \'-\' MMM yyyy'">Member Since : 02-Aug 2006</small></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
        <a class="ficon-arrow-down feed-expand collapsed" data-toggle="collapse" href="#userInfoToggle"></a>
    </div>
    <div ng-if="( !userPostDetailLoader && userPostDetail.UserDetails )" class="panel-body custom-scroll scroll-sms" style="height: 200px;">
        <div class="form-group no-bordered">
            <label class="control-label bolder">POST TAGS</label>
            <div class="input-icon">
                <i class="ficon-price-tag"></i>
                <tags-input
                    ng-model="userPostDetail.ActivityTags.Normal"
                    display-property="Name"
                    on-tag-added="addMemberTags('ACTIVITY', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
                    on-tag-removed="removeMemberTags('ACTIVITY', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
                    placeholder="Add more tags"
                    replace-spaces-with-dashes="false" 
                    template="tag1">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].activity.ActivityID, 0, 'ACTIVITY', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag1">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
        </div>
       <!-- <div class="form-group no-bordered">
            <div class="input-icon">
                <i class="ficon-happy"></i>
                <tags-input
                    ng-model="userPostDetail.ActivityTags.ActivityMood"
                    display-property="Name"
                    on-tag-added="addMemberTags('MOOD', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
                    on-tag-removed="removeMemberTags('MOOD', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
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
                    on-tag-added="addMemberTags('CLASSIFICATION', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
                    on-tag-removed="removeMemberTags('CLASSIFICATION', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
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
                    on-tag-added="addMemberTags('READER', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
                    on-tag-removed="removeMemberTags('READER', $tag, activityDataList[currentActivityIndex].activity.ActivityID, 0)"
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
            <div class="input-icon">
                <i class="ficon-profession"></i>
                <tags-input 
                    ng-model="userPostDetail.UserTags.UserProfession"
                    display-property="Name"
                    on-tag-added="addMemberTags('PROFESSION', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
                    on-tag-removed="removeMemberTags('PROFESSION', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
                    placeholder="Add more profession"
                    template="tag5">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].subject_user.UserID, 3, 'PROFESSION', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag5">
                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                    </div>
                </script>
            </div>
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
                <i class="ficon-price-tag"></i>
                <tags-input
                    ng-model="userPostDetail.UserTags.User_ReaderTag"
                    display-property="Name"
                    on-tag-added="addMemberTags('USER', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
                    on-tag-removed="removeMemberTags('USER', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
                    placeholder="Add user type"
                    template="tag7">
                    <auto-complete source="loadMemberTags($query, activityDataList[currentActivityIndex].subject_user.UserID, 3, 'USER', 1)" load-on-focus="true" min-length="0"></auto-complete>
                </tags-input>
                <script type="text/ng-template" id="tag7">
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
                    on-tag-added="addMemberTags('BRAND', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
                    on-tag-removed="removeMemberTags('BRAND', $tag, activityDataList[currentActivityIndex].subject_user.UserID, 3)"
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

<!-- <input type="hidden" value="<?php echo isset($UserStatus) ? $UserStatus : 2 ; ?>" id="hdnUserStatus"> -->
<input type="hidden"  name="hdnUserID" id="hdnUserID" value=""/>
<input type="hidden"  name="hdnUserGUID" id="hdnUserGUID" value=""/>
<input type="hidden"  name="hdnChangeStatus" id="hdnChangeStatus" value=""/>

<?php //$this->load->view('admin/users/persona/add_note_popup') ?>
<?php $this->load->view('admin/users/persona/user_persona') ?>
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
                        <img ng-if="user.ProfilePicture" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/'+user.ProfilePicture}}" alt="Profile Image" style="width: 48px; height: 48px" id="imgUser">
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


<div ng-include="newsletter_group_view"></div>                
