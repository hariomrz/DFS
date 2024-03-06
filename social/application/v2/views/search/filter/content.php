<div class="secondary-fixed-nav" ng-controller="ContentSearchController as ContentSearch" ng-init="ContentSearch.searchForContent();">
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
                            <ul class="nav navbar-nav filter-nav">
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> POSTED BY 
                                        <span ng-cloak>
                                            <span ng-if="(ContentSearch.requestPayload.PostedBy == 0)"> Any One </span>
                                            <span ng-if="(ContentSearch.requestPayload.PostedBy == 1)"> You </span>
                                            <span ng-if="(ContentSearch.requestPayload.PostedBy == 4)" ng-bind=" (ContentSearch.PostedByLookedMore.length > 0) ? ContentSearch.PostedByLookedMore[0].FirstName + ' ' + ContentSearch.PostedByLookedMore[0].LastName : ''"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input ng-change="ContentSearch.checkPostedBy();" id="posted-by-anyone" type="radio" name="postedBy" ng-value="0" checked ng-model="ContentSearch.requestPayload.PostedBy">
                                                <label for="posted-by-anyone"> Anyone </label>
                                            </div>
                                        </li>
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input ng-change="ContentSearch.checkPostedBy();" id="posted-by-you" type="radio" name="postedBy" ng-value="1" ng-model="ContentSearch.requestPayload.PostedBy">
                                                <label for="posted-by-you"> You </label>
                                            </div>
                                        </li>
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input id="posted-by-looked-more" type="radio" name="postedBy" ng-value="4" ng-model="ContentSearch.requestPayload.PostedBy">
                                                <label for="posted-by-looked-more"> Look for more </label>
                                            </div>
                                        </li>
                                        <div class="add-morefilter">
                                            <div class="input-search form-control left" ng-if=" (ContentSearch.requestPayload.PostedBy == 4)">
                                                <tags-input class="form-control" ng-model="ContentSearch.PostedByLookedMore" display-property="Name" key-property="UserGUID" placeholder="Look for more" replace-spaces-with-dashes="false" add-from-autocomplete-only="true" on-tag-added="ContentSearch.checkPostedBy('add', $tag);" on-tag-removed="ContentSearch.checkPostedBy('remove', $tag);">
                                                    <auto-complete source="ContentSearch.searchUsers($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="10" template="searchUserDropdownTemplate"></auto-complete>
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
                                </li>

                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> CREATED 
                                        <span>
                                            <span ng-if="(ContentSearch.created == 'Anytime')" ng-cloak> Anytime </span>
                                            <span ng-if="(ContentSearch.created == 'createdDuringLast')" ng-bind="'Last ' + ContentSearch.makeDuringLastValue(ContentSearch.CreatedLastUpdate);"></span>
                                            <span ng-if="(ContentSearch.created == 'Between')" ng-bind="ContentSearch.requestPayload.StartDate"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li>
                                            <div class="radio">
                                                <input id="created-anytime" type="radio" name="createdAnytime" value="Anytime" ng-model="ContentSearch.created" ng-change="ContentSearch.makeDateRangeToSearch('createdDate', 'Anytime');">
                                                <label for="created-anytime">Anytime</label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="radio">
                                                <input id="during-last-created" type="radio" name="createdLastUpdate" value="createdDuringLast" ng-model="ContentSearch.created" ng-change="ContentSearch.makeDateRangeToSearch('createdDate', 'LastUpdate');">
                                                <label ng-if="ContentSearch.created != 'createdDuringLast'" for="during-last-created">During Last</label>
                                                <label ng-if="ContentSearch.created == 'createdDuringLast'" for="during-last-created" ng-bind="'During Last ' + ContentSearch.makeDuringLastValue(ContentSearch.CreatedLastUpdate);"></label>
                                            </div>  
                                            <div class="slider-range" ng-if="ContentSearch.created == 'createdDuringLast'">                                          
                                                <slider 
                                                    slider-id="tooltipSlider"
                                                    ng-model="ContentSearch.CreatedLastUpdate" 
                                                    on-stop-slide="ContentSearch.searchForContent();"
                                                    min="ContentSearch.sliderOptions.min" 
                                                    step="ContentSearch.sliderOptions.step" 
                                                    max="ContentSearch.sliderOptions.max" 
                                                    value="ContentSearch.sliderOptions.value"
                                                    formatter="ContentSearch.rangeSliderFormatter('createdDate', value)">
                                                </slider>
                                            </div>                                         
                                        </li>

                                        <li>
                                            <div class="radio">
                                                <input id="created-between" type="radio" name="createdBetween" value="Between" ng-model="ContentSearch.created" ng-change="ContentSearch.makeDateRangeToSearch('createdDate', 'Between');">
                                                <label for="created-between">Between</label>
                                            </div>
                                            <div class="row" ng-if="ContentSearch.created == 'Between'"  ng-cloak>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="control-label">From Date</label>
                                                        <div data-error="hasError" class="text-field">
                                                            <input 
                                                                ng-change="ContentSearch.searchForContent();"
                                                                ng-model="ContentSearch.requestPayload.StartDate" 
                                                                type="text" 
                                                                readonly 
                                                                placeholder="yy-mm-dd" 
                                                                range-datepicker
                                                                pickerType="from" 
                                                                id="createBetweenFrom" 
                                                                fromid="createBetweenFrom" 
                                                                toid="createBetweenTo"/>
                                                            <label id="errorFromDate" class="error-block-overlay"></label> 
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="control-label">To Date</label>
                                                        <div data-error="hasError" class="text-field">
                                                            <input 
                                                                ng-change="ContentSearch.searchForContent();" 
                                                                ng-model="ContentSearch.requestPayload.EndDate" 
                                                                type="text" 
                                                                readonly 
                                                                placeholder="yy-mm-dd"
                                                                range-datepicker
                                                                pickerType="to" 
                                                                id="createBetweenTo" 
                                                                fromid="createBetweenFrom" 
                                                                toid="createBetweenTo"/>
                                                            <label id="errorToDate" class="error-block-overlay"></label> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> UPDATED 
                                        <span>
                                            <span ng-if="(ContentSearch.updated == 'updatedAnytime')" ng-cloak> Anytime </span>
                                            <span ng-if="(ContentSearch.updated == 'updatedDuringLast')" ng-bind="'Last ' + ContentSearch.makeDuringLastValue(ContentSearch.ModifiedLastUpdate);"></span>
                                            <span ng-if="(ContentSearch.updated == 'updatedBetween')" ng-bind="ContentSearch.requestPayload.UpdatedStartDate"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li>
                                            <div class="radio">
                                                <input id="update1" type="radio" name="update" value="updatedAnytime" ng-model="ContentSearch.updated" ng-change="ContentSearch.makeDateRangeToSearch('modifiedDate', 'Anytime');">
                                                <label for="update1">Anytime</label>
                                            </div> 
                                        </li>
                                        <li>
                                            <div class="radio">
                                                <input id="updated-during-last" type="radio" name="update" value="updatedDuringLast" ng-model="ContentSearch.updated" ng-change="ContentSearch.makeDateRangeToSearch('modifiedDate', 'LastUpdate');">
                                                <label ng-if="ContentSearch.updated != 'updatedDuringLast'" for="updated-during-last">During Last</label>
                                                <label ng-if="ContentSearch.updated == 'updatedDuringLast'" for="updated-during-last" ng-bind="'During Last ' + ContentSearch.makeDuringLastValue(ContentSearch.ModifiedLastUpdate);"></label>
                                            </div>
                                            <div class="slider-range" ng-if="ContentSearch.updated == 'updatedDuringLast'">                                          
                                                <slider 
                                                    ng-model="ContentSearch.ModifiedLastUpdate" 
                                                    on-stop-slide="ContentSearch.searchForContent();"
                                                    min="ContentSearch.sliderOptions.min" 
                                                    max="ContentSearch.sliderOptions.max" 
                                                    step="ContentSearch.sliderOptions.step" 
                                                    value="ContentSearch.sliderOptions.value"
                                                    formatter="ContentSearch.rangeSliderFormatter('modifiedDate', value)">
                                                </slider>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="radio">
                                                <input id="update3" type="radio" name="update" value="updatedBetween" ng-model="ContentSearch.updated" ng-change="ContentSearch.makeDateRangeToSearch('modifiedDate', 'Between');">
                                                <label for="update3">Between</label>
                                            </div>
                                            <div class="row" ng-if="ContentSearch.updated == 'updatedBetween'" ng-cloak>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="control-label">From Date</label>
                                                        <div data-error="hasError" class="text-field date-field">
                                                            <input 
                                                                ng-change="ContentSearch.searchForContent();"
                                                                ng-model="ContentSearch.requestPayload.UpdatedStartDate" 
                                                                type="text" 
                                                                readonly 
                                                                placeholder="yy-mm-dd" 
                                                                range-datepicker 
                                                                pickerType="from"
                                                                id="updatedBetweenFrom" 
                                                                fromid="updatedBetweenFrom" 
                                                                toid="updatedBetweenTo"/>
                                                            <label id="errorFromDate" class="error-block-overlay"></label>
                                                            <label class="iconDate" for="updatedBetweenFrom">
                                                                <i class="ficon-calc"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label class="control-label">To Date</label>
                                                        <div data-error="hasError" class="text-field date-field">
                                                            <input 
                                                                ng-change="ContentSearch.searchForContent();" 
                                                                ng-model="ContentSearch.requestPayload.UpdatedEndDate"
                                                                type="text" 
                                                                readonly 
                                                                placeholder="yy-mm-dd" 
                                                                range-datepicker 
                                                                pickerType="to"
                                                                id="updatedBetweenTo" 
                                                                fromid="updatedBetweenFrom" 
                                                                toid="updatedBetweenTo"/>
                                                            <label id="errorToDate" class="error-block-overlay"></label>
                                                            <label class="iconDate" for="updatedBetweenTo">
                                                                <i class="ficon-calc"></i>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> HAS ATTACHMENT ? 
                                        <span ng-cloak>
                                            <span ng-if="(ContentSearch.requestPayload.IsMediaExists == 1)"> Yes </span>
                                            <span ng-if="(ContentSearch.requestPayload.IsMediaExists == 0)"> No </span>
                                            <span ng-if="(ContentSearch.requestPayload.IsMediaExists == 2)"> Can't say </span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input ng-change="ContentSearch.searchForContent();" id="has-attachement-yes" type="radio" name="hasAttachement" ng-value="1" ng-model="ContentSearch.requestPayload.IsMediaExists">
                                                <label for="has-attachement-yes"> Yes </label>
                                            </div>
                                        </li>
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input ng-change="ContentSearch.searchForContent();" id="has-attachement-no" type="radio" name="hasAttachement" ng-value="0" ng-model="ContentSearch.requestPayload.IsMediaExists">
                                                <label for="has-attachement-no"> No </label>
                                            </div>
                                        </li>
                                        <li ng-cloak>
                                            <div class="radio">
                                                <input ng-change="ContentSearch.searchForContent();" id="has-attachement-cant-say" type="radio" name="hasAttachement" ng-value="2" ng-model="ContentSearch.requestPayload.IsMediaExists">
                                                <label for="has-attachement-cant-say"> Can’t say </label>
                                            </div>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> POST TYPE 
                                        <span>
                                            <span ng-if="ContentSearch.searchFor.posts" ng-cloak> Posts</span>
                                            <span ng-if="ContentSearch.searchFor.comments" ng-bind="( ContentSearch.searchFor.posts && ContentSearch.searchFor.comments ) ? ', Comments ' : ' Comments' "></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li ng-cloak>
                                            <label class="checkbox">
                                                <input type="checkbox" name="searchForPosts" ng-change="ContentSearch.updateToSearchFor();" ng-model="ContentSearch.searchFor.posts" ng-checked="ContentSearch.searchFor.posts">
                                                <span class="label">Posts</span>
                                            </label>
                                        </li>
                                        <li ng-cloak>
                                            <label class="checkbox">
                                                <input type="checkbox" name="searchForComments" ng-change="ContentSearch.updateToSearchFor();" ng-model="ContentSearch.searchFor.comments" ng-checked="ContentSearch.searchFor.comments">
                                                <span class="label">Comments</span>
                                            </label>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> INCLUDE 
                                        <span>
                                            <span ng-if=" (ContentSearch.requestPayload.IncludeArchive === 1)" ng-cloak> Archived Post</span>
                                            <span ng-if=" (ContentSearch.requestPayload.IncludeAttachment === 1)" ng-bind="( ( ContentSearch.requestPayload.IncludeArchive === 1 ) && ( ContentSearch.requestPayload.IncludeAttachment === 1 ) ) ? ', Name of Files ' : ' Name of Files' "></span>
                                            <span ng-if="0 && (ContentSearch.requestPayload.IncludeUserAndGroup === 1)" ng-bind="( ( ( ContentSearch.requestPayload.IncludeArchive === 1 ) || ( ContentSearch.requestPayload.IncludeAttachment === 1 ) ) && ( ContentSearch.requestPayload.IncludeUserAndGroup === 1 ) ) ? ', Name of User/Group/Event/Page/Wiki Category ' : ' Name of User / Group / Event / Page / Wiki Category' "></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li ng-cloak>
                                            <label class="checkbox">
                                                <input type="checkbox" name="includeArchivedPost" ng-change="ContentSearch.searchForContent();" ng-model="ContentSearch.requestPayload.IncludeArchive" ng-true-value="1" ng-false-value="0" ng-checked=" (ContentSearch.requestPayload.IncludeArchive === 1)">
                                                <span class="label">Archived Post</span>
                                            </label>
                                        </li>
                                        <li ng-cloak>
                                            <label class="checkbox">
                                                <input type="checkbox" name="includeNameOfFiles" ng-change="ContentSearch.searchForContent();" ng-model="ContentSearch.requestPayload.IncludeAttachment" ng-true-value="1" ng-false-value="0" ng-checked=" (ContentSearch.requestPayload.IncludeAttachment === 1)">
                                                <span class="label">Name of Files</span>
                                            </label>
                                        </li>
                                        <li ng-cloak ng-if="0">
                                            <label class="checkbox">
                                                <input type="checkbox" name="IncludeUserAndGroup" ng-change="ContentSearch.searchForContent();" ng-model="ContentSearch.requestPayload.IncludeUserAndGroup" ng-true-value="1" ng-false-value="0" ng-checked=" (ContentSearch.requestPayload.IncludeUserAndGroup === 1)">
                                                <span class="label">Name of User/Group/Event/Page/Category</span>
                                            </label>
                                        </li>
                                    </ul>
                                </li>

                                <li ng-if="!ContentSearch.isDefaultFilter()" ng-cloak="">
                                    <div class="reset-button" >
                                        <button class="btn btn-default" ng-click="ContentSearch.ResetFilter()">Reset</button>
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
<input type="hidden" id="sortby" value="" />
<input type="hidden" id="CurrentPage" value="Content" />