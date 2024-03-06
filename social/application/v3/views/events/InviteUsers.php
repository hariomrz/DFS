<div class="panel-body">
  <ul class="list-group removed-peopleslist hover-none">
    <li class="list-group-item">
      <p ng-cloak > <span ng-bind="EventDetail.Description|limitTo:DescriptionLimit"></span> <a href='javascript:void(0);' ng-if="EventDetail.Description.length > DescriptionLimit" ng-click="showMoreDesc(EventDetail.Description.length)"><?php echo lang('see_more');?></a> </p>
      <div class="form-group" ng-if="(loggedUserRole=='1' || loggedUserRole=='2') && EventDetail.EventStatus!=='Past'">
        <h3 class="panel-subtitle"><?php echo lang('invite_friends_to_join');?></h3>
        <div class="text-field">
          <tags-input 
                ng-model="tagsInvited" 
                display-property="Name" 
                add-from-autocomplete-only="true" 
                on-tag-added="tagAddedInvited($tag)"  
                on-tag-removed="tagRemovedInvited($tag)"
                placeholder="Invite your friends to event">
            <label class="error-block-overlay" id="errorEventMember" ng-bind-template="{{errorEventMember}}"></label>
            <auto-complete source="loadTags($query)"></auto-complete>
          </tags-input>
          <button id="InviteEventBtn" ng-click="inviteEventUsers('0','Invited')"  type="button" class="btn  btn-primary btn-icon m-t-10"> 
          <span class="icon"><i class="ficon-plus"></i></span> <?php echo lang('invite');?> 
          <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span>
          </button>
        </div>
      </div>
    </li>
  </ul>
</div>
