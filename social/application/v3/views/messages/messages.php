<div class="container wrapper">
  <div class="page-content message-page" id="messageSectionCtrl" data-ng-controller="messageSectionCtrl">
    <div class="pages-block">
      <div class="pages-head">
        <h4><?php echo lang('messages') ?></h4>
         <div class="msgRight-action pull-right">
        	<a class="btn btn-default btn-small" ng-init="newMessageCompose()" ng-click="newMessageCompose()" id="newMsz">New</a>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="message-region clearfix"> 
        <!-- Message Panel -->
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 messsag-left-col">
          <section class="message-panel" ng-init="getMessage('Inbox','192')">
            <div class="message-ctrl">
              <nav class="message-nav"> <a href="javascript:void(0);" class="active messages-title titleInbox" ng-click="getMessage('InboxF', '192')">INBOX</a> <a href="javascript:void(0);" class="messages-title titleTrash" ng-click="getMessage('TrashF', '192')">TRASH</a> </nav>
            </div>
            <article class="message-search-wrap">
              <div class="message-search">
                <div class="text-field-iconright">
                  <button type="button" class="field-icon icon"><i class="icon-search-gray"></i></button>
                  <input type="text" ng-init="searchKey=''" ng-model="searchKey" ng-keyup="getMessageSearch()" placeholder="Search">
                </div>
              </div>
              <div class="defaultScroller message-group mszList" ng-style="mszListStyle()">
                <ul class="message-list-group" id="ulID">
                  <li class="message-list-item" ng-repeat="(key, mszList) in MszList" ng-class="{'unread' : mszList.UnreadCount > 0}" ng-click="resetMessageDetails(mszList.MessageGUID,'192')"> <a class="thumb img-circle" ng-class="{'group-thumb-two' : mszList.Users.length == 2, 'group-thumb' : mszList.Users.length > 2 }"> <span ng-repeat="img in mszList.Users | limitTo:3"> <img  ng-src="{{img.ProfilePicture}}"/> </span> </a>
                    <div class="overflow">
                      <div class="overflow"> <span class="user"> <span ng-repeat="(key, Users) in mszList.Users" ng-if="mszList.Users.length == 1" ng-bind="Users.FirstName +' '+ Users.LastName"> </span> <span ng-repeat="(key, Users) in mszList.Users" ng-if="mszList.Users.length > 1" ng-bind="Users.FirstName"></span> </span> </div>
                      <span class="mszlist-ctrl"> <span class="mszcount" ng-if="mszList.UnreadCount > 0">{{mszList.UnreadCount}} new</span> <span class="mszlistctrl"> <a class="icon-closesmall" ng-init="isTrash = isTrashFn(mszList.UnreadCount)" title="Delete message" ng-click="moveTrash(mszList.MessageGUID, isTrash); $event.stopPropagation();"></a> <a class="icon-circle" ng-init="isRead = isReadFn(mszList.UnreadCount)" title="Mark as read / unread" ng-if="mszList.Status=='9' || mszList.Status=='11'" ng-click="changeFlagStatus(mszList.MessageGUID, mszList.UnreadCount); $event.stopPropagation();"></a> <a class="icon-message" ng-if="mszList.Status=='12'" title="Move to inbox" ng-click="moveTrash(mszList.MessageGUID,'9'); $event.stopPropagation();"></a> </span> <span class="pull-right muted msztime" ng-bind="date_format((mszList.ModifiedDate))"></span> </span>
                      <div class="message-text">
                        <p ng-bind="mszList.Subject"></p>
                      </div>
                    </div>
                  </li>
                  <li class="no-message" ng-cloak ng-if="mnum==0"><span ng-bind="nomsg"></span></li>
                  <li style="display:none;" class="msg-loadr loaderbtn">
                    <div><div class="spinner32"></div></div>
                  </li>
                </ul>
              </div>
            </article>
          </section>
        </div>
        <!--/ Message Panel --> 
        
        <!-- Message Body -->
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 messsag-right-col">
          <section class="message-body">
            <section class="message-box-header clearfix"> 
             <a href="javascript:void(0);" id="backList" class="pull-left visible-xs backIcon"><i class="icon-backmsg"></i></a>
              <h3 class="pull-left add-users" ng-if="isNewMessage">
                <?php if(!empty($user_details)) { ?>
                <span class="add-user-id" id="user-<?php echo $user_details['UserGuID']; ?>"><?php echo $user_details['FirstName']; ?> 
             	  <a onclick="removeParentSpanEle($(this).parent('span').attr('id'));" href="javascript:void(0);">x</a> 
                </span>
                <?php } else { ?>
                	No Selection
                <?php } ?>
              </h3>
              <h3 class="pull-left" ng-if="!isNewMessage">{{UsersName}}</h3>
              <!-- Hide actions button for now --> 
              <!--<a class="btn btn-orange btn-small pull-right" data-toggle="dropdown" id="mszActions">Actions</a>-->
              <ul class="dropdown-menu msz-actions" role="menu" aria-labelledby="mszActions">
                <li><a>Option #1</a></li>
                <li><a>Option #2</a></li>
                <li><a>Option #3</a></li>
                <li><a>Option #4</a></li>
              </ul>
            </section>
            <section class="message-box-content mszBox" ng-style="mszBoxStyle()">
              <section style="display:none;" class="message-form-group mail-to">
                <label class="control-label">To:</label>
                <div class="controls controls-to">
                  <input type="text" class="form-control" id="addUsers" placeholder="Username">
                </div>
              </section>
              <div class="msg-lisitings">
              	<ul class="view-message-group">
                <li class="view-message-item" ng-repeat="msg in messageDetails"> <a ng-href="{{msg.ProfileURL}}" class="thumb"><img ng-src="{{msg.ProfilePicture}}" class="img-circle" ></a>
                  <div class="overflow">
                    <div class="overflow"> 
                        <a class="user" ng-href="{{msg.ProfileURL}}" ng-bind="msg.FirstName+' '+msg.LastName"></a> 
                        <span class="pull-right muted msztime" ng-bind="date_format((msg.CreatedDate));"></span> 
                    </div>
                    <div class="message-text">
                      <p ng-bind-html="textToLink(msg.Body)"></p>
                    </div>
                  </div>
                </li>
              </ul>
              </div>
            </section>
            <section class="message-composer">
              <div class="composer-inner">
                <textarea class="form-control" rows="3" ng-model="msgBody" ng-init="msgBody" placeholder="Write a message"></textarea>
                <div class="message-action">
                  <div class="msg-ctrl"> </div>
                  <button type="button" ng-if="messageDetails==''" class="btn btn-primary btn-small pull-right" ng-click="sendMessage('',msgBody,'')"> Send </button>
                  <button type="button" ng-if="messageDetails!=''" class="btn btn-primary btn-small pull-right" ng-click="sendMessage('',msgBody,messageDetails[0].MessageGUID)"> Send </button>
                </div>
              </div>
            </section>
          </section>
        </div>
        <!--/ Message Body --> 
      </div>
    </div>

    <?php if(!empty($MessageGuID)) { ?>
      <input type="hidden" ng-init="resetMessageDetails('<?php echo $MessageGuID; ?>','192')" />
  <?php } if(!empty($user_details)){ ?>
    <input type="hidden" ng-init="newMessageCompose2()" />
  <?php } ?>

  </div>
</div>
<script>
/*window.onload = function() {
	<?php if(!empty($MessageGuID)) { ?>
	    angular.element(document.getElementById('messageSectionCtrl')).scope().getMessageDetails('<?php echo $MessageGuID; ?>','192');

	<?php } if(!empty($user_details)){ ?>
	    angular.element(document.getElementById('messageSectionCtrl')).scope().newMessageCompose2();
	<?php } ?>
}*/
</script>

<input type="hidden" id="selectedUsers" value="<?php if(!empty($user_details)) { echo $user_details['UserGuID']; } ?>" />
<input type="hidden" id="PageNo" value="1" />
<input type="hidden" id="MessagePageNo" value="1" />
<link rel="stylesheet" type="text/css" href="<?php echo ASSET_BASE_URL.'css/messages.css' ?>" />
