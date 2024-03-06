<div id="InviteFriendCtrl" ng-controller="InviteFriendCtrl" ng-init="get_top_active_users_of_selected_cat()">
    <div class="container wrapper container-primary">
        <div class="category-text">
            <h1 class="category-heading">Follow top contributors!</h1>
            <p class="lead">Here are some top contributors from the categories you followed. Follow them and get inspired!</p>
        </div>
        <div class="row categories">
            <div class="col-sm-4 col-lg-2" ng-cloak ng-repeat="user in peopleWithsimilarInterest" ng-click="toggle_follow_entity(3,user.UserGUID);">
                <div class="categories-box category-thumb-square" ng-class="(user.isFollow=='1')?'active':''">
                    <a class="category-select"><i class="ficon-check"></i></a>
                    <div ng-cloak class="category-thumb" ng-if="user.ProfilePicture!=''" style="background-image:url('<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{user.ProfilePicture}}');">
                        <div class="category-thumb-txt" ng-cloak>
                            {{user.FirstName+" "+user.LastName}}
                            <div class="small" ng-bind="user.CityName"></div>
                        </div>
                        <div class="category-thumb-overlay"></div>
                    </div>
                    <div class="category-thumb category-thumb-default" ng-cloak ng-if="user.ProfilePicture==''" style="background:{{(user.defaultColor)?user.defaultColor:'#F4C8DD !important'}}">
                        <div class="category-thumb-txt" ng-cloak>
                            <div class="text">
                                <div>{{user.FirstName+" "+user.LastName}}</div>
                                <div class="small" ng-bind="user.CityName" ng-cloak></div>
                            </div>
                        </div>
                        <div class="category-thumb-overlay"></div>
                    </div>
                    <div class="category-thumb-overlay-txt" ng-if="user.TagLine!='' && user.TagLine!=null" ng-cloak ng-bind="user.TagLine"></div>
                    <div class="category-thumb-overlay-txt" ng-if="user.TagLine=='' || user.TagLine==null" ng-cloak>I am here to influence people with my ideas and experiences!</div>
                </div>
            </div>
        </div>
    </div>
    <section class="navbar navbar-fixed-bottom navbar-default">
        <div class="container">
            <a href="javascript:void(0);" ng-click="goToNext('people');" class="btn btn-primary navbar-btn uppercase pull-right">NEXT</a>
            <div class="catgry-selected">
                <span class="badge-count badge-count-rounded" ng-bind="followCount"></span>
                <span class="bold uppercase">Selections</span>
            </div>
        </div>
    </section>
</div>
<input type="hidden" id="redirect_url" value="<?php echo $redirect_url ?>">