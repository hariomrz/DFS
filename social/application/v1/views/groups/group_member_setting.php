
            <div class="col-sm-12 animate-show" ng-show="MemberView=='Setting'">
                <div class="panel panel-default">
                    <h3 class="panel-title border-bottom"> 
                        <a target="_self" class="back-button" ng-click="ToggleMemberPage('Listing')" href="javascript:void(0)">
                            <span class="icon">
                                <svg width="18px" height="16px"  class="svg-icons">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnBackspace'}}"></use>
                                </svg>
                            </span> 
                            {{::lang.back_to_members}}
                        </a>
                    </h3>
                    <div class="panel-body">
                        <div class="padding-inner border-bottom">
                            <div class="form-group"  ng-cloak>
                                <div>
                                    <label ng-bind="::lang.add_members"></label>
                                </div>
                                <div class="row member-add">
                                    <div class="col-xs-10 col-sm-11 inputAddMember">
                                        <tags-input ng-model="tagsto3" class="inputAddMember" add-from-autocomplete-only="true" display-property="name"  placeholder="Select User" replace-spaces-with-dashes="false" on-tag-added="tagAddedNonMembers($tag)" on-tag-removed="tagRemovedNonMembers($tag)">
                                                <auto-complete source="loadNonMembers($query)" max-results-to-show="1000" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="userlistTemplate"></auto-complete>
                                        </tags-input>
                                        <script type="text/ng-template" id="userlistTemplate">
                                            <a target="_self" class="m-conv-list-thmb">
                                            <figure>
                                                <img   err-Name="{{data.name}}" class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{data.ProfilePicture}}">  
                                            </figure>
                                            </a>
                                            <a target="_self" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                             <span><i class="icon-lock" ng-if="data.ModuleID==1" ng-class="{'icon-n-closed':data.Privacy==0,'icon-n-group-secret':data.Privacy==2,'icon-n-global':data.Privacy==1}"></i></span>
                                        </script>
                                    </div>
                                    <div class="col-xs-2 col-sm-1 ">
                                        <button class="btn btn-primary" ng-click="inviteGroupUsersNew('1','Invited')"><?php echo lang('group_member_add');?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
        

    <!--  SUggested Members -->
    <div class="padding-inner border-bottom" ng-if="suggested_members.length>0">
        <div class="form-group">
            <label ng-bind="::lang.suggested_addition"></label>
            <ul class="suggested-addition">

                <li ng-cloak ng-repeat="member in suggested_members" id="member_{{member.UserGUID}}" >
                    <figure data-toggle="dropdown" ng-click="set_sugested_user(member.UserGUID)">
                        <span class="overlay loadbusinesscard" entitytype="user" entityguid="{{member.UserGUID}}"><span>+</span> </span>
                            <img title="member.Name"  ng-if="member.ProfilePicture!='' "  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{member.ProfilePicture}}"> 
                            <span ng-if="member.ProfilePicture==''" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(member.Name)"></span></span>
                    </figure>

                 
                    <ul class="dropdown-menu setting-dropdown" data-type="stopPropagation">
                        <li><a target="_self" class="text-center">{{member.Name}}</a></li>
                        <li class="divider"></li>

                        <li>
                            <label class="checkbox">
                                <input type="checkbox"  ng-init="setModelData('IsAdmin',$index,GroupDetails.param.a)" ng-true-value="'1'" ng-false-value="'0'"  name="IsAdmin" ng-click="togglePermission('IsAdmin',IsAdmin[$index])" ng-model="IsAdmin[$index]" >
                                <span class="label" ng-bind="::lang.admin"></span>
                            </label>
                        </li>


                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-init='IsExpert[$index]=convertToString(GroupDetails.param.ge)' ng-true-value="'1'" ng-false-value="'0'" name="IsExpert" ng-click="togglePermission('IsExpert',IsExpert[$index])" ng-model="IsExpert[$index]" >                                
                                <span class="label" ng-bind="::lang.group_expert"></span>
                            </label>
                        </li>

                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-init="setModelData('CanPost',$index,GroupDetails.param.p)" ng-true-value="'1'" ng-false-value="'0'"  name="CanPost" ng-click="togglePermission('CanPost',CanPost[$index])" ng-model="CanPost[$index]" >
                                <span class="label" ng-bind="::lang.post"></span>
                            </label>
                        </li>

                        <li>
                            <label class="checkbox">
                                <input type="checkbox" ng-init='CanComment[$index]=convertToString(GroupDetails.param.c)' ng-true-value="'1'" ng-false-value="'0'"  name="CanComment" ng-click="togglePermission('CanComment',CanComment[$index])" ng-model="CanComment[$index]">
                                <span class="label" ng-bind="::lang.comment"></span>
                            </label>
                        </li>


                        <li ng-show='showKB==true'>
                            <label class="checkbox">
                                <input type="checkbox" ng-init='CanCreateKnowledgeBase[$index]=convertToString(GroupDetails.param.kb)' ng-true-value="'1'" ng-false-value="'0'" name="CanCreateKnowledgeBase" ng-click="togglePermission('CanCreateKnowledgeBase',CanCreateKnowledgeBase[$index])" ng-model="CanCreateKnowledgeBase[$index]" >
                                <span class="label" ng-bind="::lang.knowledge_base"></span>
                            </label>
                        </li>

                        <li class="list-footer">
                            <button class="btn btn-default btn-xs" ng-click="inviteGroupUsersNew(1,'Suggestion')">{{::lang.add}}</button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- End Suggested Friends -->

        <div class="padding-inner"  ng-cloak>
            <div class="clearfix">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-9 col-sm-8 ">
                            <label for="" class="m-t-5" ng-bind="::lang.all_members"></label>
                        </div>
                        <div class="col-md-3 col-sm-4">
                            <div class="input-group form-group">
                                <input type="text" name="Search" placeholder="Search" my-enter='searchMember(SearchKey,"Enter")' ng-keyup="searchMember(SearchKey)" ng-model="SearchKey" class="form-control" on-focus>
                                <span class="input-group-addon addon-white">
                                     <i class="ficon-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="table-responsive">
                            <table class="member-listing-table table">
                                <thead>
                                    <tr>
                                        <th ng-click="MemberOrderBy='Name'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'Name',ReverseSort);">
                                           {{::lang.member_name}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                        <th ng-click="MemberOrderBy='Admin'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'Admin',ReverseSort);">
                                           {{::lang.admin}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                        <th ng-click="MemberOrderBy='Expert'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'Expert',ReverseSort);">
                                           {{::lang.group_expert}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                            <span class="icon" tootip data-toggle="tooltip" data-placement="top" title="Subject Expert">
                                                <svg class="svg-icons" width="16px" height="16px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconInfotip'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                        <th ng-click="MemberOrderBy='CanPost'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'CanPost',ReverseSort);">
                                             {{::lang.post}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                        <th ng-click="MemberOrderBy='CanComment'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'CanComment',ReverseSort);">
                                            {{::lang.comment}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                        <th ng-show='showKB==true' ng-click="MemberOrderBy='KnowledgeBase'; ReverseSort = !ReverseSort; LoadMoreAllMembers(1,'KnowledgeBase',ReverseSort);">
                                            {{::lang.knowledge_base}}
                                            <span class="icon">
                                                <svg class="svg-icons" width="10px" height="10px">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconShort'}}"></use>
                                                </svg>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
              

                 <tbody class="table table-noborderd table-hover table-middle table-sort" ng-if="GroupDetails.Permission.IsAdmin == true ||  GroupDetails.Permission.IsCreator == true">
                    <tr class="default-row">
                        <td>
                             <div class="table-media">
                                <div class="table-media-body table-media-middle">
                                    <h5 class="table-media-heading"><a target="_self" class="text-black" ng-bind="::lang.default"></a></h5>
                                </div>
                            </div>
                        </td>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-checked='GroupDetails.param.a==1' ng-model="GroupDetails.param.a" ng-click="save_default_setting();">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td> 
                            <label class="checkbox">
                                <input type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-checked='GroupDetails.param.ge==1' ng-model="GroupDetails.param.ge" ng-click="save_default_setting();">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td> 
                            <label class="checkbox">
                                <input type="checkbox" ng-true-value="'1'" ng-false-value="'0'"  ng-checked='GroupDetails.param.p==1' ng-model="GroupDetails.param.p"  ng-click="save_default_setting();">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td> 
                            <label class="checkbox">
                                <input type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-checked='GroupDetails.param.c==1' ng-model="GroupDetails.param.c"  ng-click="save_default_setting();" >
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td ng-show='showKB==true' >    
                            <label class="checkbox">
                                <input type="checkbox" ng-true-value="'1'" ng-false-value="'0'" ng-checked='GroupDetails.param.kb==1' ng-model="GroupDetails.param.kb" ng-click="save_default_setting();" >
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                    </tr>
                </tbody>

                    <tbody>
                <tr ng-if='TotalRecordsAllmembers<1'>
                <td colspan="6">
                     <p class="m-t-15" ng-bind="::lang.no_record"></p>
                </td>
                 </tr>
    
                    <tr  ng-repeat="list in listObj = ListAllMembers" >
                        <td>
                            <div class="table-media">
                                <div class="table-media-left table-media-middle">
                                    <figure class="object-36">
                                            <a target="_self" ng-attr-entitytype="{{list.ModuleID==1?'group':'user'}}" entityguid="{{list.ModuleEntityGUID}}" ng-if="list.ModuleID==3 || list.ModuleID==1" class="loadbusinesscard" href="{{'<?php echo site_url() ?>'+list.ProfileURL}}">                        
                                            <img   ng-if="list.ProfilePicture!=='user_default.jpg' && list.ProfilePicture!==''" class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}"> 
                                            <span ng-if="list.ProfilePicture=='' || list.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(list.FirstName+' '+list.LastName)"></span></span>
                                            </a>
                                    </figure>
                                </div>
                                <div class="table-media-body table-media-middle">
                                    <h5 class="table-media-heading">
                                        <a target="_self" class="a-link loadbusinesscard" ng-attr-entitytype="{{list.ModuleID==1?'group':'user'}}" entityguid="{{list.ModuleEntityGUID}}" href="{{'<?php echo site_url() ?>'+list.ProfileURL}}" >{{list.FirstName}} {{list.LastName}}</a>
                                    </h5>
                                    <span class="text-sm-off"  ng-if="list.ModuleRoleID == 4" ng-bind="::lang.g_creator"></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox" value="5" ng-disabled='list.ModuleRoleID == 4' ng-checked="list.ModuleRoleID==5" ng-model="ModuleRoleID[$index]" ng-click="set_member_permission('ModuleRoleID',ModuleRoleID[$index],list.ModuleEntityID)">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox" value="1" ng-disabled='GroupDetails.Permission.IsCreator != true && list.ModuleRoleID == 4' ng-checked="list.IsExpert==1" ng-model="IsExpert[$index]" ng-click="set_member_permission('IsExpert',IsExpert[$index],list.ModuleEntityID)" >
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox" value="1"  ng-disabled='list.ModuleRoleID == 4' ng-checked="list.CanPostOnWall==1" ng-model="CanPostOnWall[$index]" ng-click="set_member_permission('CanPostOnWall',CanPostOnWall[$index],list.ModuleEntityID)">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td>
                            <label class="checkbox">
                                <input type="checkbox" value="1"  ng-disabled='list.ModuleRoleID == 4' ng-checked="list.CanComment==1" ng-model="CanComment[$index]" ng-click="set_member_permission('CanComment',CanComment[$index],list.ModuleEntityID)">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                        <td ng-show='showKB==true'>
                            <label class="checkbox">
                                <input type="checkbox" value="1"  ng-disabled='list.ModuleRoleID == 4' ng-checked="list.CanCreateKnowledgeBase==1" ng-model="CanCreateKnowledgeBase[$index]" ng-click="set_member_permission('CanCreateKnowledgeBase',CanCreateKnowledgeBase[$index],list.ModuleEntityID)">
                                <span class="label">&nbsp;</span>
                            </label>
                        </td>
                    </tr>
                </tbody>
                            </table>

                            <nav aria-label="Page navigation" class="pagination-nav" ng-if='TotalRecordsAllmembers>0'>
                                <div class="showing-content pull-left">
                                    <?php echo lang('showing');?> {{ StartPageLimit() }}  to {{ EndPageLimit() }} of {{TotalRecordsAllmembers}} {{::lang.members}}
                                </div>

                                 <pagination  class="pagination pagination-sm pull-right"  page="1" on-select-page="LoadMoreAllMembers(page)"  total-items="TotalRecordsAllmembers" items-per-page="AllMemberLimit"></pagination>

                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>


                    </div>
                </div>
            </div>
        

