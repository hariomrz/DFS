<?php if(isset($IsNewsFeed) && $IsNewsFeed=='1'){ ?>
  <?php if(!$this->session->userdata('LoginSessionKey')){ ?>

            <?php //$this->load->view('include/non-loggedin') ?>
   
  <?php } ?>
<?php } ?>

<?php 
if(!isset($UserID)){
  $UserID = $this->session->userdata('UserID');
}
?>
<input type="hidden" id="ActivityGUID" value="<?php echo isset($ActivityGUID) ? $ActivityGUID : "" ; ?>" />
    <?php if(!isset($AllActivity) || $AllActivity!='1'){ ?>
      <?php $this->load->view('profile/profile_banner') ?>
    <?php } ?>
    <!-- showWelcomePopup(); getCategoryGuid(); -->
      <div ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="getEntityList();">
        <?php 
          if(isset($IsNewsFeed)){
            $this->load->view('include/newsfeed-filter');
            $this->load->view('include/newsfeed_header');
          } 
        ?>
        <div class="container wrapper">
          <?php 
            if(isset($IsNewsFeed) && $IsNewsFeed=='1') {
              $this->load->view('widgets/newbies');
            } 
          ?>
          <div class="row" ng-cloak>
            <?php if(isset($IsNewsFeed) && $IsNewsFeed=='1') { ?>
              <div class="col-md-2 col-sm-12" data-scroll="leftSticky" ng-cloak="" ng-class="(postEditormode) ? 'recentCoversation' : '' ;">
            <?php } else { ?>
              <div class="col-md-2 col-sm-12" data-scroll="leftSticky">
            <?php } ?>
              <?php $this->load->view('sidebars/left'); ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-12 pull-right sidebar" data-scroll="rightSticky">

              <?php $this->load->view('sidebars/right'); ?>
            </div>
            <?php
            if (isset($IsNewsFeed) && $IsNewsFeed == '1' && isset($IsLoggedIn) && $IsLoggedIn) {
              $this->load->view('include/live-feed');
            }
            ?>
            <?php if((!isset($isFileTab) && !isset($isLinkTab)) || (!$isFileTab && !$isLinkTab) ): ?>
             <div class="col-md-7 col-sm-8 col-xs-12 pull-left" >
              <div ng-init="GetwallPostTime()">
                                  
                   <div ng-include="AssetBaseUrl + 'partials/wall/wall2.html'" ></div>
                  
              </div>
            </div>    
                
              <?php elseif(isset($isFileTab) && $isFileTab): ?>
              <div class="col-md-7 col-sm-8 col-xs-12 pull-left">
                <?php $this->load->view('users/files_list'); ?>
              </div>
              <?php elseif(isset($isLinkTab) && $isLinkTab): ?>
              <div class="col-md-7 col-sm-8 col-xs-12 pull-left">
                <?php $this->load->view('users/links_list'); ?>
              </div>
              
            <?php endif; ?>
              
          </div>
        </div>
        <?php $this->load->view('include/welcome-user-popup'); ?>
      </div>


      <input type="hidden" id="loginUserGUID" value="<?php echo $this->session->userdata('UserGUID'); ?>" />
      <input type="hidden" id="WallPageNo" value="1" />
      <input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />
      <input type="hidden" id="AllActivity" value="<?php if(isset($AllActivity)){ echo $AllActivity; } ?>" />
      <input type="hidden" id="UserWall" value="1" />
      <input type="hidden" id="FeedSortBy" value="2" />
      <input type="hidden" id="IsMediaExists" value="2" />
      <input type="hidden" id="PostOwner" value="" />
      <input type="hidden" id="ActivityFilterType" value="0" />
      <input type="hidden" id="AsOwner" value="0" />
      <input type="hidden" id="IsFriend" value="<?php echo $IsFriend; ?>" />

<script type="text/javascript">
function changeEntityID(value){
  $('#ShareEntityUserGUID').val(value);
}

</script>
<?php if(isset($RedirectPage)){ ?>
  <input type="hidden" id="RedirectPage" value="1">
<?php } ?>