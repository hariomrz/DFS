<section class="main-container">
    <div ng-controller="DiscoverCtrl" id="DiscoverCtrl" class="container ng-scope">
        <div >
            <div class="info-row row-flued">
                <h2 ng-if="is_category_order==0">Tag Category Management</h2>
                <h2 ng-if="is_category_order==1">Reorder Category</h2>
                <div class="info-row-right pull-right">
                    <ul ng-if="is_category_order==0" class="list-unstyled pull-right">
                        <li class="pull-left manage_tag">
                            <a ng-click="manage_category_order()" class="btn-link">Manage Category</a>
                        </li>
                        <li class="pull-left">
                            <a ng-click="clear_current_tag_category()" class="btn-link" data-toggle="modal" data-target="#AddTag">
                                <button class="btn btn-primary">+ Add New Category</button></a>
                        </li>
                    </ul>
                </div>
            </div>
    
        <div class="tab-content">
            <div ng-if="is_category_order==0" class="row-flued tab-pane fade in active" id="home">
                <div class="tag_managment_list">
                    <ul>
                        <li class="item-drag move">
                            All <span class="pull-right default-tag">Default</span>
                        </li>
                    </ul>
                    
                    <ul class="list-drag ui-sortable">
                        <li class="item-drag move" ng-repeat="(key, category) in tag_categories">
                            {{category.Name}} 
                            <span class="pull-right">
                                <div class="action">
                                    <a class="ficon-edit mrgn-l-20" ng-cloak ng-click="set_current_tag_category(category)" uib-tooltip="Edit" tooltip-append-to-body="true" data-toggle="modal" data-target="#EditTag"></a>
                                    <span>&nbsp;</span>
                                    <a class="ficon-bin" ng-cloak ng-click="delete_tag_category(category.TagCategoryID)" uib-tooltip="Delete" tooltip-append-to-body="true"  ></a>                                    
                                </div>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div ng-if="is_category_order==1" class="row-flued">
                <div class="tag_managment_list">
                    <ul dnd-list="tag_categories" class="list-drag ui-sortable drag_cursor">
                        <li class="item-drag move" ng-repeat="(key, category) in tag_categories">
                            <img class="drag_img" src="<?php echo ASSET_BASE_URL ?>admin/img/dragdrop.svg"> {{category.Name}} 
                        </li>
                    </ul>
                </div>
                <div class="pull-right">
                <a href="javascript:void(0);" class="btn-link">
                    <button ng-click="cancel_category_reorder()" class="btn btn-default">Cancel</button></a>
                <a href="javascript:void(0);" class="btn-link">
                    <button class="btn btn-primary" ng-click="change_category_order()">Update</button></a>
                </div>                

            </div>
        </div>    


        </div>
        
        
        <div class="modal fade" id="AddTag">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                 <h4>Add New Category</h4>
            </div>
            <div class="modal-body">
               <div class="popup-content">
                    <div class="communicate-footer row-flued">
                       <div class="from-subject input-for-tags">
                            <tags-input  replace-spaces-with-dashes="false" ng-model="current_tag_category.Tags" key-property="Name" display-property="Name" placeholder="Add More Tags">
                                <auto-complete source="loadTags($query)"  min-length="2" load-on-focus="false" load-on-empty="true"  max-results-to-show="4" template="tagTemplate"></auto-complete>
                            </tags-input>
                            <script type="text/ng-template" id="tagTemplate">
                                <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                                <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                                <a class="tag-remove ng-scope" ng-click="$removeTag()"></a>
                                </div>
                            </script>
                        </div>
                        <div class="from-subject"> 
                            <div class="text-field ">
                                <input  type="text" ng-model="current_tag_category.Name" class="ng-pristine ng-untouched ng-valid" placeholder="Category Name">
                            </div>
                        </div>
                        <button class="button btn AddTag" ng-disabled=" ( !current_tag_category.Name  || current_tag_category.Tags.length < 1 || !current_tag_category.Tags) " ng-click="save_tag_category('AddTag');">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
    
        <div class="modal fade" id="EditTag">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                 <h4>Edit Category</h4>
            </div>
            <div class="modal-body">
               <div class="popup-content">
                    <div class="communicate-footer row-flued">
                       <div class="from-subject">
                           <tags-input replace-spaces-with-dashes="false" ng-model="current_tag_category.Tags" key-property="Name" display-property="Name" placeholder="Add More Tags">
                                <auto-complete source="loadTags($query)" min-length="2" load-on-focus="false" load-on-empty="true" max-results-to-show="4" template="tagEditTemplate"></auto-complete>
                            </tags-input>
                           <script type="text/ng-template" id="tagEditTemplate">
                                <div ng-init="tagname = $getDisplayText();" ng-cloak class="tag-item-remove" data-toggle="tooltip" data-original-title="{{data.TooltipTitle}}" tag-tooltip  make-content-highlighted="data.Name">
                                <span class="tag-item-text" searchfieldid="advancedSearchKeyword" ng-bind-html="data.Name"></span>
                                <a class="tag-remove ng-scope" ng-click="$removeTag()"></a>
                                </div>
                            </script>
                        </div>
                        <div class="from-subject"> 
                            <div class="text-field">
                                <input type="text" ng-model="current_tag_category.Name" class="ng-pristine ng-untouched ng-valid" placeholder="Category Name">
                            </div>
                        </div> 
                        <button class="button btn EditTag" ng-disabled=" ( !current_tag_category.Name  || current_tag_category.Tags.length < 1 || !current_tag_category.Tags) " ng-click="save_tag_category('EditTag');">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
    </div>
</section>








