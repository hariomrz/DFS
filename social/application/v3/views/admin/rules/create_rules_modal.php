<div class="modal fade" tabindex="-1" role="dialog" id="createRule" ng-init="get_age_group()">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ficon-cross"></i>
                </button> -->
                <h4 class="modal-title">Create Rule</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Name of Rule</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <input ng-model="rule.Name" type="text" class="form-control" placeholder="Enter name e.g. New Year">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Location</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group location-field">
                            <input id="address" class="form-control" placeholder="Enter specific cities" type="text" ng-init="initCity()" />
                            <a class="link-specific" ng-click="rule.Location = [];">Anywhere</a>
                            <div class="location-added" id="readonlyinput">
                                <tags-input readonly="readonly" ng-model="rule.Location" display-property="City" add-from-autocomplete-only="true" replace-spaces-with-dashes="false">
                                </tags-input>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Gender</label>
                    </div>
                    <div class="col-sm-8">
                        <!-- chosen data-disable-search="true" -->
                        <div class="form-group">
                            <select id="genderchosen" chosen data-disable-search="true" ng-model="rule.Gender" ng-options="g.value as g.name for g in gender" placeholder="Any" class="form-control">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Age Group</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <select id="agechosen" ng-model="rule.AgeGroupID" placeholder="Any"  chosen data-disable-search="true" class=" form-control" ng-options="ag.AgeGroupID as ag.Name for ag in age_groups">
                                <option>Any</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Interests</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <tags-input add-from-autocomplete-only="true" replace-spaces-with-dashes="false" ng-model="rule.InterestIDs" key-property="CategoryID" display-property="Name" placeholder="Add more interests">
                                <auto-complete source="loadInterest($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4"></auto-complete>
                            </tags-input>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label vr">Specific User</label>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <tags-input ng-model="rule.SpecificUser" key-property="UserID" display-property="Name" placeholder="Add more users" add-from-autocomplete-only="true">
                                <auto-complete source="loadUsers($query)"></auto-complete>
                            </tags-input>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="button-group pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" ng-click="add_rule()">Continue</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Add Content Starts -->
<div class="modal fade" tabindex="-1" role="dialog" id="addContent">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ficon-cross"></i>
                </button>
                <h4 class="modal-title">Add content for - <span ng-bind="rule.Name"></span></h4>
            </div>
            <div class="rule-filter" ng-show="isFilterExistsForTab">
                <div class="rule-filter-write-post">
                    <div class="row">
                        <div class="col-sm-8 border-right">
                            <div class="rule-filter-content">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="form-label">Location</label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group location-field">
                                            <input id="address2" ng-init="initCity2()" type="text" class="form-control" placeholder="Enter specific cities">
                                            <a class="link-specific" ng-click="content_rule.Location=[];">Anywhere</a>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="location-added" id="readonlyinput">
                                            <tags-input readonly="readonly" ng-model="content_rule.Location" display-property="City" add-from-autocomplete-only="true" placeholder="Location" replace-spaces-with-dashes="false">
                                            </tags-input>
                                        </div>
                                    </div>
                                </div>
                            </div>        
                        </div>
                        <div class="col-sm-4 select-Dropdown">
                             <ul class="clearfix">
                                <li class="clearfix">
                                    <label class="form-label">Age Group</label> 
                                    <div class="select-box">
                                         <select ng-model="content_rule.AgeGroupID" placeholder="Any" chosen data-disable-search="true" title="Any" class="form-control" ng-options="ag.AgeGroupID as ag.Name for ag in age_groups">
                                            <option>Any</option>
                                        </select>
                                    </div>
                                </li>
                                <li class="clearfix">
                                    <label class="form-label">Gender</label>
                                    <div class="select-box">
                                        <select ng-model="content_rule.Gender" ng-options="g.value as g.name for g in gender" title="Any" placeholder="Any" chosen data-disable-search="true" class="form-control">
                                        </select>
                                    </div>
                                </li>
                              </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>    
            <div class="modal-body crate-new-content">
                    <div class= write-post-view clearfix" xng-show="writepostView">
                        <div class="clearfix" id="accordion"> 
                            <div class="panel panel-accordion">
                                <a class="accordion-heading completed heading-1 collapsed" ng-click="addClass('completed',1); set_content_rule(current_rule_id);" data-parent="#accordion" data-toggle="collapse" href="#Welcome">
                                    <span class="acc-index">1</span>
                                    <span>Welcome</span>
                                    <i class="ficon-plus"></i>
                                </a>
                                <div id="Welcome" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <div id="summernote"></div>
                                        <div class="button-group clearfix">
                                            <button ng-click="set_welcome_message()" class="btn btn-primary pull-right outline">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-accordion">
                                <a class="accordion-heading collapsed heading-2" data-parent="#accordion" ng-click="addClass('completed',2); set_content_rule(current_rule_id,'post');" data-toggle="collapse" href="#Posts">
                                    <span class="acc-index">2</span>
                                    <span>Posts</span>
                                    <i class="ficon-plus"></i>
                                </a>
                                <div id="Posts" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label class="radio">
                                                        <input type="radio" name="rulepost" ng-value="1" ng-model="publicpostrule" name="post">
                                                        <span class="label"> ALL PUBLIC POSTS</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label class="radio">
                                                        <input type="radio" name="rulepost" ng-model="publicpostrule" ng-value="2" name="post">
                                                        <span class="label"> CUSTOM</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label class="radio">
                                                        <input type="radio" name="rulepost" ng-model="publicpostrule" ng-value="3" name="post">
                                                        <span class="label"> POPULAR</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-t-15" ng-cloak ng-show="publicpostrule=='2'">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="form-label vr">Tags</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <tags-input add-from-autocomplete-only="true" ng-model="PostTags" key-property="TagID" display-property="Name" placeholder="Add more tags">
                                                            <auto-complete source="loadPostTags($query)"></auto-complete>
                                                        </tags-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="form-label vr">Interests</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <tags-input add-from-autocomplete-only="true" ng-model="PostInterests" key-property="CategoryID" display-property="Name" placeholder="Add more interests">
                                                            <auto-complete source="loadPostInterest($query)"></auto-complete>
                                                        </tags-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="form-label vr">Specific User</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <tags-input add-from-autocomplete-only="true" ng-model="PostSpecificUser" key-property="UserID" display-property="Name" placeholder="Add members name">
                                                            <auto-complete source="loadPostUsers($query)"></auto-complete>
                                                        </tags-input>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="form-label vr">Customize Selection</label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" ng-model="activity_link" ng-init="activity_link=''"/>
                                                            <div class="input-group-addon">
                                                                <button ng-click="fetch_single_activity(activity_link)" class="btn btn-primary btn-sm"><i class="ficon-plus"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="feed-view" ng-repeat="data in activity_data" ng-cloak>
                                                            <ul class="list-group list-group-thumb bordered sm">
                                                                <li class="list-group-item">
                                                                    <a ng-click="remove_activity(data.ActivityID)" class="remove-icon ficon-cross"></a>
                                                                    <div class="list-group-body">
                                                                        <figure class="list-figure">
                                                                            <a><img err-Name="{{data.FirstName+' '+data.LastName}}" ng-src="{{data.ImageServerPath+'upload/profile/220x220/'+data.ProfilePicture}}" class="img-circle img-responsive"  /></a>
                                                                        </figure>
                                                                        <div class="list-group-content">
                                                                            <h5 class="list-group-item-heading">                                       
                                                                                <a ng-bind="data.FirstName+' '+data.LastName"></a>
                                                                            </h5>
                                                                            <ul class="list-activites">
                                                                                <li ng-bind="date_format(data.CreatedDate)"></li>
                                                                                <li>
                                                                                    <span ng-if="data.Privacy == '1'" class="icn">
                                                                                        <i class="ficon-globe"></i>
                                                                                    </span>
                                                                                    <!-- <i ng-if="data.Privacy == '1'" class="icon-n-everyone">&nbsp;</i>
                                                                                    <i ng-if="data.Privacy == '2'" class="icon-n-followers">&nbsp;</i>
                                                                                    <i ng-if="data.Privacy == '3'" class="icon-n-friends">&nbsp;</i>
                                                                                    <i ng-if="data.Privacy == '4'" class="icon-n-onlyme">&nbsp;</i> -->
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <p class="list-group-item-text" ng-bind-html="textToLink(data.PostContent,false,200)"></p>
                                                                    <div class="list-group-footer">
                                                                        <ul class="list-group-inline pull-left">
                                                                            <li ng-if="data.NoOfLikes>0">
                                                                                <a class="bullet">
                                                                                    <i class="ficon-heart"></i> 
                                                                                </a>
                                                                                <a class="text" ng-bind="data.NoOfLikes"></a>
                                                                            </li>
                                                                            <li ng-if="data.NoOfComments>0">
                                                                                <a class="bullet">
                                                                                    <i class="ficon-comment"></i> 
                                                                                </a>
                                                                                <a class="text" ng-bind="data.NoOfComments"></a>
                                                                            </li>
                                                                        </ul>
                                                                        <span class="pull-right" ng-bind="data.PostTypeName"></span>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                            <div class="button-group clearfix">
                                                <button ng-click="set_post_rule()" class="btn btn-primary pull-right outline">save</button>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-accordion">
                                <a class="accordion-heading collapsed heading-3" data-parent="#accordion" ng-click="addClass('completed',3); set_content_rule(current_rule_id,'profile');" data-toggle="collapse" href="#Profiles">
                                    <span class="acc-index">3</span>
                                    <span>Profiles</span>
                                    <i class="ficon-plus"></i>
                                </a>
                                <div id="Profiles" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="radio">
                                                        <input type="radio" ng-model="ruleprofile" ng-value="1" name="rulepost2" id="allpopularprofiles">
                                                        <span class="label"> ALL POPULAR PROFILES</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="radio">
                                                        <input type="radio" ng-model="ruleprofile" ng-value="2" name="rulepost2" id="customprofiles">
                                                        <span class="label"> CUSTOM</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" ng-show="ruleprofile=='2'" ng-cloak>
                                            <div class="col-sm-4">
                                                <label class="form-label vr">Tags</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <tags-input add-from-autocomplete-only="true" ng-model="ProfileTags" key-property="TagID" display-property="Name" placeholder="Add more tags">
                                                        <auto-complete source="loadProfileTags($query)"></auto-complete>
                                                    </tags-input>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" ng-show="ruleprofile=='2'" ng-cloak>
                                            <div class="col-sm-4">
                                                <label class="form-label vr">Interests</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <tags-input add-from-autocomplete-only="true" ng-model="ProfileInterests" key-property="CategoryID" display-property="Name" placeholder="Add more interests">
                                                        <auto-complete source="loadProfileInterest($query)"></auto-complete>
                                                    </tags-input>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" ng-show="ruleprofile=='2'" ng-cloak>
                                            <div class="col-sm-4">
                                                <label class="form-label vr">Specific User</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <tags-input add-from-autocomplete-only="true" ng-model="ProfileSpecificUser" key-property="UserID" display-property="Name" placeholder="Add members name">
                                                        <auto-complete source="loadProfileUsers($query)"></auto-complete>
                                                    </tags-input>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="button-group clearfix">
                                            <button ng-click="set_profile_rule()" class="btn btn-primary pull-right outline">save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-accordion">
                                <a class="accordion-heading collapsed heading-4" data-parent="#accordion" ng-click="addClass('completed',4); set_content_rule(current_rule_id,'tags');" data-toggle="collapse" href="#Tags">
                                    <span class="acc-index">4</span>
                                    <span>Tags</span>
                                    <i class="ficon-plus"></i>
                                </a>
                                <div id="Tags" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label class="checkbox form-label">
                                                        <input type="checkbox" ng-value="1" ng-model="trendingTags" id="IsTrending" />
                                                        <span class="label">Trending</span>
                                                    </label>
                                                </div>
                                            </div> 
                                        </div> 
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label class="form-label vr">Specific Tags</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <tags-input add-from-autocomplete-only="true" ng-model="SpecificTags" key-property="TagID" display-property="Name" placeholder="Add tags">
                                                        <auto-complete source="loadSpecificTags($query)"></auto-complete>
                                                    </tags-input>
                                                </div>
                                                <div class="button-group clearfix">
                                                    <button ng-click="set_tags_rule()" class="btn btn-primary pull-right outline">save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                     </div>
                 </div>
            </div>
            <div class="modal-footer">
                <div class="button-group pull-right">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">I'm Done</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Add Content Ends -->

<!-- Add existing rules start -->
<div class="modal fade" tabindex="-1" role="dialog" id="addExistingRules" ng-init="get_rules();">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                <i class="ficon-cross"></i>
            </span>
                </button>
                <h4 class="modal-title">Add To Rule</h4>
            </div>
            <div class="modal-body">
                 <div class="row">
                     <div class="col-sm-12">
                        <label class="control-label">Rules</label>
                        <select ng-model="existing_rule_select" ng-options="rule.ActivityRuleID as rule.Name for rule in rules_list" chosen class="form-control">
                            <option></option>
                        </select>
                     </div>  
                 </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right">    
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#createRule">Add New Rule</button>
                    <button type="button" ng-click="update_existing_rule(existing_rule_select)" class="btn btn-primary">Done</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- Add existing rules ends -->