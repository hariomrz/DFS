<div id="WallPostCtrl" ng-controller="WallPostCtrl">
        <div ng-controller="NotesCtrl" id="NotesCtrl">
<div class="modal fade" tabindex="-1" role="dialog" id="user_persona">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body user-personas">
                <div class="personas-left">
                    <div class="personas-content">
                        <div class="personas">
                            <div class="users-thum">
                                <i class="ficon-edit" ng-click="setUpdateProfilePic(); getUserProfilePictures(userPersonaDetail.UserGUID);"></i>
                                <ul class="dp-slider">
                                    <li> 
                                        <img src="{{userPersonaDetail.ProfilePicture}}" >
                                    </li>
                                </ul>
                            </div>
                            <h2 class="user-name"><span ng-bind="userPersonaDetail.FirstName+' '+userPersonaDetail.LastName"></span> <i class="ficon-edit" ng-click="editDetail()"></i></h2>
                            <span><span ng-if="userPersonaDetail.Age!=''"><span ng-bind="userPersonaDetail.Age"></span> Y</span><span ng-if="userPersonaDetail.Gender!=''"><span ng-if="userPersonaDetail.Age!='' && userPersonaDetail.Gender!=''">,</span> <span ng-bind="userPersonaDetail.Gender"></span></span><span ng-if="userPersonaDetail.Location.City!='' && userPersonaDetail.Gender!=''">,</span> <span ng-bind="userPersonaDetail.Location.City+' '+userPersonaDetail.Location.Country"></span></span>
                        </div>
                        <ul class="detail-listing">
                            <li ng-if="userPersonaDetail.PhoneNumber!=''"><i class="ficon-phone"></i> <span ng-bind="userPersonaDetail.PhoneNumber"></span></li>
                            <li  ng-if="userPersonaDetail.Email!=''"><i class="ficon-envelope"></i> <span ng-bind="userPersonaDetail.Email"></span></li>
                            <li ng-if="userPersonaDetail.WorkExperience.length>0 && workexp.CurrentlyWorkHere==0" ng-repeat="workexp in userPersonaDetail.WorkExperience"> 
                                    <i class="ficon-portfolio"></i>                                
                                    <span >
                                        <span ng-bind="workexp.OrganizationName"></span>
                                        <span ng-if="$index>0"></span>
                                    </span> 
                            </li>
                            <li ng-if="userPersonaDetail.WorkExperience.length>0 && workexp.CurrentlyWorkHere==1" ng-repeat="workexp in userPersonaDetail.WorkExperience">
                                <i class="ficon-portfolio"></i> 
                                <span>
                                    <span ng-bind="'Working at '+workexp.OrganizationName"></span>
                                </span> 
                            </li>
                            <li ng-if="userPersonaDetail.MartialStatus == 2 || userPersonaDetail.MartialStatus == 3 || userPersonaDetail.MartialStatus == 4 || userPersonaDetail.MartialStatus == 5 || userPersonaDetail.family_details.length>0">
                                <i class="ficon-group"></i>
                                <span>
                                    <span ng-if="userPersonaDetail.Gender=='F' && userPersonaDetail.MartialStatus==4">Husband</span>
                                    <span ng-if="userPersonaDetail.Gender=='M' && userPersonaDetail.MartialStatus==4">Wife</span> 
                                    <span ng-if="userPersonaDetail.AdminRelationWithName != ''" ng-bind="userPersonaDetail.AdminRelationWithName"></span>
                                    <span ng-if="userPersonaDetail.AdminRelationWithName != '' && userPersonaDetail.RelationWithAge!=''" ng-bind="userPersonaDetail.RelationWithAge+' Y'"></span>

                                    <span ng-if="userPersonaDetail.AdminRelationWithName != '' && userPersonaDetail.RelationWithAge!='' && userPersonaDetail.family_details.length>0">,</span>

                                    <span ng-repeat="family in userPersonaDetail.family_details">
                                        <span ng-if="family.FGender=='2'">Daughter</span>
                                        <span ng-if="family.FGender=='1'">Son</span>
                                        <span ng-if="family.FGender=='3'">Father</span> 
                                        <span ng-if="family.FGender=='4'">Mother</span> 
                                        <span ng-if="family.FGender=='5'">Husband</span> 
                                        <span ng-if="family.Age!=''" ng-bind="family.Age+' Y'"></span>
                                        <span ng-if="$index!=(userPersonaDetail.family_details.length-1)">,</span>
                                    </span>
                                </span>
                            </li>
                        </ul>
                        <div class="user-status">
                        <span ng-if="userPersonaDetail.HighlyActivePercentage>0">Highly active (In top <span ng-bind="userPersonaDetail.HighlyActivePercentage"></span>%)</span> 

                        <!-- <i class="ficon-stats"></i> -->
                        <i class="ficon-activity-level-high" uib-tooltip="High" ng-if="userPersonaDetail.NowScore>userPersonaDetail.BeforeScore"></i> 
                        <i class="ficon-activity-level-low" uib-tooltip="Low" ng-if="userPersonaDetail.NowScore<userPersonaDetail.BeforeScore"></i>
                        <i class="ficon-activity-level-moderate" uib-tooltip="Moderate" ng-if="userPersonaDetail.NowScore==userPersonaDetail.BeforeScore"></i>
                        </div>
                    </div>
                    <!-- <div class="personas-content">
                        <div class="pers-header" ng-click="editNetworkDetails()">Network <i class="ficon-edit"></i></div>
                        <ul class="network-detail">
                            <li>
                                <a target="_blank" ng-href="{{'< ?php echo site_url() ?>'+userPersonaDetail.ProfileURL}}"><i class="ficon-vsocial"></i></a>
                                <div class="data-list" ng-if="Settings.m10 == 1">
                                    <label ng-bind="userPersonaDetail.friends_n_followers.Friends"></label>
                                    <span>Friends</span>
                                </div>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.friends_n_followers.Follow"></label>
                                    <span>Following</span>
                                </div>
                            </li>
                            <li>
                                <a target="_blank" ng-if="userPersonaDetail.Admin_Facebook_profile_URL" ng-href="{{userPersonaDetail.Admin_Facebook_profile_URL}}"><i class="ficon-facebook"></i></a>
                                <i ng-if="!userPersonaDetail.Admin_Facebook_profile_URL" class="ficon-facebook"></i>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.NoOfFriendsFB"></label>
                                    <span>Friends</span>
                                </div>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.NoOfFollowersFB"></label>
                                    <span>Followers</span>
                                </div>
                            </li>
                            <li>
                                <a target="_blank" ng-if="userPersonaDetail.Admin_Linkedin_profile_URL" ng-href="{{userPersonaDetail.Admin_Linkedin_profile_URL}}"><i class="ficon-linkedin"></i></a>
                                <i ng-if="!userPersonaDetail.Admin_Linkedin_profile_URL" class="ficon-linkedin"></i>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.NoOfConnectionsIn"></label>
                                    <span>Connections</span>
                                </div>
                            </li>
                        </ul>
                    </div> -->
                    <div class="personas-footer">
                        <div class="button-group">
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==1 || userPersonaDetail.StatusID==2 || userPersonaDetail.StatusID==6 || userPersonaDetail.StatusID==7" ng-click="block_unblock_toggle(userPersonaDetail.UserID,4);">Block</button>
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==4" ng-click="block_unblock_toggle(userPersonaDetail.UserID,2);">Unblock </button>
                           <!-- <div class="dropdown" ng-if="userPersonaDetail.StatusID==1 || userPersonaDetail.StatusID==2 || userPersonaDetail.StatusID==6 || userPersonaDetail.StatusID==7">
                                <button class="btn btn-default" data-toggle="dropdown">Suspend</button>
                                <ul class="dropdown-menu" data-type="stopPropagation">
                                    <li>
                                        <div class="form-group"  ng-show="userPersonaDetail.StatusID==2" >
                                          <div class="col-sm-12">
                                                <div class="form-group dob-field "> 
                                                    <input type="text" ng-init="calldatepickersuspend()" class="form-control datepicker" id="datesuspend" ng-model="AccountSuspendTill" value="">
                                                    <label class="ficon-calendar" for="datesuspend"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <button class="btn btn-default btn-link" ng-click="suspend_user_toggle(userPersonaDetail.UserID,23);">Forever</button>
                                    </li>
                                </ul>
                            </div>
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==23"  ng-click="suspend_user_toggle(userPersonaDetail.UserID,2);">Unsuspend</button>                            
-->
                        </div>
                        <div class="footer-info">
                            <span ng-if="userPersonaDetail.StatusID==23 && userPersonaDetail.AccountSuspendTill"><i class="ficon-info"></i> User has been suspended till <span ng-bind="createDateObject(userPersonaDetail.AccountSuspendTill) | date : 'dd MMM'"></span></span>
                            <span ng-if="userPersonaDetail.StatusID==23 && !userPersonaDetail.AccountSuspendTill"><i class="ficon-info"></i> User has been suspended.</span>
                            <span>Member since : {{userPersonaDetail.membersince}}</span>
                            <span>Last Login : {{createDateObject(utc_to_time_zone(userPersonaDetail.lastlogindate)) | date : 'dd MMM \'at\' hh:mm a'}}</span>
                        </div>
                    </div>
                    <div class="info-edit-mode" ng-show="editDetails">
                        <div class="modal-header">
                            <button type="button" class="close"><i class="ficon-cross" ng-click="close_detail_box();"></i></button>
                            <h4 class="modal-title">{{editTitle}}</h4>
                        </div>
                        <div class="modal-body">
                            <div ng-show="editPersonalDetail">
                                <div class="row">
                                    
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Full Name</label>
                                            <input type="text" class="form-control"  value="" ng-model="profile.FullName">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Phone number</label>
                                            <input type="text" class="form-control" disabled="true" value="{{profile.PhoneNumber}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6" ng-init="GenderOptions=[{val:'1',label:'Male'},{val:'2',label:'Female'},{val:'0',label:'Other'}]">
                                      <div class="form-group">
                                        <label class="control-label">{{::lang.gender}}</label>
                                        <select ng-model="profile.AdminGender" id="Gender" name="GenderEdit" ng-value="Gender" ng-options="Gender.val as Gender.label for Gender in GenderOptions" class="form-control" data-chosen="" data-disable-search="true">
                                        </select>
                                      </div>
                                    </div>
                                    <!-- <div class="col-sm-6" ng-init="GenderOptions=[{val:'1',text:'Male'},{val:'2',text:'Female'},{val:'0',text:'Other'}]">
                                        <div class="form-group">
                                            <label for="">Gender</label>
                                            <select class="chosen-select form-control" ng-model="profile.AdminGender" ng-options="GenderOption.val as GenderOption.text for GenderOption in GenderOptions" ng-value="GenderOption">
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group dob-field ">
                                            <label for="">Date of Birth</label>
                                            <input type="text" class="form-control" ng-model="profile.DOB" id="dob" value="">
                                            <label class="ficon-calendar" for="dob"></label>
                                        </div>
                                        <div class="form-group">
                                            <input type="checkbox" id="IsDOBApprox" name="IsDOBApprox" ng-checked="profile.IsDOBApprox == '1'">
                                            <label for="">Approx</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <label for="">Location</label>
                                        <!-- <div class="form-group">
                                            <input type="text" class="form-control" ng-model="profile.Location" id="hometown" value="Indore, India">
                                        </div> -->
                                        <!-- <a class="arrow-right" data-toggle="dropdown" role="button"> 
                                            <span class="text">Ward</span> 
                                            <span ng-bind="profile.Locality.Name" class="text-small"></span>
                                        </a> -->
                                        <!-- <ul class="dropdown-menu filters-dropdown" data-type="stopPropagation">
                                            <li> -->
                                                <div class="form-group"> 
                                                    <select id="select_ward" chosen class="form-control" ng-options="wards.LocalityID as wards.Name+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in locality_list" data-ng-model="profile.Locality.LocalityID">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            <!-- </li>
                                        </ul> -->
                                    </div>
                                    
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Email Id</label>
                                            <input type="email" class="form-control" ng-model="profile.Email" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Works at</label>
                                            <input type="text" class="form-control" ng-model="profile.WorkExperience" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12" ng-init="IncomeOptions=[{val:'1',label:'Low'},{val:'2',label:'Medium'},{val:'3',label:'High'}]">
                                        <div class="form-group">
                                            <label for="">Income Level</label>
                                            <select ng-model="profile.IncomeLevel" id="Income" name="Income" ng-value="Income" ng-options="Income.val as Income.label for Income in IncomeOptions" class="form-control" data-chosen="" data-disable-search="true">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12" ng-init="RelationOptions=[{val:'1',label:'Single'},{val:'2',label:'In a relationship'},{val:'3',label:'Engaged'},{val:'4',label:'Married'},{val:'5',label:'Its complicated'},{val:'6',label:'Separated'},{val:'7',label:'Divorced'}]">
                                        <div class="form-group">
                                            <label for="">Marital Status</label>
                                            <select ng-model="profile.MaritalStatus" id="Marital" name="Marital" ng-value="Marital" ng-options="Relation.val as Relation.label for Relation in RelationOptions" class="form-control" data-chosen="" data-disable-search="true" ng-change="updateRelationshipOptions()">
                                            </select>
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-12" ng-init="RelationshipOptions=[{val:'1',Relation:''},{val:'2',Relation:''},{val:'3',Relation:''},{val:'4',Relation:''},{val:'5',Relation:''},{val:'6',Relation:''},{val:'7',Relation:''}]">
                                        <div class="form-group">
                                            <label for="">Marital Status</label>
                                            <select data-ng-change="showRelationWith();" class="chosen-select form-control" ng-model="profile.MaritalStatus" ng-options="Relationship.val as Relationship.Relation for Relationship in RelationshipOptions">
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-12">
                                        <div class="row" ng-if="showRelationshipOptions==1" ng-init="InitRelationToNew();">
                                            <div class="col-sm-5" >
                                                <div class="form-group">
                                                    <input type="text" ng-model="RelationWithInput" data-requiredmessage="Required" data-msglocation="errorTo" data-mandatory="false" data-controltype="relationfield" value="" id="RelationTo" class="form-control ui-autocomplete-input" placeholder="Start typing" uix-input="" />
                                              <label id="errorTo" class="error-block-overlay"></label>
                                                </div>
                                            </div>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <div class="input-text">
                                                        <input type="text" class="form-control" value="" ng-model="profile.RelationWithDOB" placeholder="Age">
                                                    </div>
                                                    <!-- <a class="remove-link button-link">Remove</a> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-12">
                                        <div class="row" ng-show="showRelationOption==1" ng-init="InitRelationTo();">
                                            <div class="col-sm-5" >
                                                <div class="form-group">
                                                    <input type="text" ng-model="RelationWithInputEdit" data-requiredmessage="Required" data-msglocation="errorTo" data-mandatory="false" data-controltype="relationfield" value="" id="RelationTo" class="form-control ui-autocomplete-input" placeholder="Start typing" uix-input="" />
                                              <label id="errorTo" class="error-block-overlay"></label>
                                                </div>
                                            </div>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <div class="input-text">
                                                        <input type="text" class="form-control" value="" ng-model="profile.RelationWithDOB" placeholder="Age">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-12">
                                        <div class="">
                                            <label for="">Lives with</label>
                                            <a class="pull-right button-link" ng-click="add_relation();">Add More</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <label for="">Type</label>
                                            </div>
                                            <div class="col-sm-7">
                                                <label for="">Age (Years)</label>
                                            </div>
                                        </div>
                                        <div class="row" ng-repeat="family in profile.family">
                                            <div class="col-sm-5">
                                                <div class="form-group">
                                                    <select class="chosen-select form-control" ng-model="family.FGender">
                                                        <option value="" selected="">Select</option>
                                                        <option value="1">Son</option>
                                                        <option value="2">Daughter</option>
                                                        <option value="3">Father</option>
                                                        <option value="4">Mother</option>
                                                        <option value="5">Husband</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <div class="input-text">
                                                        <select class="chosen-select form-control" ng-model="family.Age">
                                                            <option value="" selected="">Select</option>
                                                            <?php for($i=1;$i<=100;$i++): ?>
                                                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                    <a class="remove-link button-link" ng-click="remove_relation($index);">Remove</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary pull-right" ng-click="updatePersonalDetail()">Update</button>
                                    </div>
                                </div>
                            </div>
                           
                            <!-- Profile Picture Start -->
                                <div class="updated-profile" ng-cloak ng-show="updateProfilePic">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <button ngf-select="uploadProfilePictureByAdmin($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);" class="btn btn-default btn-block">Upload new</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="profile-pic-edit">
                                                <img src="{{userPersonaDetail.ProfilePicture}}" >
                                            </div>
                                        </div> 
                                    </div>
                                    <div class="row m-t">
                                         <div class="col-sm-12">
                                             <ul class="uploaded-img-listing">
                                                 <li ng-repeat="user_pic in userProfilePictures" ng-init="checkActiveStatus(userPersonaDetail.ProfilePicture,'<?php echo IMAGE_SERVER_PATH ?>upload/profile/'+user_pic.ImageName,user_pic.MediaGUID)" ng-class="(user_pic.IsActive==1) ? 'active' : '' ;" ng-click="setActiveStatus(user_pic.MediaGUID)">
                                                     <div class="uploaded-view">
                                                         <img ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>upload/profile/'+user_pic.ImageName}}" >
                                                     </div>
                                                     <i class="ficon-check"></i>
                                                     <input type="radio" name="uploadedImg">
                                                 </li>
                                             </ul>
                                         </div> 
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12"> 
                                            <div class="button-group">
                                                <button ng-click="setProfilePicByAdmin(userPersonaDetail.UserID);" class="btn btn-primary pull-right">Update</button>
                                                <button ng-click="close_detail_box();" class="btn btn-primary btn-link pull-right">Back</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Profile Picture Ends --> 
                        </div>
                        
                    </div>
                    
                </div>
                <div class="personas-right">
                    <div class="overlay-div" ng-if="(editPersonalDetail || editNetworkDetail || editDetails || updateProfilePic)"></div>
                    <div class="navbar-tabs">
                        <ul class="tabs-nav clearfix">
                            <li class="active"><a href="#General" ng-click="setShowActivity(false)" data-toggle="tab">General</a></li>
                            <li><a href="#Activities" ng-click="setShowActivity(true);" data-toggle="tab">Activities</a></li>
                            <li><a href="#Communication" ng-click="setShowActivity(false); getCommunications(userPersonaDetail.UserID)" data-toggle="tab">Communication</a></li>
                            <li><a href="#Notes" ng-click="setShowActivity(false)" data-toggle="tab">Notes</a></li>
                            <li><a href="#Usage" ng-click="setShowActivity(false); getUsageData(userPersonaDetail.UserID)" data-toggle="tab">Usage</a></li>
                        </ul>
                    </div>
                    <div class="tab-block">
                        <div class="tab-content">
                            <div class="tab-pane fade active in activities-tabs" id="General">
                              <!--  <div class="section-content border-bottom clearfix">
                                <button ng-click="openwindow(userPersonaDetail.UserID);" class="btn btn-default icons print-button btn-sm pull-right">
                                    <i class="ficon-printer"></i> PRINT
                                </button>
                                    
                                </div>
                                                            -->
                                <p class="quotes-view" ng-if="userPersonaDetail.UserWallStatus!=''">“<span ng-bind="userPersonaDetail.UserWallStatus"></span>”</p>
                                <div class="section-content border-bottom">
                                    <h2>Contributions</h2>
                                    <ul class="contributions-list row">
                                        <li class="col-sm-3">
                                            <i class="ficon-add-post ficon-blue"></i>
                                            <label ng-bind="userPersonaDetail.ActivityCount"></label>
                                            <span>Posts</span>
                                        </li>
                                        <li class="col-sm-3">
                                            <i class="ficon-add-post ficon-blue"></i>
                                            <label ng-bind="userPersonaDetail.UQC"></label>
                                            <span>Question asked</span>
                                        </li>
                                        <li class="col-sm-3">
                                            <i class="ficon-comment ficon-blue"></i>
                                            <label ng-if="userPersonaDetail.CommentCount" ng-bind="userPersonaDetail.CommentCount"></label>
                                            <label ng-if="!userPersonaDetail.CommentCount">0</label>
                                            <span>Response posted</span>
                                        </li>
                                        
                                        <li class="col-sm-3">
                                            <i class="ficon-heart ficon-red"></i>
                                            <label ng-if="userPersonaDetail.LikeRecievedCount" ng-bind="userPersonaDetail.LikeRecievedCount"></label>
                                            <label ng-if="!userPersonaDetail.LikeRecievedCount">0</label>
                                            <span>Likes Received</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="section-content border-bottom">
                                    <div class="row">
                                        <div class="col-sm-3" ng-init="GenderOptions=[{val:'1',label:'Male'},{val:'2',label:'Female'},{val:'0',label:'Other'}]">
                                        <div class="form-group">
                                            <label class="control-label">{{::lang.gender}}</label>
                                            <select ng-model="userPersonaDetail.AdminGender" id="Gender" name="GenderEdit" ng-value="Gender" ng-options="Gender.val as Gender.label for Gender in GenderOptions" class="form-control" data-chosen="" data-disable-search="true">
                                            </select>
                                        </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group dob-field ">
                                                <label class="control-label">Date of Birth</label>
                                                <input type="text" class="form-control" ng-model="userPersonaDetail.DOB" id="dob1" value="">
                                                <label class="ficon-calendar" for="dob1"></label>
                                            </div>
                                            <div class="form-group">
                                                <input type="checkbox" id="IsDOBApprox1" name="IsDOBApprox" ng-checked="userPersonaDetail.IsDOBApprox == '1'">
                                                <label for="">Approx</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3" ng-init="IncomeOptions=[{val:'1',label:'Low'},{val:'2',label:'Medium'},{val:'3',label:'High'}]">
                                            <div class="form-group">
                                                <label class="control-label">Income Level</label>
                                                <select ng-model="userPersonaDetail.IncomeLevel" id="Income" name="Income" ng-value="Income" ng-options="Income.val as Income.label for Income in IncomeOptions" class="form-control" data-chosen="" data-disable-search="true">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                            <label class="" style="padding: 40px 0px 0px 0px;"></label>
                                                <button class="btn btn-default border-primary m-b-xs" ng-click="saveUserDetails()">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- <div class="section-content">
                                    <h2>Interests</h2>
                                    <div id="chart_div" class="chart-view"></div>
                                </div> -->
                                <div class="section-content">
                                    <h2>Members Tags</h2>
                                    <div class="per-member-tags clearfix">
                                        <!-- <div class="form-group no-bordered">
                                            <div class="input-icon">
                                                <i class="ficon-profession"></i>
                                                <tags-input ng-model="professionTag" 
                                                placeholder="Add profession" 
                                                enforce-max-tags
                                                display-property="Name" 
                                                replace-spaces-with-dashes="false" 
                                                add-from-autocomplete-only="true"
                                                template="tag-template"
                                                on-tag-added="tagAddedPersona($tag,'PROFESSION')"
                                                on-tag-removed="tagRemovedPersona($tag,'PROFESSION')"
                                                >
                                                    <auto-complete source="loadLinkTags($query,'PROFESSION')" template="tag-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="tag-template">
                                                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="tag-template1">
                                                    <div class="autocomplete-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                    <div class="right-panel">

                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                    </div>
                                                </script>
                                            </div>
                                        </div>
                                       <div class="form-group no-bordered">
                                            <div class="input-icon">
                                                <i class="ficon-interest"></i>
                                                <tags-input ng-model="interestsTag" 
                                                placeholder="Add interests" 
                                                enforce-max-tags
                                                display-property="Name" 
                                                add-from-autocomplete-only="false"
                                                template="interests-template"
                                                on-tag-added="tagAddedInterest($tag,'INTEREST')"
                                                on-tag-removed="tagRemovedInterest($tag,'INTEREST')" key-property="ModuleEntityCount">
                                                    <auto-complete source="loadInterest($query)"  template="interests-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="interests-template">
                                                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="interests-template1">
                                                    <div class="autocomplete-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <div class="right-panel">

                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                    </div>
                                                </script>
                                            </div>
                                        </div>
                                                            -->
                                        <div class="form-group no-bordered">
                                            <div class="input-icon">
                                                <i class="ficon-price-tag"></i>
                                                <tags-input ng-model="addsuerType" 
                                                    placeholder="Add user type" 
                                                    display-property="Name" 
                                                    replace-spaces-with-dashes="false" 
                                                    add-from-autocomplete-only="true" 
                                                    template="reader-template"
                                                    on-tag-added="tagAddedPersona($tag,'READER')"
                                                    on-tag-removed="tagRemovedPersona($tag,'READER')">
                                                    <auto-complete source="loadLinkTags($query,'READER')" template="reader-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="reader-template">
                                                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="reader-template1">
                                                    <div class="autocomplete-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <div class="right-panel">

                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                    </div>
                                                </script>
                                            </div>
                                        </div>
                                       <!-- <div class="form-group no-bordered">
                                            <div class="input-icon">
                                                <i class="ficon-brand"></i>
                                                <tags-input ng-model="addBrand" 
                                                placeholder="Add Brand" 
                                                template="brand-template"
                                                enforce-max-tags
                                                display-property="Name" 
                                                add-from-autocomplete-only="false"
                                                on-tag-added="tagAddedPersona($tag,'BRAND')"
                                                on-tag-removed="tagRemovedPersona($tag,'BRAND')"
                                                >
                                                    <auto-complete source="loadLinkTags($query,'BRAND')" template="brand-template1"  load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="brand-template">
                                                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="brand-template1">
                                                    <div class="autocomplete-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                    <div class="right-panel">

                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                    </div>
                                                </script>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                                <div class="section-content">
                                    <h2>Reason of joining <i ng-cloak ng-if="!EditReasonOfJoining" ng-click="setEditReasonOfJoining(true)" class="ficon-edit"></i></h2>
                                    <p ng-cloak ng-if="!EditReasonOfJoining" ng-bind="userPersonaDetail.ReasonOfJoining"></p>
                                    <textarea ng-cloak ng-show="EditReasonOfJoining" ng-model="userPersonaDetail.ReasonOfJoining" id="EditReasonOfJoining" class="form-control"></textarea>
                                </div>
                                <div ng-cloak ng-show="EditReasonOfJoining" class="row">
                                    <div class="col-xs-12">
                                        <button ng-click="saveEditReasonOfJoining(userPersonaDetail.ReasonOfJoining,userPersonaDetail.UserID)" type="button" class="btn btn-default pull-right border-primary">Save</button>
                                    </div>
                                </div>
                                <div class="section-content">
                                    <h2>Problems/Complaints <i ng-cloak ng-if="!EditProblemsNComplaints" ng-click="setEditProblemsNComplaints(true)" class="ficon-edit"></i></h2>
                                    <p ng-cloak ng-if="!EditProblemsNComplaints" ng-bind="userPersonaDetail.ProblemsNComplaints"></p>
                                    <textarea ng-cloak ng-show="EditProblemsNComplaints" ng-model="userPersonaDetail.ProblemsNComplaints" id="EditProblemsNComplaints" class="form-control"></textarea>
                                </div>
                                <div ng-cloak ng-show="EditProblemsNComplaints" class="row">
                                    <div class="col-xs-12">
                                        <button ng-click="saveEditProblemsNComplaints(userPersonaDetail.ProblemsNComplaints,userPersonaDetail.UserID)" type="button" class="btn btn-default pull-right border-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                            
                            <?php $this->load->view('admin/users/persona/activity');?>
                            <?php $this->load->view('admin/users/persona/communications');?>
                            <?php $this->load->view('admin/users/persona/notes');?>
                            <?php $this->load->view('admin/users/persona/usage');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php $this->load->view('admin/users/persona/add_note') ?>
</div>
</div>
<?php $this->load->view('admin/users/persona/profile_picture') ?>

<style type="text/css">
@media print {
  body * {
    visibility: hidden;
  }
  .user-personas, .user-personas * {
    visibility: visible;
  }
  .user-personas {
    position: absolute;
    left: 0;
    top: 0;
  }
}
</style>
