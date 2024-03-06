<script type="text/javascript">
    var DummyUser = 1;
</script>
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span>Users</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="container" ng-controller="UserListCtrl" id="UserListCtrl">
    <div class="main-container">
        <div class="page-heading">
            <div class="row">
                <div class="col-sm-6">
                    <h2 class="page-title">Users</h2>
                </div>
                <div class="col-sm-6">
                    <div class="btn-toolbar btn-toolbar-right">
                        <button class="btn btn-default outline pad-8" data-toggle="collapse" data-target="#manageDummyUsers">
                            <span class="icn"><i class="ficon-cog"></i></span>
                        </button>
                        <button class="btn btn-default outline" data-toggle="modal" data-target="#addPost" ng-click="showPostEditor();">Create Post</button>
                        <button class="btn btn-primary" ng-click="clear_create_user_popup(); get_random_dummy_details();" data-toggle="modal" data-target="#createNewUser">Create New User</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-secondary create-dummy-user collapse" ng-init="get_dummy_user_tags_managers();" id="manageDummyUsers"> 
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-3">
                        <label for="" class="form-label">Dummy user managers</label>   
                    </div>
                    <div class="col-sm-9">
                        <div class="form-group">
                            <tags-input 
                                ng-model="dummyUserManagers"
                                display-property="Name"
                                on-tag-added="addDummyMemberTags($tag,'USER')"
                                on-tag-removed="removeDummyUserTags($tag,'USER')"
                                add-from-autocomplete-only="true"
                                replace-spaces-with-dashes="false"
                                placeholder="Add users"
                                template="tag5">
                                <auto-complete source="loadManagerMembers($query)" load-on-focus="true" min-length="0"></auto-complete>
                            </tags-input>
                            <script type="text/ng-template" id="tag5">
                                <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                <span ng-if="data.UserID != '' && data.Name != ''" ng-bind="data.Name"></span>
                                <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <label for="" class="form-label">Default user tags for dummy users</label>   
                    </div>
                    <div class="col-sm-9">
                        <div class="form-group">
                            <tags-input 
                                ng-model="dummyUserTags"
                                display-property="Name"
                                on-tag-added="addDummyMemberTags($tag,'TAGS')"
                                on-tag-removed="removeDummyUserTags($tag,'TAGS')"
                                replace-spaces-with-dashes="false"
                                placeholder="Add tags"
                                template="tag5">
                                <auto-complete source="loadDummyMemberTags($query, 'USER', 'USER')" load-on-focus="true" min-length="0"></auto-complete>
                            </tags-input>
                            <script type="text/ng-template" id="tag6">
                                <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                                <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                                <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                </div>
                            </script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button ng-disabled="dummy_user_data.user_managers.length <= '0' || dummy_user_data.user_tags.length <= '0'" ng-click="save_manager_tags();" class="btn btn-primary pull-right outline">Submit</button>
                    </div>
                </div>  
            </div> 
        </div>
        <div class="panel panel-secondary" ng-init="sort_dummy_user('U.FirstName');">
            <div class="panel-body">
                <div class="table-listing">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th ng-click="sort_dummy_user('U.FirstName')" class="sorting" ng-class="(sort_dummy_user_by=='U.FirstName') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Name<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th ng-click="sort_dummy_user('TotalFriends')" class="sorting" ng-class="(sort_dummy_user_by=='TotalFriends') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Friends<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th ng-click="sort_dummy_user('TotalFollowers')" class="sorting" ng-class="(sort_dummy_user_by=='TotalFollowers') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Followers<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th ng-click="sort_dummy_user('U.CreatedDate')" class="sorting" ng-class="(sort_dummy_user_by=='U.CreatedDate') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Created On<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th ng-click="sort_dummy_user('TotalPosts')" class="sorting" ng-class="(sort_dummy_user_by=='TotalPosts') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Post<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th ng-click="sort_dummy_user('TotalComments')" class="sorting" ng-class="(sort_dummy_user_by=='TotalComments') ? (order_dummy_user_by=='DESC') ? 'sorting-up' : 'sorting-down' : '' ;">Comments<a class="sort"><span class="icn"><i class="ficon-sort-arrow"></i></span></a></th>
                                
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="user in dummy_users">
                                <td>
                                    <div class="list-group list-group-thumb xs">
                                        <div class="list-group-item">
                                            <div class="list-group-body">
                                                <figure class="list-figure">
                                                    <a><img err-Name="{{user.FirstName+' '+user.LastName}}" ng-src="{{'<?php echo IMAGE_SERVER_PATH ?>'+'upload/profile/220x220/'+user.ProfilePicture}}" class="img-circle img-responsive"  ></a>
                                                </figure>
                                                <div class="list-group-content">
                                                    <div class="list-group-item-heading">
                                                        <span ng-bind="user.FirstName+' '+user.LastName"></span>
                                                        <span class="notification-count" ng-if="user.TotalNotificationRecords>0" ng-bind="user.TotalNotificationRecords"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span ng-bind="(user.TotalFriends>0) ? user.TotalFriends : '-' ;"></span></td>
                                <td><span ng-bind="(user.TotalFollowers>0) ? user.TotalFollowers : '-' ;"></span></td>
                                <td><span ng-bind="user.CreatedDateFormat"></span></td>
                                <td><span ng-bind="(user.TotalPosts>0) ? user.TotalPosts : '-' ;"></span></td>
                                <td><span ng-bind="(user.TotalComments>0) ? user.TotalComments : '-' ;"></td>
                                <td>
                                    <div class="action">
                                        <a class="ficon-add-content" data-toggle="modal" data-target="#addPost" ng-click="set_post_as_user(user.UserID); showPostEditor();" uib-tooltip='Post'></a>
                                        <a data-toggle="modal" data-target="#createNewUser" ng-click="set_current_user_data(user.UserID); update_create_user_popup(user.UserID)" class="ficon-edit" uib-tooltip='Edit'></a>
                                        <a class="ficon-bin" ng-click="delete_dummy_user(user.UserID);" uib-tooltip='Delete'></a>
                                        <a ng-click="setDummyUser(user)"><i class="ficon-login-as" uib-tooltip='Login   '></i></a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer text-right">
                    <a href="<?php echo site_url('admin/users/activity') ?>" class="view-link">View all Posts</a>
                </div>
            </div>
            <div class="simple-pagination" data-pagination="" total-items="total_user_records" data-num-per-page="numPerPage" data-num-pages="numPages()" data-current-page="currentPage" data-max-size="maxSize" data-boundary-links="true"></div>
        </div>
    </div>
    <?php $this->load->view('admin/users/dummy_user_popup') ?>
</div>
