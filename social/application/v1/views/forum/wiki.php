<div ng-controller="ForumCtrl" id="ForumCtrl">
    <div ng-controller="WallPostCtrl" id="WallPostCtrl" ng-init="GetWallPostInit()">
        <?php $this->load->view('widgets/breadcrumb-forum',array('PageName'=>'')) ?>
        <div class="container wrapper">
            <div class="row">
                <?php $this->load->view('widgets/category_details') ?>
                <div ng-cloak class="col-sm-12">
                    <div class="feed-title" ng-bind="PostTypeName"></div>
                    <!--  secondary-nav -->
                    <div class="navbar navbar-static">
                        <div class="filter-fixed" ng-show="filterFixed" ng-cloak>
                            <button class="btn btn-default close-filter" ng-click="setFilterFixed(false)">
                                <span class="icon">
                                    <i class="ficon-cross"></i>
                                </span>
                                <span class="caret"></span>
                            </button>
                            <div class="main-filter-nav">
                                <nav class="navbar navbar-default navbar-static">
                                    <?php $this->load->view('include/filter-options') ?>
                                </nav>
                            </div>
                        </div>
                        <!-- //Filter Name -->
                        <?php $this->load->view('forum/nav',array('ShowWall'=>1,'Active'=>'Wiki')) ?>
                    </div>
                    <!-- // secondary-nav -->
                    <div class="row">
                        <?php $this->load->view('wiki/wiki',array('ShowFilter'=>'0')) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('forum/popup') ?>
</div>
<input type="hidden" id="ForumID" value="<?php echo $ForumID ?>" />
<input type="hidden" id="ForumCategoryID" value="<?php echo $ForumCategoryID ?>" />
<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="UserGUID" value="<?php echo $this->session->userdata('UserGUID') ?>" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="FeedSortBy" value="2" />
<input type="hidden" id="IsMediaExists" value="2" />
<input type="hidden" id="PostOwner" value="" />
<input type="hidden" id="ActivityFilterType" value="0" />
<input type="hidden" id="AsOwner" value="0" />
<input type="hidden" id="IsWall" value="1" />
<input type="hidden" id="IsForum" value="1" />
<input type="hidden" id="CatMediaGUID" value="" />
<input type="hidden" id="IsAdmin" value="<?php echo ($IsAdmin) ? '1' : '0' ; ?>" />
