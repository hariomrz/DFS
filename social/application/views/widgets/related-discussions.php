<div class="panel panel-widget visible-lg visible-md" ng-init="getSimilarDiscussions(5);" ng-cloak ng-if="similar_discussions.length > 0">    
    <div class="panel-heading">        
        <h3 class="panel-title" ng-bind="lang.w_related_discussions"></h3>
    </div>
    <div class="panel-body">
        <ul class="list-text" ng-cloak>
            <li ng-repeat="discussion in similar_discussions" ng-if="discussion.PostTitle!='' ">
                <a target="_self" href="{{discussion.ActivityLink}}" ng-bind-html="discussion.PostTitle"></a>
            </li>
        </ul>
    </div>
</div>