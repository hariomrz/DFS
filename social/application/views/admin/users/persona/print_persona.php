<html ng-app="App">
    <script>        
    var base_url = '<?php echo base_url();?>';
    var js_date = "";
    var week_start_on = "";
    var admin_role_id = <?php echo ADMIN_ROLE_ID; ?>;
    var image_path='<?php echo IMAGE_SERVER_PATH.'upload/'; ?>'
    var image_server_path='<?php echo IMAGE_SERVER_PATH; ?>'
    var NodeAddr = '<?php echo NODE_ADDR;?>';
    var AssetBaseUrl = '<?php echo ASSET_BASE_URL ?>';
    var partialsUrl = '<?php echo ASSET_BASE_URL ?>admin/js/app/partials/';
    var IsAdminView = 1;
    var IsFileTab = 0;
    var LoginSessionKey = '';

    var user_url = base_url;
    var profile_picture = base_url;
    var login_user_name = '';
    var time_zone_offset=0;

    var TomorrowDate = '';
    var NextWeekDate = '';
    var DisplayTomorrowDate = '';
    var DisplayNextWeekDate = '';
    var accept_language = '<?php echo $this->config->item("language"); ?>';
    var TimeZone = "UTC";
    var pagination = 10;
    var pagination_links = 3;

    var WeAreWorkingTime = '<?php echo WE_ARE_WORKING_TIME ?>';
    var StillWeAreWorkingTime = '<?php echo STILL_WE_ARE_WORKING_TIME ?>';
    var SeemsSomethingWrongRefreshTime = '<?php echo SEEMS_SOMETHING_WRONG_REFRESH_TIME ?>';

    </script>
    <?php $this->load->view('admin/layout/all_css') ?>
    <?php $this->load->view('admin/layout/all_js') ?>
    <script type="text/javascript">
        window.onload = function()
        {
            setTimeout(function(){
                window.print();
            },2000);
        }
    </script>

    <div ng-controller="UserListCtrl" id="UserListCtrl" class="container">
    <div id="WallPostCtrl" ng-controller="WallPostCtrl">
            <div ng-controller="NotesCtrl" id="NotesCtrl">
            <div  id="user_persona" ng-init="showUserPersona('<?php echo $UserID ?>','<?php echo $UserGUID ?>','<?php echo $Name ?>')">
         <div class="user-personas">
                    <div class="personas-left">
                        <div class="personas-content">
                            <div class="personas">
                                <div class="users-thum">
                                    <i class="ficon-edit" ></i>
                                    <ul class="dp-slider">
                                        <li> 
                                            <img src="{{userPersonaDetail.ProfilePicture}}" >
                                        </li>
                                    </ul>
                                </div>
                                <h2 class="user-name"><span ng-bind="userPersonaDetail.FirstName+' '+userPersonaDetail.LastName"></span> <i class="ficon-edit" ></i></h2>
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
                                        <span ng-bind="userPersonaDetail.AdminRelationWithName"></span>
                                        <span ng-if="userPersonaDetail.RelationWithAge!=''" ng-bind="userPersonaDetail.RelationWithAge+' Y'"></span>

                                        <span ng-if="userPersonaDetail.RelationWithAge!='' && userPersonaDetail.family_details.length>0">,</span>

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
                        <div class="personas-content">
                            <div class="pers-header" >Network <i class="ficon-edit"></i></div>
                            <ul class="network-detail">
                                <li>
                                    <a target="_blank" ng-href="{{'<?php echo site_url() ?>'+userPersonaDetail.ProfileURL}}"><i class="ficon-vsocial"></i></a>
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
                        </div>
                        <div class="personas-footer">
                            <div class="button-group">
                                <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==1 || userPersonaDetail.StatusID==2 || userPersonaDetail.StatusID==6 || userPersonaDetail.StatusID==7" >Block</button>
                                <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==4" >Unblock </button>
                                <div class="dropdown" ng-if="userPersonaDetail.StatusID==1 || userPersonaDetail.StatusID==2 || userPersonaDetail.StatusID==6 || userPersonaDetail.StatusID==7">
                                    <button class="btn btn-default">Suspend</button>
                                </div>
                                <button class="btn btn-default" ng-if="userPersonaDetail.StatusID==23"  >Unsuspend</button>                            
                                
                            </div>
                            <div class="footer-info">
                                <span ng-if="userPersonaDetail.StatusID==23 && userPersonaDetail.AccountSuspendTill"><i class="ficon-info"></i> User has been suspended till <span ng-bind="createDateObject(userPersonaDetail.AccountSuspendTill) | date : 'dd MMM'"></span></span>
                                <span ng-if="userPersonaDetail.StatusID==23 && !userPersonaDetail.AccountSuspendTill"><i class="ficon-info"></i> User has been suspended.</span>
                                <span>Member since : {{userPersonaDetail.membersince}}</span>
                                <span>Last Login : {{userPersonaDetail.lastlogindate}}</span>
                            </div>
                        </div>
                        
                    </div>
                    <div class="personas-right">
                        <div class="overlay-div" ng-if="(editPersonalDetail || editNetworkDetail || editDetails || updateProfilePic)"></div>
                        <div class="navbar-tabs">
                            <ul class="tabs-nav clearfix">
                                <li class="active"><a href="#General"  data-toggle="tab">General</a></li> 
                            </ul>
                        </div>
                        <div class="tab-block">
                            <div class="tab-content">
                                <div class="tab-pane fade active in activities-tabs" id="General">
                                    <div class="section-content border-bottom clearfix">
                                    <button onclick="window.print()" class="btn btn-default icons print-button btn-sm pull-right">
                                        <i class="ficon-printer"></i> PRINT
                                    </button>
                                        
                                    </div>

                                    <p class="quotes-view" ng-if="userPersonaDetail.UserWallStatus!=''">“<span ng-bind="userPersonaDetail.UserWallStatus"></span>”</p>
                                    <div class="section-content border-bottom"
                                        <h2>Contributions</h2>
                                        <ul class="contributions-list row">
                                            <li class="col-sm-4">
                                                <i class="ficon-add-post ficon-blue"></i>
                                                <label ng-bind="userPersonaDetail.ActivityCount"></label>
                                                <span>Posts</span>
                                            </li>
                                            <li class="col-sm-4">
                                                <i class="ficon-comment ficon-blue"></i>
                                                <label ng-if="userPersonaDetail.CommentCount" ng-bind="userPersonaDetail.CommentCount"></label>
                                                <label ng-if="!userPersonaDetail.CommentCount">0</label>
                                                <span>Comments</span>
                                            </li>
                                            <li class="col-sm-4">
                                                <i class="ficon-heart ficon-red"></i>
                                                <label ng-if="userPersonaDetail.LikeRecievedCount" ng-bind="userPersonaDetail.LikeRecievedCount"></label>
                                                <label ng-if="!userPersonaDetail.LikeRecievedCount">0</label>
                                                <span>Likes Received</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="section-content">
                                        <h2>Interests</h2>
                                        <div id="chart_div" class="chart-view"></div>
                                    </div>
                                    <div class="section-content">
                                        <h2>Members Tags</h2>
                                        <div class="per-member-tags clearfix">
                                            <div class="form-group no-bordered">
                                                <div class="input-icon">
                                                    <i class="ficon-profession"></i>
                                                    <tags-input ng-model="professionTag" 
                                                    placeholder="Add profession" 
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
                                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope" ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                    <script type="text/ng-template" id="tag-template1">
                                                        <div class="autocomplete-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                        <div class="right-panel">

                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope" ng-bind="::$$removeTagSymbol">×</a>
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
                                                    on-tag-removed="tagRemovedInterest($tag,'INTEREST')">
                                                        <auto-complete source="loadInterest($query)"  template="interests-template1" load-on-focus="true" min-length="0"></auto-complete>
                                                    </tags-input>
                                                    <script type="text/ng-template" id="interests-template">
                                                        <div class="tag-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                    <script type="text/ng-template" id="interests-template1">
                                                        <div class="autocomplete-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                        <div class="right-panel">

                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
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
                                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                    <script type="text/ng-template" id="reader-template1">
                                                        <div class="autocomplete-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <div class="right-panel">

                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
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
                                                        <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                    </script>
                                                    <script type="text/ng-template" id="brand-template1">
                                                        <div class="autocomplete-template" ng-class="data.ModuleEntityUserType=='2'?'added-by-admin':''">
                                                        <div class="right-panel">

                                                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                                        <a class="remove-button ng-binding ng-scope"  ng-bind="::$$removeTagSymbol">×</a>
                                                        </div>
                                                        </div>
                                                    </script>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="section-content">
                                        <h2>Reason of joining <i ng-cloak ng-if="!EditReasonOfJoining" class="ficon-edit"></i></h2>
                                        <p ng-cloak ng-if="!EditReasonOfJoining" ng-bind="userPersonaDetail.ReasonOfJoining"></p>
                                        <textarea ng-cloak ng-show="EditReasonOfJoining" ng-model="userPersonaDetail.ReasonOfJoining" id="EditReasonOfJoining" class="form-control"></textarea>
                                    </div>
                                    <div ng-cloak ng-show="EditReasonOfJoining" class="row">
                                        <div class="col-xs-12">
                                            <button  type="button" class="btn btn-default pull-right border-primary">Save</button>
                                        </div>
                                    </div>
                                    <div class="section-content">
                                        <h2>Problems/Complaints <i ng-cloak ng-if="!EditProblemsNComplaints" class="ficon-edit"></i></h2>
                                        <p ng-cloak ng-if="!EditProblemsNComplaints" ng-bind="userPersonaDetail.ProblemsNComplaints"></p>
                                        <textarea ng-cloak ng-show="EditProblemsNComplaints" ng-model="userPersonaDetail.ProblemsNComplaints" id="EditProblemsNComplaints" class="form-control"></textarea>
                                    </div>
                                    <div ng-cloak ng-show="EditProblemsNComplaints" class="row">
                                        <div class="col-xs-12">
                                            <button  type="button" class="btn btn-default pull-right border-primary">Save</button>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
        <!-- /.modal-dialog -->
    </div>
    </div>
    </div>
    </div>
     

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
    .modal-backdrop {display: none !important;}
    .modal-open,.tab-block .tab-content .tab-pane{overflow: scroll !important;}
    .tab-block .tab-content .tab-pane{max-height: none !important;min-height: none !important;}
    </style>
</html>