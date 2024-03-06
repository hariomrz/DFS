<div ng-cloak ng-if="category_detail.Name" class="row">
    <div class="col-sm-9">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#seconDaynav" aria-expanded="false">All Posts</button>
        </div>
        <div class="navbar-collapse collapse" id="seconDaynav" ng-cloak>
            <ul class="nav navbar-nav nav-caret">
                <?php if($ShowWall){ ?>
                <li>
                    <a ng-href="{{category_detail.FullURL}}">
                        <?php echo lang('wall');?>
                    </a>
                </li>
                <?php } else { ?>
                <li ng-class="(PostTypeName=='All Posts') ? 'active' : '' ;">
                    <a ng-click="filterPostType({'Value':0,'Label':'All Posts'})">
                        <?php echo lang('all_posts');?>
                    </a>
                </li>
                <li ng-class="(PostTypeName=='Discussions') ? 'active' : '' ;">
                    <a ng-click="filterPostType({'Value':1,'Label':'Discussions'})">
                        <?php echo lang('discussions');?>
                    </a>
                </li>
                <li ng-class="(PostTypeName=='Questions') ? 'active' : '' ;">
                    <a ng-click="filterPostType({'Value':2,'Label':'Questions'})">
                        <?php echo lang('qna');?>
                    </a>
                </li>
                <?php } ?>
              
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">More<span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-left">
                        <li <?php if($Active=='Media' ) { echo "class='active'"; } ?>>
                            <a ng-href="{{category_detail.FullURL+'/media'}}">
                                <?php echo lang('media');?>
                            </a>
                        </li>
                        <li <?php if($Active=='Members' ) { echo "class='active'"; } ?>>
                            <a ng-href="{{category_detail.FullURL+'/members'}}">
                                <?php echo lang('members');?>
                            </a>
                        </li>
                        <li ng-if="SettingsData.m38=='1'" <?php if($Active=='Wiki' ) { echo "class='active'"; } ?>>
                            <a ng-href="{{category_detail.FullURL+'/wiki'}}">Articles</a>
                        </li>
                        <li <?php if($Active=='Files' ) { echo "class='active'"; } ?>>
                            <a ng-href="{{category_detail.FullURL+'/files'}}">
                                <?php echo lang('files');?>
                            </a>
                        </li>
                        <li <?php if($Active=='Links' ) { echo "class='active'"; } ?>>
                            <a ng-href="{{category_detail.FullURL+'/links'}}">
                                <?php echo lang('links');?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
   
    <div class="col-sm-3">
        <ul class="buttons-group">
            <?php if($Active=='Wiki' && isset($CanCreateWiki) && $CanCreateWiki){ ?>
            <!--<li>
                <button aria-expanded="false" class="btn  btn-primary" type="button" data-toggle="modal" data-target="#addWiki">
                    <span class="text"><i class="icon-add"></i> Add a Wiki</span>
                </button>
            </li>-->
            <?php } ?>
            <?php if(!$ShowWall || $Active=='Wiki'){ ?>

            <li>
                <button class="btn btn-default btn-sm btn-filter" ng-if="LoginSessionKey" ng-cloak ng-click="setFilterFixed(true)">
                    <span class="icon">
                        <i class="ficon-filter"></i>
                    </span>
                    <span class="caret"></span>
                </button>
            </li>
            <?php } ?>
            <li ng-if="category_detail.Permissions.IsAdmin">
                <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
                    <span class="icon">
                        <i class="ficon-settings"></i>
                    </span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a ng-if="category_detail.ParentCategoryID=='0'" data-toggle="modal" data-target="#addCategory" ng-click="set_current_forum_id(category_detail.ForumID); set_current_forum_guid(category_detail.ForumGUID,1); reset_media(); prefill_category(category_detail.ForumID,category_detail,1)">
                            <?php echo lang('edit_category');?>
                        </a>
                        <a ng-if="category_detail.ParentCategoryID!='0'" data-toggle="modal" data-target="#addSubCategory" ng-click="get_forum_category_list(category_detail.ForumID,category_detail.P_ForumCategoryID,category_detail.ParentCategory); set_current_forum_id(category_detail.ForumID); prefill_subcat_data(category_detail,category_detail.ForumCategoryID)">
                            <?php echo lang('edit_subcategory');?>
                        </a>
                    </li>
                    <li>
                        <a ng-href="{{BaseUrl+'community/members_settings/'+category_detail.ForumID+'/'+category_detail.ForumCategoryID}}">
                            <?php echo lang('member_settings');?>
                        </a>
                    </li>
                    <li>
                        <a ng-if="category_detail.ParentCategoryID=='0'" ng-click="delete_category(category_detail.ForumCategoryID,3,'Category')">
                            <?php echo lang('delete_category');?>
                        </a>
                        <a ng-if="category_detail.ParentCategoryID!='0'" ng-click="delete_category(category_detail.ForumCategoryID,3,'Subcategory')">
                            <?php echo lang('delete_subcategory');?>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>