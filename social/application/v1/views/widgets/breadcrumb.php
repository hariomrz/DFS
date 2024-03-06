<div class="breadcrumb-fluid">
  <ol ng-cloak="" class="breadcrumb bordered container">
        <?php
            switch ($PageName) {
                case 'Manage Admin':
                ?>
                    <li class="breadcrumb-item">
              <a target="_self" ng-bind="forum_detail.Name"></a>
            </li>
                <?php
                default:
                //case 'Member Settings':
                ?>
            <li ng-cloak="" class="breadcrumb-item" ng-if="category_detail.Breadcrumbs.Forum.Name">
              <a target="_self" ng-href="<?php echo base_url(); ?>{{'community/'+category_detail.Breadcrumbs.Forum.Link}}" ng-bind="category_detail.Breadcrumbs.Forum.Name"></a>
            </li>
                    <li ng-cloak="" class="breadcrumb-item" ng-if="category_detail.Breadcrumbs.Category.Name">
              <span class="icon">
                <i class="ficon-arrow-right"></i>
              </span>
              <a target="_self" ng-href="<?php echo base_url(); ?>{{'community/'+category_detail.Breadcrumbs.Category.Link}}" ng-bind="category_detail.Breadcrumbs.Category.Name"></a>
            </li>
            <li ng-cloak="" class="breadcrumb-item" ng-if="category_detail.Breadcrumbs.SubCategory.Name">
              <span class="icon">
                  <i class="ficon-arrow-right"></i>
              </span>
              <a target="_self" ng-href="<?php echo base_url(); ?>{{'community/'+category_detail.Breadcrumbs.SubCategory.Link}}" ng-bind="category_detail.Breadcrumbs.SubCategory.Name"></a>

            </li>
                    <!--<li class="breadcrumb-item"><a target="_self" ng-bind="category_detail.Name"></a></li>-->
                <?php
                break;
            }
        ?>
      <?php if($PageName == 'Post'){ ?>
        <li ng-cloak="" class="breadcrumb-item active" ng-if="activityData[0].PostTitle">
          <span class="icon">
              <i class="ficon-arrow-right"></i>
          </span>
          <span ng-bind="activityData[0].PostTitle"></span>
        </li>
      <?php } else if($PageName) { ?>
        <li ng-cloak="" class="breadcrumb-item active">
        <span class="icon">
            <i class="ficon-arrow-right"></i>
        </span>
        <?php echo $PageName ?>
        </li>
      <?php } ?>
  </ol>
</div>

