<div class="modal fade in" tabindex="-1" role="dialog" style="display: none;" id="user_persona">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body user-personas">
                <div class="personas-left">
                    <div class="personas-content">
                        <div class="personas">
                            <div class="users-thum">
                                <ul class="dp-slider">
                                    <li><img src="{{userPersonaDetail.ProfilePicture}}" alt=""></li>
                                </ul>
                            </div>
                            <h2 class="user-name"><span ng-bind="userPersonaDetail.FirstName+' '+userPersonaDetail.LastName"></span> <i class="ficon-edit" ng-click="editDetail()"></i></h2>
                            <span><span ng-bind="userPersonaDetail.Age"></span> Y, <span ng-bind="userPersonaDetail.Gender"></span> , <span ng-bind="userPersonaDetail.Location.City+' '+userPersonaDetail.Location.Country"></span></span>
                        </div>
                        <ul class="detail-listing">
                            <li><i class="ficon-phone"></i> <span ng-bind="userPersonaDetail.PhoneNumber"></span></li>
                            <li><i class="ficon-envelope"></i> <span ng-bind="userPersonaDetail.Email"></span></li>
                            <li ng-if="userPersonaDetail.WorkExperience.length>0 ">
                                <span ng-repeat="workexp in userPersonaDetail.WorkExperience" ng-if="workexp.CurrentlyWorkHere==0">
                                    <span >
                                        <span ng-bind="workexp.OrganizationName"></span>
                                        <span ng-if="$index>0">,</span>
                                    </span>
                                </span>
                            </li>
                            <li ng-if="userPersonaDetail.WorkExperience.length>0">
                                <span ng-repeat="workexp in userPersonaDetail.WorkExperience">
                                    <span ng-if="workexp.CurrentlyWorkHere==1">
                                        <span ng-bind="'Working at '+workexp.OrganizationName"></span>
                                    </span>
                                </span>
                            </li>
                            <li ng-if="userPersonaDetail.MartialStatus==4">
                                <i class="ficon-group"></i>
                                <span>
                                    <span ng-if="userPersonaDetail.Gender=='F'">Husband</span>
                                    <span ng-if="userPersonaDetail.Gender=='M'">Wife</span> 
                                    <span ng-bind="userPersonaDetail.RelationWithName"></span>
                                    <span ng-if="userPersonaDetail.RelationWithAge!=''" ng-bind="userPersonaDetail.RelationWithAge+' Y'"></span>, 
                                    <span ng-repeat="family in userPersonaDetail.family_details">
                                        <span ng-if="family.Gender=='F'">Daughter</span>
                                        <span ng-if="family.Gender=='M'">Son</span> 
                                        <span ng-if="family.Age!=''" ng-bind="family.Age+' Y'"></span>
                                        <span ng-if="$index!=(userPersonaDetail.family_details.length-1)">,</span>
                                    </span>
                                </span>
                            </li>
                        </ul>
                        <div class="user-status">Highly active (In top 10%) <i class="ficon-stats"></i></div>
                    </div>
                    <div class="personas-content">
                        <div class="pers-header" ng-click="editNetworkDetails()">Network <i class="ficon-edit"></i></div>
                        <ul class="network-detail">
                            <li>
                                <i class="ficon-vsocial"></i>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.friends_n_followers.Friends"></label>
                                    <span>Friends</span>
                                </div>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.friends_n_followers.Follow"></label>
                                    <span>Followers</span>
                                </div>
                            </li>
                            <li>
                                <i class="ficon-facebook"></i>
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
                                <i class="ficon-linkedin"></i>
                                <div class="data-list">
                                    <label ng-bind="userPersonaDetail.NoOfConnectionsIn"></label>
                                    <span>Connections</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="personas-footer">
                        <div class="button-group">
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==2" ng-click="block_unblock_toggle(userPersonaDetail.UserID,4);">Block</button>
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==2"  ng-click="suspend_user_toggle(userPersonaDetail.UserID,23);">Suspend</button>
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==4" ng-click="block_unblock_toggle(userPersonaDetail.UserID,2);">Unblock </button>
                            <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==23"  ng-click="suspend_user_toggle(userPersonaDetail.UserID,2);">Unsuspend</button>
                            <div class="form-group"  ng-show="userPersonaDetail.StatusID==2" >
                              <div class="col-sm-6">
                                <div class="form-group dob-field ">
                                    <label for="">Date of Birth</label>
                                    <input type="text" class="form-control datepicker" ng-model="AccountSuspendTill" id="date" value="">
                                    <label class="ficon-calendar" for="date"></label>
                                </div>
                            </div>
                            </div>
                            <button class="btn btn-default icons"><i class="ficon-printer"></i></button>
                        </div>
                        <div class="footer-info">
                            <span>Member since : {{userPersonaDetail.membersince}}</span>
                            <span>Last Login : {{userPersonaDetail.lastlogindate}}</span>
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
                                            <label for="">First Name</label>
                                            <input type="text" class="form-control" disabled="true" value="{{profile.FirstName}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Gender</label>
                                            <select class="chosen-select form-control" ng-model="profile.AdminGender">
                                                <option value="0" selected="">Male</option>
                                                <option value="1">Female</option>
                                                <option value="2">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group dob-field ">
                                            <label for="">Date of Birth</label>
                                            <input type="text" class="form-control datepicker" ng-model="profile.DOB" id="dob" value="">
                                            <label class="ficon-calendar" for="dob"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Location</label>
                                            <input type="text" class="form-control" ng-model="profile.Location" id="hometown" value="Indore, India">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Phone number</label>
                                            <input type="text" class="form-control" ng-model="profile.PhoneNumber" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Email Id</label>
                                            <input disabled="true" type="email" class="form-control" ng-model="profile.Email" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Works at</label>
                                            <input type="text" class="form-control" ng-model="profile.WorkExperience" value="">
                                        </div>
                                    </div>
                                    <div class="col-sm-12" ng-init="RelationshipOptions=[{val:'1',Relation:'Single'},{val:'2',Relation:'In a relationship'},{val:'3',Relation:'Engaged'},{val:'4',Relation:'Married'},{val:'5',Relation:'Its complicated'},{val:'6',Relation:'Separated'},{val:'7',Relation:'Divorced'}]">
                                        <div class="form-group">
                                            <label for="">Marital Status</label>
                                            <select data-ng-change="showRelationWith();" class="chosen-select form-control" ng-model="profile.MaritalStatus" ng-options="Relationship.val as Relationship.Relation for Relationship in RelationshipOptions">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
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
                                                    <a class="remove-link button-link">Remove</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="">
                                            <label for="">Lives with</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <label for="">Type</label>
                                            </div>
                                            <div class="col-sm-7">
                                                <label for="">DOB</label>
                                            </div>
                                        </div>
                                        <div class="row" ng-repeat="family in profile.family">
                                            <div class="col-sm-5">
                                                <div class="form-group">
                                                    <select class="chosen-select form-control" ng-model="family.FGender">
                                                        <option value="" selected="">Select</option>
                                                        <option value="0">Son</option>
                                                        <option value="1">Daughter</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-7">
                                                <div class="form-group">
                                                    <div class="input-text">
                                                        <input type="text" class="form-control" value="" ng-model="family.BirthYear" placeholder="y-m-d">
                                                    </div>
                                                    <a class="remove-link button-link" ng-click="remove_relation($index);">Remove</a>
                                                    <a class="button-link" ng-if="$index==(profile.family.length-1)" ng-click="add_relation();">Add More</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary pull-right" ng-click="updatePersonalDetail()">Update</button>
                                    </div>
                                </div>
                            </div>

                            <div ng-show="editNetworkDetail" class="network-edit">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="">
                                                <label for="">Vsocial</label>
                                            </div>
                                            <input type="text" readonly="true" class="form-control" value="{{SiteURL+userPersonaDetail.ProfileURL}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Friends</label>
                                           <input type="text" readonly="true" class="form-control" value="{{userPersonaDetail.friends_n_followers.Friends}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group dob-field ">
                                            <label for="">Followers</label>
                                           <input type="text" readonly="true" class="form-control" value="{{userPersonaDetail.friends_n_followers.Follow}}">
                                        </div>
                                    </div>
                                </div>  
                                <div class="row">  
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="">
                                                <label for="">Facebook</label> <a class="pull-right button-link" ng-click="empty_facebook_info();">Remove</a>
                                            </div>
                                            <input type="text" class="form-control" ng-model="network.Admin_Facebook_profile_URL" value="{{userPersonaDetail.Admin_Facebook_profile_URL}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Friends</label>
                                           <input type="text" class="form-control" ng-model="network.NoOfFriendsFB" value="{{userPersonaDetail.NoOfFriendsFB}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group dob-field ">
                                            <label for="">Followers</label>
                                           <input type="text" class="form-control" ng-model="network.NoOfFollowersFB" value="{{userPersonaDetail.NoOfFollowersFB}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">    
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="">
                                                <label for="">Linkedin</label> <a class="pull-right button-link" ng-click="empty_linkedin_info();">Remove</a>
                                            </div>
                                            <input type="text" class="form-control" ng-model="network.Admin_Linkedin_profile_URL" value="{{userPersonaDetail.Admin_Linkedin_profile_URL}}">
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">Friends</label>
                                           <input type="text" class="form-control" ng-model="network.facebook_url" value="{{}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group dob-field ">
                                            <label for="">Followers</label>
                                           <input type="text" class="form-control" ng-model="network.facebook_url" value="{{}}">
                                        </div>
                                    </div> -->
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Connections</label>
                                            <input type="text" class="form-control" ng-model="network.NoOfConnectionsIn" value="{{userPersonaDetail.NoOfConnectionsIn}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">    
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Twitter</label><a class="pull-right button-link" ng-click="empty_tw_info();">Remove</a>
                                            <input type="text" class="form-control" value="{{userPersonaDetail.Admin_Twitter_profile_URL}}" ng-model="network.Admin_Twitter_profile_URL"  placeholder="Enter twitter url">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="">Followers</label>
                                            <input type="text" class="form-control" ng-model="network.NoOfFollowersTw" value="{{userPersonaDetail.NoOfFollowersTw}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">    
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary pull-right" ng-click="updateDetail(userPersonaDetail.UserID)">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="personas-right">
                    <div class="navbar-tabs">
                        <ul class="tabs-nav clearfix">
                            <li class="active"><a href="#General" data-toggle="tab">General</a></li>
                            <li><a href="#Activities" data-toggle="tab">Activities</a></li>
                            <li><a href="#Communication" data-toggle="tab">Communication</a></li>
                            <li><a href="#Notes" data-toggle="tab">Notes</a></li>
                            <li><a href="#Usage" data-toggle="tab">Usage</a></li>
                        </ul>
                    </div>
                    <div class="tab-block">
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="General">
                                <p class="quotes-view" ng-if="userPersonaDetail.UserWallStatus!=''">“<span ng-bind="userPersonaDetail.UserWallStatus"></span>”</p>
                                <div class="section-content border-bottom">
                                    <h2>Contributions</h2>
                                    <ul class="contributions-list row">
                                        <li class="col-sm-4">
                                            <i class="ficon-add-post ficon-blue"></i>
                                            <label ng-bind="userPersonaDetail.ActivityCount"></label>
                                            <span>Posts</span>
                                        </li>
                                        <li class="col-sm-4">
                                            <i class="ficon-comment ficon-blue"></i>
                                            <label ng-bind="userPersonaDetail.CommentCount"></label>
                                            <span>Comments</span>
                                        </li>
                                        <li class="col-sm-4">
                                            <i class="ficon-heart ficon-red"></i>
                                            <label ng-bind="userPersonaDetail.LikeRecievedCount"></label>
                                            <span>Likes Received</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="section-content">
                                    <h2>Interests</h2>
                                    <div id="chart_div"></div>
                                </div>
                                <div class="section-content">
                                    <h2>Members Tags</h2>
                                    <div class="per-member-tags clearfix">
                                        <div class="form-group no-bordered">
                                            <label class="control-label bolder">MEMBER TAGS</label>
                                            <div class="input-icon">
                                                <i class="ficon-profession"></i>
                                                <tags-input ng-model="professionTag" 
                                                placeholder="Add more profession" 
                                                enforce-max-tags
                                                display-property="Name" 
                                                add-from-autocomplete-only="false"
                                                template="tag-template"
                                                on-tag-added="tagAddedPersona($tag,'PROFESSION')"
                                                on-tag-removed="tagRemovedPersona($tag,'PROFESSION')"
                                                >
                                                    <auto-complete source="loadLinkTags($query,'PROFESSION')" template="tag-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="tag-template">
                                                    <div class="tag-template">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="tag-template1">
                                                    <div class="autocomplete-template">
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
                                                placeholder="Add more interests" 
                                                enforce-max-tags
                                                display-property="Name" 
                                                add-from-autocomplete-only="false"
                                                template="interests-template"
                                                on-tag-added="tagAddedInterest($tag,'INTEREST')"
                                                on-tag-removed="tagRemovedInterest($tag,'INTEREST')">
                                                    <auto-complete source="loadInterest($query)"  template="interests-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="interests-template">
                                                    <div class="tag-template">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="interests-template1">
                                                    <div class="autocomplete-template">
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
                                                <i class="ficon-price-tag"></i>
                                                <tags-input ng-model="addsuerType" 
                                                    placeholder="Add user type" 
                                                    display-property="Name" 
                                                    add-from-autocomplete-only="false" 
                                                    template="reader-template"
                                                    on-tag-added="tagAddedPersona($tag,'READER')"
                                                    on-tag-removed="tagRemovedPersona($tag,'READER')">
                                                    <auto-complete source="loadLinkTags($query,'READER')" template="reader-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                </tags-input>
                                                <script type="text/ng-template" id="reader-template">
                                                    <div class="tag-template">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="reader-template1">
                                                    <div class="autocomplete-template">
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
                                                    <div class="tag-template">
                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                </script>
                                                <script type="text/ng-template" id="brand-template1">
                                                    <div class="autocomplete-template">
                                                    <div class="right-panel">

                                                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                                    </div>
                                                    </div>
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-content">
                                    <h2>Reason of joining</h2>
                                    <p ng-bind="userPersonaDetail.ReasonOfJoining"></p>
                                </div>
                                <div class="section-content">
                                    <h2>Problems/Complaints</h2>
                                    <p ng-bind="userPersonaDetail.ProblemsNComplaints"></p>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="Activities">
                                <div class="activity-listing clearfix">
                                    <ul class="list-group list-group-thumb sm">
                                        <li class="list-group-item">
                                            <div class="list-group-body">
                                                <figure class="list-figure">
                                                    <a><img src="assets/img/dummy7.jpg" class="img-circle img-responsive" alt="" title=""></a>
                                                </figure>
                                                <div class="list-group-content">
                                                    <h6 class="list-group-item-heading">                                               
                                                        <a>Rena Perez</a> commented on post by <a>Leon Yates</a> in <a>Ideas and suggestions</a>
                                                    </h6>
                                                    <ul class="list-activites">
                                                        <li>04 Jan at 10:00 AM</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="list-group-bottom">
                                                <p class="list-group-item-text">The beauty of astronomy is that anybody can do it. From the tiniest baby to the most advanced astrophysicist, there is something for anyone who wants to enjoy astronomy. In fact, it is a science that is</p>
                                                <div class="list-group-footer">
                                                    <ul class="list-group-inline">
                                                        <li>
                                                            <a class="bullet active">
                                                                <i class="ficon-heart"></i>
                                                            </a>
                                                            <a class="text">10</a>
                                                        </li>
                                                        <li>
                                                            <a class="bullet">
                                                                <i class="ficon-reply"></i>
                                                            </a>
                                                            <a class="text">250</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="blockquote blockquote-default">
                                                <ul class="list-group list-group-thumb sm">
                                                    <li class="list-group-item">
                                                        <div class="list-group-body">
                                                            <ul class="list-group-inline pull-right">
                                                                <li>
                                                                    <a class="bullet active">
                                                                        <i class="ficon-heart"></i>
                                                                    </a>
                                                                    <a class="text">110</a>
                                                                </li>
                                                                <li>
                                                                    <a class="bullet">
                                                                        <i class="ficon-comment"></i>
                                                                    </a>
                                                                    <a class="text">250</a>
                                                                </li>
                                                                <li><a class="text">Ideas</a></li>
                                                            </ul>
                                                            <figure class="list-figure">
                                                                <a><img src="assets/img/dummy7.jpg" class="img-circle img-responsive" alt="" title=""></a>
                                                            </figure>
                                                            <div class="list-group-content">
                                                                <h6 class="list-group-item-heading">                                               
                                                                <a>Leon Yates</a>
                                                            </h6>
                                                                <ul class="list-activites">
                                                                    <li>04 Jan at 10:00 AM</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        <p class="list-group-item-text">To make the profile unique, you can use good MySpace layouts. This will make a world of a difference to the profile. Imagine that someone visits your profile, find it without any good inform <a class="read-more">See more...</a></p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <i class="ficon-manage-tags" tooltip title="Manage tags"></i>
                                        </li>
                                    </ul>
                                </div>
                                <div class="activity-listing clearfix" ng-repeat="(key, value) in ['1','2','3','4']">
                                    <ul class="list-group list-group-thumb sm">
                                        <li class="list-group-item">
                                            <div class="list-group-body">
                                                <figure class="list-figure">
                                                    <a><img src="assets/img/dummy7.jpg" class="img-circle img-responsive" alt="" title=""></a>
                                                </figure>
                                                <div class="list-group-content">
                                                    <h6 class="list-group-item-heading">                                               
                                                        <a>Rena Perez</a> commented on post by <a>Leon Yates</a> in <a>Ideas and suggestions</a>
                                                    </h6>
                                                    <ul class="list-activites">
                                                        <li>04 Jan at 10:00 AM</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="list-group-bottom">
                                                <p class="list-group-item-text">The beauty of astronomy is that anybody can do it. From the tiniest baby to the most advanced astrophysicist, there is something for anyone who wants to enjoy astronomy. In fact, it is a science that is</p>
                                                <div class="list-group-footer">
                                                    <ul class="list-group-inline">
                                                        <li>
                                                            <a class="bullet active">
                                                                <i class="ficon-heart"></i>
                                                            </a>
                                                            <a class="text">10</a>
                                                        </li>
                                                        <li>
                                                            <a class="bullet">
                                                                <i class="ficon-reply"></i>
                                                            </a>
                                                            <a class="text">250</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <i class="ficon-manage-tags" tooltip title="Manage tags"></i>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="Communication">
                                <div class="section-content clearfix">
                                    <h2>Emails/News Letters</h2>
                                    <button class="btn btn-default pull-right">Send Email</button>
                                </div>
                                <div class="section-content clearfix">
                                    <div class="table-info">
                                        <table class="table table-bordered email-status">
                                            <thead>
                                                <tr>
                                                    <th>Emails Sent (4)</th>
                                                    <th>Date</th>
                                                    <th>User Action</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr ng-repeat="(key, value) in ['1','2','3','4']">
                                                    <td>New Year Greetings</td>
                                                    <td>04 Jan 2017</td>
                                                    <td><span class="cell-max-length sm">Clicked on profile of Akshay</span></td>
                                                    <td class="text-center"><i class="ficon-check mail-status" ng-class="{'read' : key >= 2}"></i></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="Notes">
                                <div class="section-content clearfix">
                                    <button class="btn btn-default pull-right">ADD NOTES </button>
                                </div>
                                <ul class="note-list clearfix">
                                    <li ng-repeat="(key, value) in ['03 Apr 2017 at 9:03 AM', '02 Apr 2017 at 11:10 AM','01 Apr 2017 at 10:00 MP','04 Apr 2017 at 10:00 MP']">
                                        <div class="list-header">
                                            <span>{{value}}</span>
                                            <div class="action-group">
                                                <i class="ficon-edit"></i>
                                                <i class="ficon-bin"></i>
                                            </div>
                                        </div>
                                        <p>Point of Sale hardware, the till at a shop check out, has become very complex over the past ten years. Modern POS hardware includes the cash till, bar-code readers, scales, belts, communications system and modem.</p>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="Usage">
                                <div class="section-content border-bottom clearfix">
                                    <h2>Desktop</h2>
                                    <ul class="usage-listing clearfix">
                                        <li><i class="icons-chrome"></i><span>86.58%</span></li>
                                        <li><i class="icons-mozilla"></i><span>86.58%</span></li>
                                        <li><i class="icons-opera"></i><span>86.58%</span></li>
                                        <li><i class="icons-ie"></i><span>86.58%</span></li>
                                        <li><i class="icons-mac"></i><span>86.58%</span></li>
                                        <li><i class="icons-safari"></i><span>86.58%</span></li>
                                        <li><i class="icons-window"></i><span>86.58%</span></li>
                                        <li><i class="icons-otherwin"></i><span>86.58%</span></li>
                                        <li><i class="icons-device"></i><span>86.58%</span></li>
                                        <li><i class="icons-android"></i><span>86.58%</span></li>
                                    </ul>
                                </div>
                                <div class="section-content border-bottom clearfix">
                                    <h2>Tablet</h2>
                                    <ul class="usage-listing clearfix">
                                        <li><i class="icons-chrome"></i><span>86.58%</span></li>
                                        <li><i class="icons-mozilla"></i><span>86.58%</span></li>
                                        <li><i class="icons-opera"></i><span>86.58%</span></li>
                                        <li><i class="icons-mac"></i><span>86.58%</span></li>
                                        <li><i class="icons-safari"></i><span>86.58%</span></li>
                                        <li><i class="icons-otherwin"></i><span>86.58%</span></li>
                                        <li><i class="icons-device"></i><span>86.58%</span></li>
                                        <li><i class="icons-android"></i><span>86.58%</span></li>
                                    </ul>
                                </div>
                                <div class="section-content border-bottom clearfix">
                                    <h2>Mobile</h2>
                                    <ul class="usage-listing clearfix">
                                        <li><i class="icons-chrome"></i><span>86.58%</span></li>
                                        <li><i class="icons-mozilla"></i><span>86.58%</span></li>
                                        <li><i class="icons-opera"></i><span>86.58%</span></li>
                                        <li><i class="icons-mac"></i><span>86.58%</span></li>
                                        <li><i class="icons-safari"></i><span>86.58%</span></li>
                                        <li><i class="icons-otherwin"></i><span>86.58%</span></li>
                                        <li><i class="icons-device"></i><span>86.58%</span></li>
                                        <li><i class="icons-android"></i><span>86.58%</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
