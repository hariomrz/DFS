<?php if(!$this->session->userdata('LoginSessionKey')){ ?>
        <?php //$this->load->view('include/non-loggedin') ?>
<?php } ?>
<div id="ForumCtrl" ng-controller="ForumCtrl">
  <div ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl">
    <!--Container-->
    <?php $this->load->view('community/banner') ?>
    <?php $this->load->view('community/search') ?>
    <?php $this->load->view('community/slider') ?>

    <div class="nav-tab-nav" data-scrollfix="scrollFix">
      <!-- // secondary-nav -->
     
      <div class="container container-primary"> 
        <?php $this->load->view('community/nav') ?>
      </div>
    </div>

    <div class="container wrapper container-primary">
      <div class="row">      
        <!-- Left Wall-->
        <aside class="col-lg-8 col-sm-8">
          <?php //$this->load->view('include/post/forum'); ?>
            <div ng-include="AssetBaseUrl + 'partials/include/post/forum_category.html' + app_version"></div>
            <div ng-init="setWallData()">
            <div ng-init="GetwallPostTime();" ng-if="wlEttDt.ModuleEntityGUID">
                             
                <div ng-if="wlEttDt.ModuleEntityGUID" ng-include="AssetBaseUrl + 'partials/community/wall.html'" ></div>
                
            </div>
          </div>

        </aside>
        <!-- //Left Wall-->
        <!-- Right Side Bar -->
        <?php $this->load->view('community/right') ?>
        <!--// Right Side Bar -->
      </div>
    </div>
    <!--//Container-->
  </div>
</div>

<input type="hidden" value="1" id="IsLandingPage" />
<input type="hidden" id="IsForumWall" value="1" />