
<div  ng-controller="NewsletterUserListCtrl" id="NewsletterUserListCtrl" ng-init="initList()">




    <?php $this->load->view('admin/newsletter/users_filter'); ?>


    <div class="container">
        <div class="main-container"> 

            <div class="sm-info" ng-if="isFilterReady() && filterApplied" >
                You are viewing all the

                <span ng-if="showingFilterData.StatusID != 0">
                    <b ng-if="showingFilterData.StatusID == 2"> Newsletter Subscribers </b>
                    <b ng-if="showingFilterData.StatusID == 3"><?php echo lang("User_Index_DeletedUsers"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 4"><?php echo lang("User_Index_BlockedUsers"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 1"><?php echo lang("User_Index_WaitingForApproval"); ?></b>
                    <b ng-if="showingFilterData.StatusID == 23">Suspended Users</b>

                    <span ng-if="showingFilterData.Gender != 0">,</span>

                </span>

                <span ng-if="showingFilterData.Gender != 0">
                    <b ng-if="showingFilterData.Gender == 1">Male</b>
                    <b ng-if="showingFilterData.Gender == 2">Female</b>
                    <b ng-if="showingFilterData.Gender == 3">Other</b>
                     
                </span>
                
                <span ng-if="showingFilterData.IncompleteProfileDays">
                    , With Incomplete Registration In last
                    <b ng-bind="showingFilterData.IncompleteProfileDays"></b>
                    Days                     
                </span>
                
                <span ng-if="showingFilterData.InactiveProfileDays">
                    , Inactive Users From Last
                    <b ng-bind="showingFilterData.InactiveProfileDays"></b>
                    Days                     
                </span>

                <span ng-if="showingFilterData.Locations.length != 0">from <b> 
                        <span ng-repeat="Location in showingFilterData.Locations">
                            {{Location.City}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span>
                    </b> 
                </span>

                <span ng-if="showingFilterData.AgeGroupID != 0">
                    , aged between <b>{{ageGroupList[showingFilterData.AgeGroupID - 1].Name}}</b> years
                </span>

                <span ng-if="showingFilterData.AgeStart != 0 && showingFilterData.AgeEnd != 0">
                    , aged between <b>{{showingFilterData.AgeStart}} - {{showingFilterData.AgeEnd}}</b> years
                </span>

                <span ng-if="showingFilterData.AgeStart != 0 && (showingFilterData.AgeEnd == 0 || showingFilterData.AgeEnd == '')">
                    , aged from <b>{{showingFilterData.AgeStart}} </b> years
                </span>

                <span ng-if="showingFilterData.AgeEnd != 0 && (showingFilterData.AgeStart == 0 || showingFilterData.AgeStart == '')">
                    , aged upto <b>{{showingFilterData.AgeEnd}}</b> years
                </span>

                <span ng-if="showingFilterData.TagUserType.length != 0">
                    , of type <b><span ng-repeat="tag in showingFilterData.TagUserType" >
                            {{tag.Name}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span></b> 
                </span>

                <span ng-if="showingFilterData.TagTagType.length != 0">
                    with <b>
                        <span ng-repeat="tag in showingFilterData.TagTagType" >
                            {{tag.Name}}
                            <span ng-if="!$last">,&nbsp;</span>
                        </span>
                    </b> tags
                </span>



                <a ng-click="applyFilter(1)">Reset</a> 
                | <a data-toggle="collapse"  data-target="#userFilters">Edit</a>
            </div>


            <div class="page-heading">
                <div class="row">
                    <div class="col-sm-4 " >
                        <small class="info-text-sm crm_on_check_div" ng-show="!allUserSelected" style="display:none;">
                            <span class="user_count_crm_msg"></span>
                            <span class="show_all_selection_message">
                                <a ng-click="selectUnselectAllUsers(1)">Select all {{totalRecord}} subscribers</a> in lists.
                            </span>                            
                        </small>

                        <small class="info-text-sm crm_on_check_div" ng-if="allUserSelected">
                            All {{getSelectedUsersCount()}} subscribers are selected. 
                            <a ng-click="selectUnselectAllUsers(0)">Unselect All</a>
                        </small>

                    </div>

                    <div class="col-sm-8">
                        <div class="btn-toolbar btn-toolbar-right" >
                            <div class="total-pages" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage" ng-show="totalRecord > numPerPage"></div>
                            <nav class="page navigation" ng-show="totalRecord > numPerPage">
                                <ul 
                                    uib-pagination total-items="totalRecord" items-per-page="numPerPage" 
                                    ng-model="currentPage" max-size="maxSize" 
                                    num-pages="numPages" class="pagination-sm" boundary-links="false" 
                                    ng-change="getThisPage()"
                                    template-url="pagination_template.html"
                                    >

                                </ul>
                            </nav>

                            <button class="btn btn-default" ng-click="openNewsletterUploadUsersModal()">
                                <span class="icn"><i class="ficon-upload"></i></span>
                                <span class="text">Upload List</span>
                            </button>
                            <button class="btn btn-default" ng-click="downloadList()" ng-show="userList.length != 0"><i class="ficon-download"></i> Download List</button> 
                        </div>
                    </div>
                </div>
            </div>


            <div class="panel panel-secondary">
                <div class="panel-body no-padding">



                    <div class="table-listing">
                        <table class="table table-hover">
                            <thead ng-show="totalRecord">
                                <tr>
                                    <th width="50"> 
                                        <label class="checkbox checkbox-inline">
                                            <input type="checkbox" value="0" class="userCheckBox" id="headerCheckBoxCrm" >
                                            <span class="label"></span>
                                        </label>
                                    </th>  
                                    <th ng-click="orderByField('FirstName')"  ng-class="getOrderByClass('FirstName')" >
                                        Name 
                                        <a class="sort" ng-if="getOrderByClass('FirstName')">
                                            <span class="icn">
                                                <i class="ficon-sort-arrow"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th>Type</th>
                                    <th>Tags</th>
                                    <th>Location</th> 
                                    <th class="text-center" width="60">Nature</th> 
                                    <th class="text-center" width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr ng-if="totalRecord == 0" >
                                    <td colspan="6" style="text-align: center;">
                                        No Result Found.
                                    </td>
                                </tr>

                                <tr ng-repeat="(key, user) in userList" repeat-done="popOverInit();">
                                    <td>
                                        <label class="checkbox checkbox-inline checkbox-block">
                                            <input type="checkbox" value="{{user.NewsLetterSubscriberID}}" class="userCheckBox">
                                            <span class="label"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="list-group list-group-thumb xs"> 
                                            <div class="list-group-item">
                                                <div class="list-group-body"> 
                                                    <figure class="list-figure" ng-click="getUserPersonaDetail(user.UserID, user.UserGUID, user.Name);">
                                                        <a><img ng-src="{{user.ProfilePictureUrl}}" class="img-circle img-responsive" ></a>
                                                    </figure>

                                                    <div class="list-group-content">
                                                        <div class="list-group-item-heading ellipsis">                                               
                                                            <h5 class="list-group-item-heading">
                                                                <a class="text-black " uib-tooltip="{{user.Name}}" ng-click="openUserDetails(user);">{{user.Name}}</a> 
                                                                <span class="text-small-off bold">{{user.AgeGenderTxt}}</span>
                                                            </h5>
                                                            <span class="text-sm-muted" ng-bind="user.Email"></span>
                                                        </div>
                                                    </div>

                                                </div>                           
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <ul class="tags-list clearfix">
                                            <li ng-repeat="tagName in user.UserTypeTagsStr.tagStr track by $index" class="tag-primary">
                                                <span>{{tagName}}</span>
                                            </li>

                                            <li ng-if="user.UserTypeTagsStr.tagMoreStr.length > 0" class="tag-primary">
                                                <span 
                                                    data-container="body" 
                                                    data-toggle="popover" 
                                                    data-html="true"
                                                    data-content='{{user.UserTypeTagsStr.tagMoreStrTitle}}'>
                                                    +{{user.UserTypeTagsStr.tagMoreStr.length}}
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="tags-list clearfix">
                                            <li ng-repeat="tagName in user.TagsStr.tagStr track by $index" >
                                                <span ng-bind="tagName"></span>
                                            </li>

                                            <li ng-if="user.TagsStr.tagMoreStr.length > 0">
                                                <span 
                                                    data-container="body" 
                                                    data-toggle="popover" 
                                                    data-html="true"
                                                    data-content="{{user.TagsStr.tagMoreStrTitle}}">
                                                    +{{user.TagsStr.tagMoreStr.length}}
                                                </span>
                                            </li>

                                        </ul>
                                    </td>
                                    <td>{{user.LocationStr}}</td> 
                                    
                                    <td align="center"> 
                                        <span class="icon" > 
                                            <i class="ficon-registered text-muted" ng-if="+user.UserID" data-toggle="tooltip" uib-tooltip="Registered" ></i>
                                            <i class="ficon-subscribed text-muted" ng-if="!(+user.UserID)" data-toggle="tooltip" uib-tooltip="Subscribed" ></i>
                                        </span>
                                    </td>


                                    <td>

                                        <div class="action">
                                            <a class="ficon-bin" ng-click="SetUser(user); deleteUserConfirmBox(user);"  ></a>       
                                            <a class="ficon-edit" ng-click="openUserDetails(user)" ></a>
                                        </div>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div> 
                </div>
            </div>


            <div class="paging-bottom"  ng-show="totalRecord > numPerPage">
                <div class="total-pages" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></div>
                <nav class="page navigation"
                    ng-show="totalRecord > numPerPage"
                >
                    <ul 
                        uib-pagination total-items="totalRecord" items-per-page="numPerPage" 
                        ng-model="currentPage" max-size="maxSize" 
                        num-pages="numPages" class="pagination-sm" boundary-links="false" 
                        ng-change="getThisPage()"
                        template-url="pagination_template.html"
                        
                        >
                    </ul>
                </nav>
            </div>



        </div>
    </div>



    <?php $this->load->view('admin/newsletter/users_options_models'); ?>

    <div ng-controller="UserListCtrl" id="UserListCtrl">
        <?php $this->load->view('admin/users/persona/user_persona'); ?>
    </div>


</div>