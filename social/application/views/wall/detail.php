<div ng-controller="ActivityDetailsCtrl" ng-init="initActivityDetails()">
         
    <div ng-include="AssetBaseUrl + 'partials/widgets/breadcrumb-forum_all_types.html'" ></div>
    
    <div class="container wrapper">
        <div ng-controller="WallPostCtrl" id="WallPostCtrl" ng-init="GetWallPostInit()">
            <div class="row">
                
                
                <!-- Right Side Bar -->
                <div class="col-md-4 col-md-push-8 sidebar" style="transform: none;">                    

                    <?php 
                        $post_type='';
                        $activity_data = get_detail_by_guid($ActivityGUID, 0, 'PostType', 2);
                        if($activity_data)
                        {
                            $post_type=$activity_data['PostType'];
                        }
                        if($post_type==4)
                        {
                            //$this->load->view('widgets/fav-article');
                            //$this->load->view('widgets/recommended-articles');
                            $this->load->view('widgets/fav-article-forum');
                            //$this->load->view('widgets/recommended-articles-forum');
                            
                            ?>
                            <div ng-include="AssetBaseUrl + 'partials/widgets/recommended-articles-forum.html'" ></div>
                            <?php
                        }
                        else{
                            $this->load->view('widgets/related-discussions') ;
                        }

                        
                        if(!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                            $this->load->view('widgets/people-you-may-know'); 
                        } else {
                            $this->load->view('widgets/people-you-may-follow'); 
                        }    
    

                        if($Activity_ModuleID == 1) {
                            $this->load->view('widgets/suggested-groups') ;
                        }

                    ?> 
                </div>
                <!--// Right Side Bar -->
                <div class="col-md-8 col-md-pull-4" style="transform: none;">
                    <?php $this->load->view('include/post/newsfeed'); ?>
                    <section class="news-feed">
                        <?php //$this->load->view('wall/activity_details') ?>
                        
                        <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>
                        
                    </section>
                </div>
                <!-- //Left Wall-->

                </div>
            </div>
    </div>
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
<input type="hidden" id="Activity_ModuleID" value="<?php echo $Activity_ModuleID; ?>" />
<input type="hidden" id="Activity_ModuleEntityID" value="<?php echo $Activity_ModuleEntityID ?>" />