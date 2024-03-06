<script type="text/javascript">
    var redirect_url = "<?php echo $redirect_url ?>";
</script>

<div ng-init="getSubCategory('popular')" class="container wrapper container-primary">
    <div class="category-text">
        <h1 class="category-heading">What are you interested in?</h1>
        <p class="lead">Select atleast 4. But the more you select, better suggestions you'll get!</p>
    </div>
    <div class="row categories">
        <div class="col-sm-4 col-lg-3" data-ng-repeat="intlist in allInterestList" data-notify-when-repeat-finished ng-click="toggleBtn(intlist)">
            <div class="categories-box" ng-class="(intlist.IsInterested=='1') ? 'active' : '' ;" ng-click="">
                <a class="category-select">
                    <i class="ficon-check"></i>
                </a>
                <div class="category-thumb" style="background-image:url('<?php echo IMAGE_SERVER_PATH;?>upload/category/220x220/{{intlist.ImageName}}');">
                    <div class="category-thumb-txt" ng-bind="intlist.Name"></div>
                    <div class="category-thumb-overlay"></div>
                </div>
                <div class="category-thumb-overlay-txt" ng-bind="intlist.Description"></div>
            </div>
        </div>
    </div>
</div>
<section class="navbar navbar-fixed-bottom navbar-default">
    <div class="container">
        <a href="javascript:void(0)" ng-click="goToNext('interest')" class="btn btn-primary navbar-btn uppercase pull-right">NEXT</a>
        <div class="catgry-selected">
            <span class="badge-count badge-count-rounded" ng-bind="selectedCount"></span>
            <span class="bold uppercase">Selections</span>
        </div>
    </div>
</section>

<input type="hidden" id="IsInterestPage" value="1" />
<input type="hidden" id="redirect_url" value="<?php echo $redirect_url ?>">