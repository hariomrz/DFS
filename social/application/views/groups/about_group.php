<div class="panel panel-widget" 
    ng-if="(GroupDetails.GroupDescription != '' || GroupDetails.IsAdmin == '1') || (GroupDetails.Permission.IsActiveMember == 1 && GroupDetails.IsPublic == 1)" ng-cloak>
    <div class="panel-heading" ng-if="GroupDetails.GroupDescription != ''">
        <h3 class="panel-title text-sm"><span class="text" ng-bind="lang.g_about_caps"></span></h3>
    </div>

    <div class="panel-body" ng-if="GroupDetails.GroupDescription != '' || (GroupDetails.Permission.IsActiveMember == 1 && GroupDetails.IsPublic == 1)">
        
        <p ng-if="GroupDetails.GroupDescription != ''" ng-cloak>                
            <span ng-bind="GroupDetails.GroupDescription|limitTo:DescriptionLimit"> </span>
            <a target="_self"  ng-if="GroupDetails.GroupDescription.length > DescriptionLimit" ng-click="showMoreDesc(GroupDetails.GroupDescription.length)" ng-bind="lang.g_see_more_dots"></a> 
        </p>
        
        <div  ng-cloak ng-if="GroupDetails.Permission.IsActiveMember == 1 && GroupDetails.IsPublic == 1">
            <div class="form-group">
                <label class="control-label" ng-bind="lang.g_invite_connections_to_join"></label>
                <tags-input replace-spaces-with-dashes="false" ng-model="tagsInvited" class="inputAddMember" display-property="Name" key-property="UserGUID" add-from-autocomplete-only="true" on-tag-added="tagAddedInvited($tag)"  on-tag-removed="tagRemovedInvited($tag)" data-placeholder="Invite connections to group">
                    <label class="error-block-overlay" id="errorGroupInviteMember"></label>
                    <auto-complete source="loadTags($query)"></auto-complete>
                </tags-input>              
            </div>
            <div class="form-actions">
                <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                  <input type="submit" value="Invite" class="btn btn-primary btn-xs-size" ng-click="inviteGroupUsers('0', 'Invited')">
                </div>
            </div>
        </div> 
    </div>
</div>
<input type="hidden" id="InvitedUserGUID" value="" />

