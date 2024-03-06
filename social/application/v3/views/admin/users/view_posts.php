<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span>Users</span></li>
                    <li>/</li>
                    <li><span>Posts</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div ng-controller="UserListCtrl" id="UserListCtrl"> 
    <div id="WallPostCtrl" ng-controller="WallPostCtrl" ng-init="GetWallPostInit();">
        
        <div  ng-controller="NewsFeedCtrl" id="NewsFeedCtrl"
              
             
            infinite-scroll="GetwallPost()" 
            infinite-scroll-distance="2" 
            infinite-scroll-use-document-bottom="true" 
            infinite-scroll-disabled="is_busy"
              
        >
        
        <section class="filter-default">
            <div class="container">
                <?php $this->load->view('admin/users/filter_options') ?>
            </div>
        </section>
        <section class="main-container">
            <div class="container container-sm">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="page-heading">
                            <div class="row">
                                <div class="col-xs-4">
                                    <h4 class="page-title">Posts <span ng-if="tr>0" ng-bind="'('+tr+')'"></span></h4>
                                </div>
                                <div class="col-xs-8">
                                    <div class="page-actions">
                                        <div class="row ">
                                            <div class="col-xs-6 col-xs-offset-6">
                                                &nbsp;
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div >
                            <?php $this->load->view('admin/users/post_single') ?>
                        </div>
                        
                        <?php $this->load->view('admin/users/activity') ?>
                        <?php $this->load->view('admin/users/media_popup') ?>

                    </div>
                </div>
            </div>
        </section>
        
        </div>
    </div>
</div>


<script>
    var LoggedInUserID = '<?php  echo $this->session->userdata('AdminUserID'); ?>';    
    var site_url = '<?php echo base_url(); ?>';
</script>