<script type="text/javascript">
    var redirect_url = "<?php echo $redirect_url ?>";
</script>

<div ng-init="get_categories()" class="container wrapper container-primary">
    <div class="category-text">
        <h1 class="category-heading">Follow these forum categories!</h1>
        <p class="lead">Select atleast 4. The more you select, the better we get in recommending you good content!</p>
    </div>
    <div class="row categories">
        <div class="col-sm-4 col-lg-3" data-ng-repeat="(index,category) in forum_categories" data-notify-when-repeat-finished ng-click="toggle_follow_category(category.ForumCategoryID,index)">
            <div class="categories-box" ng-class="(category.Permissions.IsMember)?'active':''">
                <a class="category-select">
                    <i class="ficon-check"></i>
                </a>
                <div class="category-thumb" style="background-image:url('<?php echo IMAGE_SERVER_PATH;?>upload/profile/220x220/{{category.ProfilePicture}}');">
                    <div class="category-thumb-txt" ng-bind="category.Name"></div>
                    <div class="category-thumb-overlay"></div>
                </div>
                <div class="category-thumb-overlay-txt" ng-bind="category.Description"></div>
            </div>
        </div>
    </div>
</div>
<section class="navbar navbar-fixed-bottom navbar-default">
    <div class="container">
        <a href="javascript:void(0)" ng-click="goToNext('categories')" class="btn btn-primary navbar-btn uppercase pull-right">NEXT</a>
        <div class="catgry-selected">
            <span class="badge-count badge-count-rounded" ng-bind="followedCat"></span>
            <span class="bold uppercase">Selections</span>
        </div>
    </div>
</section>

<input type="hidden" id="IsInterestPage" value="1" />
<input type="hidden" id="redirect_url" value="<?php echo $redirect_url ?>">