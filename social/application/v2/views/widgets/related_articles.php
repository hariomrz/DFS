<div ng-if="SettingsData.m38=='1'" class="modal fade" id="addRelatedArticles" tabindex="-1">
    <div class="modal-dialog modal-xlg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true"><i class="icon-close"></i></span> </button>
                <h4 class="modal-title" id="myModalLabel" ng-bind="lang.w_add_related_articles"></h4>
            </div>
            <div class="modal-body articleModal"> 
                <div class="selected-articles">
                    <div class="slected-from">
                        <h4 ng-bind="local_article_data.PostTitle"></h4>
                        <span>{{::lang.w_posted_in}} <a target="_self" ng-bind="local_article_data.EntityName"></a></span>
                    </div>
                    <div class="selected-filter" ng-if="related_articles.length > 0">
                        <label><span ng-bind="related_articles.length"></span> {{::lang.w_selected}}</label>
                        <div class=" selected-list bx-arrow-top bx-slider-fluid">
                             <ul class="wiki-listing wiki-fav" id="wikislider">
                                    <li ng-repeat="article in related_articles" repeat-done="slider_init(); update_article_status();">
                                        <div class="wiki-listing-content">
                                            <div class="wiki-content">
                                                <div class="wiki-lable" ng-bind="article.EntityName"></div>
                                                <h2><a target="_self" ng-href="{{article.ActivityURL}}" ng-bind="article.PostTitle"></a></h2>
                                            </div>
                                        </div>
                                        <a target="_self" class="list-close" ng-click="select_article(article.ActivityID)">
                                            <span class="icon">
                                               <svg height="16px" width="16px" class="svg-icons">
                                                <use xlink:href="{{SiteURL+'assets/img/sprite.svg#closeIcon'}}"></use>
                                               </svg>
                                           </span>
                                        </a>
                                    </li>
                              </ul>
                        </div>
                    </div>
                </div>
                <div class="articles-filter">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{::lang.w_show_from}}</label>
                                <div>
                                    <tags-input on-tag-added="add_tag_article($tag)" on-tag-removed="remove_tag_article($tag)" ng-model="categorySelect" display-property="Name" add-from-autocomplete-only="true" placeholder="Search by groups, forums, category" replace-spaces-with-dashes="false">
                                        <auto-complete source="loadCategorylist($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4" template="groupTemplate"></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="groupTemplate">
                                        <a target="_self" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                    </script>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>{{::lang.by}}</label>
                                <div class="group-contacts">
                                    <div class="input-group groups-tag">
                                        <div class="form-control">
                                            <tags-input ng-model="PostedByLookedMore" on-tag-added="filter_article()" on-tag-removed="filter_article()" display-property="Name" add-from-autocomplete-only="true" placeholder="Search by user" replace-spaces-with-dashes="false">
                                                <auto-complete source="loadSearchUsersArticle($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="memberTemplate"></auto-complete>
                                            </tags-input>
                                            <script type="text/ng-template" id="memberTemplate">
                                                <a target="_self" class="m-conv-list-thmb">
                                                    <figure>
                                                    <img err-Name="{{data.Name}}" ng-src="<?php echo IMAGE_SERVER_PATH ?>upload/profile/220x220/{{data.ProfilePicture}}" >
                                                    </figure>
                                                </a>
                                                <a target="_self" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                            </script>
                                        </div> 
                                        <span class="input-group-addon">
                                            <span class="icon">
                                                <svg class="svg-icons no-hover" height="16px" width="16px">
                                                    <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnAccountMale'}}"></use>
                                                </svg>
                                            </span> 
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>{{::lang.w_tags}}</label>
                                <div class="group-contacts">
                                    <div class="input-group groups-tag">
                                        <div class="form-control">
                                            <tags-input min-length="2" ng-model="search_tags" on-tag-added="filter_article()" on-tag-removed="filter_article()" display-property="Name" add-from-autocomplete-only="true" placeholder="Search by tags" replace-spaces-with-dashes="false">
                                                <auto-complete source="loadSearchTagsArticle($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="4" template="byTags"></auto-complete>
                                            </tags-input>
                                            <script type="text/ng-template" id="byTags">
                                                <a target="_self" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                            </script>
                                        </div>
                                        <span class="input-group-addon">
                                           <span class="icon">
                                                <svg class="svg-icons no-hover" height="16px" width="16px">
                                                    <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnTag'}}"></use>
                                                </svg>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="articles-listing">
                    <div class="wiki-suggested-listing">
                        <div class="row">
                            <ul class="wiki-listing" ng-show="article_list.length">
                                <li class="col-sm-6 col-md-4 col-lg-4" repeat-done="update_article_status()" ng-repeat="article in article_list" ng-cloak>
                                    <div class="wiki-listing-content">
                                        <div ng-class="(article.IsChecked=='1') ? 'selected' : '' ;" ng-click="select_article(article.ActivityID)" class="wiki-header" style="background-image: url({{'<?php echo IMAGE_SERVER_PATH ?>upload/wall/220x220/'+article.Album[0].Media[0].ImageName}});">
                                            <div class="wiki-lable" ng-bind="article.EntityName"></div>
                                                <i class="wiki-selected"></i>
                                            <div class="default-wiki-banner" ng-if="!article.Album[0].Media[0].ImageName" ng-cloak>
                                                <span class="icon">
                                                    <svg height="72px" width="72px" class="svg-icons no-hover">
                                                        <use xlink:href="{{SiteURL+'assets/img/sprite.svg#icnKnowledge'}}"></use>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="wiki-content">
                                            <h2><a target="_self" ng-href="{{article.ActivityURL}}" ng-bind="article.PostTitle"></a></h2>
                                            <p ng-bind-html="slice_string(article.PostContent,150)"></p>
                                        </div>
                                        <div class="wiki-footer">
                                            <div class="wiki-activites">
                                                <ul class="list-activites user-detail">
                                                    <li>
                                                        <a target="_self" class="user-thmb"><img err-Name="{{article.UserName}}" ng-src="{{article.ImageServerPath + 'upload/profile/220x220/' + article.ProfilePicture}}" class="img-circle"></a>

                                                        <!-- <span ng-if="article.ProfilePicture=='' || article.ProfilePicture=='user_default.jpg'" class="default-thumb ng-scope"><span ng-bind="getDefaultImgPlaceholder(article.UserName)" class="ng-binding"></span></span> -->

                                                        <a target="_self" ng-bind="article.UserName"></a>
                                                        <span class="time" ng-bind="date_format(article.CreatedDate)"></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="col-sm-12" ng-if="article_list.length==0 && blankScreen" ng-cloak>
                                <div class="blank-block">
                                    <i class="view-icon">
                                        <span class="icon">
                                            <svg height="100px" width="100px" class="svg-icons no-hover">
                                                <use  xlink:href="{{SiteURL+'assets/img/sprite.svg#icnKnowledge'}}"></use>
                                            </svg>
                                        </span>
                                    </i>
                                    <h2 ng-bind="lang.w_no_data_found"></h2>
                                    <p ng-bind="lang.w_sorry_article_not_found"></p>
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="loader text-lg" ng-if="viewLoader" ng-cloak style="display: block;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-default" ng-click="dismiss_related_activity_popup()" ng-bind="lang.cancel"></button>
                    <button type="submit" class="btn btn-primary" ng-click="add_related_activity();" ng-bind="lang.done"></button>
                </div>
            </div>
        </div>
    </div>
</div>