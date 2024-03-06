
<?php
if(!isset($UserID)){
  $UserID = $this->session->userdata('UserID');
}
?>

<!-- BEGIN CONTAINER -->
<div class="page-content" ng-controller="UserListCtrl" id="UserListCtrl" ng-init="searchmember()">

<div class="profile-container" ng-controller="UserProfileCtrl" ng-init="fetchDetails('load')" id="UserProfileCtrl">
    <?php $this->load->view('profile/profile_banner') ?>
    <!-- /Banner Image Chrop modal -->
    <div class="profile-detail-content">
      <div class="container">
        <div class="profile-header">
          <figure class="profile-thumb">
            <input type="hidden" name="profile_media" value="{{ProfilePicture}}"/>
            <a href="javascript:void(0);" class="profile-thumb-inner">
              
              <?php if($UserID == $this->session->userdata('UserID')){ ?>
                <i class="fa fa-pencil profile-thumb-edit" id="thumbEditToggle"></i>
                <span class="thumb-option" id="thumbEditContent">
                  <span id="profile-picture">Upload New</span>
                  <span ng-click="removeProfilePicture()">Remove</span>
                  <i class="fa fa-caret-up thumb-option-arrow"></i>
                </span>
              <?php } ?>

              <img ng-src="{{imgsrc}}"  />
              <!-- <i class="del-ico" onclick="removeThisMedia(this);"><?php echo lang('remove') ?></i> -->
            </a>
            <span class="profile-thumb-inner editMode" id="uploadprofilepic">
              <span class="profile-img-null" id="profile-picture"></span>
              <span class="profile-img-loader" id="loader"></span>
            </span>

            <span class="title">
            </span>

            

          </figure>
          <nav class="profile-status">
            <a href="<?php echo site_url('users/friends').'/'.get_detail_by_id($UserID, 3, 'UserGUID' , 1); ?>">
              <strong ng-if="records>0" class="b" ng-bind="records"></strong>
              <span ng-if="records>1"><?php echo lang('connections') ?></span>
              <span ng-if="records==1"><?php echo lang('connection') ?></span>
            </a>
          </nav>


          <!-- Profile Navigation -->
          <?php if($this->session->userdata('UserID')==$UserID){ ?>
            
          <?php } else { ?>
          <nav class="profile-navigation ng-hide" ng-controller="UserListCtrl" ng-init="getProfileUser()" ng-hide="showProfileAction">

            <button type="button" ng-if="profileUser.CanReport=='1'" data-toggle="modal" id="tid-user-<?php echo $UserID ?>" data-target="#reportAbuse" onclick="flagValSet('<?php echo $UserID ?>','User')" class="btn btn-orange btn-small"><?php echo lang('report_abuse') ?></button>
            <button type="button" id="tid2-user-<?php echo $UserID ?>" style="display:none;" class="btn btn-orange btn-small">Flagged</button>

            <?php if($this->session->userdata('UserID')!=$UserID){ ?>
            <button type="button" ng-if="profileUser.CanReport!='1'" class="btn btn-orange btn-small">Flagged</button>
            <?php } ?>
            <button type="button" ng-if="profileUser.ShowFollowBtn==1 " class="btn btn-orange btn-small" id="followmem{{profileUser.UserID}}" ng-click="follow(profileUser.UserID)">{{profileUser.follow}}</button>
            <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='1' && profileUser.ShowFriendsBtn=='1'" ng-click="removeFriend(profileUser.UserID)"><?php echo lang('delete_request') ?></button>

            <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='2' && profileUser.ShowFriendsBtn=='1'" ng-click="rejectRequest(profileUser.UserID)"><?php echo lang('cancel_request') ?></button>

            <button type="button" class="btn btn-orange btn-small" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='3'" ng-click="denyRequest(profileUser.UserID)"><?php echo lang('deny') ?></button>

            <button type="button" class="btn btn-orange btn-small" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='3'" ng-click="acceptRequest(profileUser.UserID)"><?php echo lang('accept') ?></button>

            <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='4' && profileUser.ShowFriendsBtn=='1'" ng-click="sendRequest(profileUser.UserID)"><?php echo lang('send_request') ?></button>

            <!--<button type="button" data-toggle="modal" data-target="#reportAbuse" class="btn btn-orange btn-small"><?php echo lang('report_abuse') ?></button>-->
          </nav>
          <?php } ?>
          <!--/ Profile Navigation -->



        </div>
        <div class="profile-content">
          <div class="profile-content-view">
            <div class="content-body" ng-bind-html="aboutme"></div>

            <div class="profile-tag-view" ng-if="expObj == undefined">
              <a class="tag-view ng-hide" ng-show="expObj.length>0" ng-repeat="exp in expObj = Expertise" ng-bind="exp.Expertise"></a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Global Navigation -->
    <?php $this->load->view('include/inner-navigation'); ?>
    <!--/ Global Navigation -->
  </div>





<div class="container-wrap">
  <div class="content container">
    <div class="row">
      <div class="col-md-12">
        <div class="grid simple group-header">
          
          <div class="page-title-block">
            <div class="text-field-iconright" id="Schfield">
              <button type="button" class="field-icon icon fa fa-search" id="searchinvitemember" class="btn btn-orange pull-right" ng-click = "searchmember()"></button>

              <input type="text" aria-controls="example" id ="searchformember"  placeholder="<?php echo lang('quick_search') ?>"/>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="group-list trans-col p-t-5" id="showmember">
      <div class="row raw8px ng-hide" ng-hide="listObj == undefined">

        <div class="col-sm-4 group-member-col" id="user{{list.UserID}}" ng-repeat="list in listObj = allmember">

          <div class="overflow ">
            <div class="user-profile-wrap"><img ng-src="{{list.ProfilePicURL}}" ></div>
            <div class="overflow">
              <h2 class="member-col-title overflow semi-bold"><a ng-href="{{list.ProfileLink}}">{{list.FirstName}} {{list.LastName}}</a></h2>
              <div class="member-col-content overflow">
                {{list.Location}}
              </div>
            </div>


          </div>
          <div class="member-col-footer overflow">
            <button type="button" ng-if="list.ShowFollowBtn==1 && list.MySelf!='1'" class="btn btn-orange btn-small" id="followmem{{list.UserID}}" ng-click="follow(list.UserID)"><span class="bold">{{list.FollowStatus}}</span></button>
            <?php /*<div class="small-text-description p-t-5 inline">{{list.Earnpoints}} Points</div> */ ?>

            <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='1' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="removeFriend(list.UserID)"><span class="bold"><?php echo lang('delete_request') ?></span></button>

            <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='2' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="rejectRequest(list.UserID)"><span class="bold"><?php echo lang('cancel_request') ?></span></button>

            <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='4' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="sendRequest(list.UserID)"><span class="bold"><?php echo lang('send_request') ?></span></button>

            <button type="button" class="btn btn-orange btn-small pull-right w70 m-l-5" lang="{{list.UserID}}" ng-if="list.FriendStatus=='3' && list.ShowFriendsBtn=='1' && list.MySelf!='1'"  ng-click="denyRequest(list.UserID)"><span class="bold"><?php echo lang('deny') ?></span></button>

            <button type="button" class="btn btn-orange btn-small pull-right w70" lang="{{list.UserID}}" ng-if="list.FriendStatus=='3' && list.ShowFriendsBtn=='1' && list.MySelf!='1'"  ng-click="acceptRequest(list.UserID)"><span class="bold"><?php echo lang('accept') ?></span></button>
          </div>


        </div>

      </div>
    </div>
    <div class="group-list" id="grpHasNoMember" style="display:none">
      <div class="row profile-detail-wrap">
        <div class="row column-seperation">
          <div class="col-md-12">
            <div class="col-md-5 pull-none center-text p-t-95 p-b-95">
              <div> <i class="icn-nogroup"></i>
              </div>
              <div class="semi-bold font16 color-grey m-t-15 m-b-15">
                <?php echo lang('no_member') ?>
              </div>
            </div>
          </div>
        </div>
      </div>






    </div>

    <input type="hidden" name="Type" id="Type"  value="<?php echo $Type; ?>"/>
    <input type="hidden" id="FollowersPageNo" value="1" />
    <input type="hidden" id="UID" value="<?php echo $UID ?>" />
    <input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />
  </div>
</div>
</div>


<script type="text/javascript">
$(document).ready(function(){
  setTab(3);
});
</script>

<?php /*
<!-- BEGIN CONTAINER -->
<div class="page-content" ng-controller="UserListCtrl" id="UserListCtrl" ng-init="searchmember()">
  <div class="content container">
    <div class="row">
      <div class="col-md-12">
      <div class="grid simple group-header">
        <?php $this->load->view('include/inner-navigation'); ?>
        <div class="page-title-block">
        <div class="text-field-iconright" id="Schfield">
          <button type="button" class="field-icon icon fa fa-search" id="searchinvitemember" class="btn btn-orange pull-right" ng-click = "searchmember()"></button>

          <input type="text" aria-controls="example" id ="searchformember"  placeholder="<?php echo lang('quick_search') ?>"/>
        </div>
        </div>
        </div>
      </div>
    </div>

<div class="group-list trans-col p-t-5" id="showmember">
  <div class="row raw8px ng-hide" ng-hide="listObj == undefined">

  <div class="col-sm-4 group-member-col" id="user{{list.UserID}}" ng-repeat="list in listObj = allmember" ng-hide="list.length>0">

<div class="overflow ">
        <div class="user-profile-wrap"><img ng-src="{{list.ProfilePicURL}}" ></div>
        <div class="overflow">
          <h2 class="member-col-title overflow semi-bold"><a ng-href="{{list.ProfileLink}}">{{list.FirstName}} {{list.LastName}}</a></h2>
          <div class="member-col-content overflow">
            {{list.Location}}
          </div>
        </div>

      
      </div>
      <div class="member-col-footer overflow">
        <button type="button" ng-if="list.ShowFollowBtn==1 && list.MySelf!='1'" class="btn btn-orange btn-small" id="followmem{{list.UserID}}" ng-click="follow(list.UserID)"><span class="bold">{{list.FollowStatus}}</span></button>
        
        <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='1' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="removeFriend(list.UserID)"><span class="bold"><?php echo lang('delete_request') ?></span></button>

        <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='2' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="rejectRequest(list.UserID)"><span class="bold"><?php echo lang('cancel_request') ?></span></button>

        <button type="button" class="btn btn-orange btn-small pull-right w140" lang="{{list.UserID}}" ng-if="list.FriendStatus=='4' && list.ShowFriendsBtn=='1' && list.MySelf!='1'" ng-click="sendRequest(list.UserID)"><span class="bold"><?php echo lang('send_request') ?></span></button>

        <button type="button" class="btn btn-orange btn-small pull-right w70 m-l-5" lang="{{list.UserID}}" ng-if="list.FriendStatus=='3' && list.ShowFriendsBtn=='1' && list.MySelf!='1'"  ng-click="denyRequest(list.UserID)"><span class="bold"><?php echo lang('deny') ?></span></button>

        <button type="button" class="btn btn-orange btn-small pull-right w70" lang="{{list.UserID}}" ng-if="list.FriendStatus=='3' && list.ShowFriendsBtn=='1' && list.MySelf!='1'"  ng-click="acceptRequest(list.UserID)"><span class="bold"><?php echo lang('accept') ?></span></button>
      </div>


  </div>
    
  </div>
</div>
<div class="group-list" id="grpHasNoMember" style="display:none">
  <div class="row profile-detail-wrap">
      <div class="row column-seperation">
          <div class="col-md-12">
              <div class="col-md-5 pull-none center-text p-t-95 p-b-95">
                  <div> <i class="icn-nogroup"></i>
                  </div>
                  <div class="semi-bold font16 color-grey m-t-15 m-b-15">
                      <?php echo lang('no_member') ?>
                  </div>
              </div>
          </div>
      </div>
  </div>






  </div>

<input type="hidden" name="Type" id="Type"  value="<?php echo $Type; ?>"/>
<input type="hidden" id="FollowersPageNo" value="1" />
<input type="hidden" id="UID" value="<?php echo $UID ?>" />
</div>
</div>


<script type="text/javascript">
$(document).ready(function(){
  setTab(3);
});
</script> */ ?>