<div class="secondary-fixed-nav">
    <div class="secondary-nav">
        <div class="container">
            <div class="row nav-row">
                <div class="col-sm-12 main-filter-nav"> 
                    <nav class="navbar navbar-default navbar-static">
                        <div class="navbar-header visible-xs">
                             <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#filterNav"> 
                                    <span class="icon"><i class="ficon-filter"></i></span>
                                </button>
                        </div>
                        <div class="collapse navbar-collapse" id="filterNav">
                            <ul class="nav navbar-nav filter-nav" ng-init="getFilterDetails();">
                                <li class="dropdown">
                                    <a class="" data-toggle="dropdown" role="button"> Posted By 
                                        <span>
                                            <span ng-cloak ng-if="photo_posted_by==''">Anyone</span>
                                            <span ng-cloak ng-if="photo_posted_bsizey!==''" ng-bind="photo_posted_by">
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left">
                                        <div class="collapse in" id="posted">
                                            <div class="panel-body">
                                                <div class="filters-section">
                                                    <ul class="list-group">
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updatePostedByPhoto('Anyone')" id="RadioAnyone" type="radio" name="posted" checked="checked" />
                                                                <label for="RadioAnyone">Anyone</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updatePostedByPhoto('Friend')" id="RadioFriend" type="radio" name="posted" value="1" />
                                                                <label for="RadioFriend">Friend</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updatePostedByPhoto('My Follows')" id="RadioMyFollow" type="radio" name="posted" value="1" />
                                                                <label for="RadioMyFollow">My Follows</label>
                                                            </div>
                                                        </li>
                                                        <div class="add-morefilter">
                                                            <div class="input-search form-control left">
                                                                <tags-input class="form-control" ng-model="PostedByUsers" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="callPhotoList();" on-tag-added="callPhotoList();">
                                                                    <auto-complete source="searchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="searchUserDropdownTemplate"></auto-complete>
                                                                </tags-input>
                                                                <script type="text/ng-template" id="searchUserDropdownTemplate">
                                                                    <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                                                </script>
                                                                <div class="input-group-btn">
                                                                    <button type="button" class="btn">
                                                                        <span class="icon">
                                                                            <i class="ficon-search f-lg"></i>
                                                                        </span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a class="" data-toggle="dropdown" role="button"> Who's Tagged 
                                        <span>
                                            <span ng-if="photo_tag_by==''">Anyone</span>
                                            <span ng-if="photo_tag_by!==''" ng-bind="photo_tag_by"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left">
                                        <div class="collapse in" id="tagged">
                                            <div class="panel-body">
                                                <div class="filters-section">
                                                    <ul class="list-group">
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updateTagByPhoto('Me')" id="tagMe" type="radio" name="tag">
                                                                <label for="tagMe">Me</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updateTagByPhoto('My Friends')" id="tagMyFriends" type="radio" name="tag">
                                                                <label for="tagMyFriends">My Friends</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="radio">
                                                                <input ng-click="updateTagByPhoto('My Follows')" id="tagMyFollows" type="radio" name="tag">
                                                                <label for="tagMyFollows">My Follows</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <div class="add-morefilter">
                                                        <div class="input-search form-control left">
                                                            <tags-input class="form-control" ng-model="TaggedInUsers" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-removed="callPhotoList();" on-tag-added="callPhotoList();">
                                                                <auto-complete source="searchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="searchUserDropdownTemplate2"></auto-complete>
                                                            </tags-input>
                                                            <script type="text/ng-template" id="searchUserDropdownTemplate2">
                                                                <a ng-bind-html="$highlight($getDisplayText())" class="d-user-name"></a>
                                                            </script>
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn">
                                                                    <span class="icon">
                                                                        <i class="ficon-search f-lg"></i>
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Sort By 
                                        <span>
                                            <span ng-cloak ng-if="sort_by_label==''">Network</span>
                                            <span ng-cloak ng-if="sort_by_label!==''" ng-bind="sort_by_label"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="active-with-icon dropdown-menu dropdown-menu-left">
                                        <li ng-if="sort_by_label2!=='NameAsc'" ng-class="(sort_by_label2 == 'NameDesc') ? 'active' : ''"><a ng-click="changeSortBy('NameAsc');">By Name</a></li>
                                        <li ng-if="sort_by_label2=='NameAsc'" ng-class="(sort_by_label2 == 'NameAsc') ? 'active' : ''"><a ng-click="changeSortBy('NameDesc');">By Name</a></li>
                                        <!-- <li><a ng-click="changeSortBy('Size')">By Size</a></li> -->
                                        <li ng-class="(sort_by_label2 == 'Recent Updated') ? 'active' : ''"><a ng-click="changeSortBy('Recent Updated')">Recent Updated</a></li>
                                        <li ng-class="(sort_by_label2 == 'Network' || sort_by_label2 == '') ? 'active' : ''"><a ng-click="changeSortBy('Network')">Network</a></li>
                                        <!-- <li><a ng-click="changeSortBy('Followers')">By No. of Followers</a></li> -->
                                    </ul>
                                </li>
                                
                                
                                <li  ng-cloak="" ng-if="!isDefaultFilterPhotoSearch()">
                                    <div class="reset-button" >
                                        <button class="btn btn-default" ng-click="ResetFilterPhotoSearch()">Reset</button>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                    </nav> 

                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="sortby" value="Network" />
<input type="hidden" id="CurrentPage" value="Photo" />