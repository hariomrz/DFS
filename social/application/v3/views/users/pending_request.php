<!-- BEGIN CONTAINER -->
<div class="page-content" ng-controller="UserListCtrl" id="UserListCtrl" ng-init="searchmember()">
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

  <div <?php if($Type=='Friends'){ ?> ng-if="list.FriendStatus=='1'" <?php } ?> class="col-sm-4 group-member-col" id="user{{list.UserID}}" ng-repeat="list in listObj = allmember" ng-hide="list.length>0">

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
                      <?php echo lang('no_request') ?>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
</div>





             <input type="hidden" name="Type" id="Type"  value="<?php echo $Type; ?>"/>

<input type="hidden" id="PendingPageNo" value="1" />
</div>

