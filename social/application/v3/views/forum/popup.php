<!--All POPUP's Included-->
    <!-- Manage Feature Modal -->
    <div class="modal fade" id="manageFeature">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title"><?php echo lang('manage_feature');?></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center semi-bold"><?php echo lang('select_upto_3_to_feature');?> </p>
                    <ul class="list-drag">
                        <li class="item-drag" ng-repeat="forum_category in forum_categories">
                            <span class="icon">
                                <label class="checkbox">
                                    <input type="checkbox" ng-disabled="disable_checkbox(forum_category.ForumCategoryID);" ng-click="make_featured(forum_category.ForumCategoryID);" name="forum_category.IsFeatured" ng-checked="(forum_category.IsFeatured==1) ? true : false ;" />
                                    <span class="label"></span>
                                </label>
                            </span>
                            <span class="text" ng-bind="forum_category.Name"></span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" ng-click="set_forum_categories()" class="btn btn-primary pull-right"><?php echo lang('done');?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Re-order Forum Modal -->
    <div class="modal fade" id="reOrderForum"> 
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title"><?php echo lang('reorder_forum');?></h4>
                </div>
                <div class="modal-body">  
                    <div class="default-scroll scrollbar">              
                    <ul dnd-list="forums_reorder" class="list-drag">
                        <li class="item-drag move" ng-repeat="forum_order in forums_reorder">
                            <span class="icon">                                
                                <svg height="18px" width="18px" class="svg-icons no-hover">
                                    <use xlink:href="{{SiteURL+'assets/img/sprite.svg#iconDrage'}}"></use>
                                </svg>
                            </span>
                            <span class="text" ng-bind="forum_order.Name"></span>
                        </li>
                    </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" ng-click="change_forum_order()" class="btn btn-primary pull-right"><?php echo lang('done');?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Re-order Category Modal -->
    <div class="modal fade" id="reOrderCategory"> 
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title" ng-if="current_category_id == 0"><?php echo lang('re_order_categories');?></h4>
                    <h4 class="modal-title" ng-if="current_category_id != 0"><?php echo lang('re_order_sub_categories');?></h4>
                </div>
                <div class="modal-body block-hidden">                    
                    <ul dnd-list="forum_categories" class="list-drag">
                        <li ng-repeat="forum_category in forum_categories" class="item-drag move">
                            <span class="icon">                                
                                <svg height="18px" width="18px" class="svg-icons no-hover">
                                    <use xlink:href="{{SiteURL+'assets/img/sprite.svg#iconDrage'}}"></use>
                                </svg>
                            </span>
                            <span class="text" ng-bind="forum_category.Name"></span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" ng-click="change_category_order();" class="btn btn-primary pull-right"><?php echo lang('done');?></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Forum Modal -->
    <div class="modal fade" id="addForum">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title" ng-bind="addEditForumPopupTitle"><?php echo lang('add_forum');?></h4>
                </div>
                <form method="post" id="createUpdateForum" ng-submit="CreateUpdateForum();">
                    <div class="modal-body">
                            <div class="form-group">
                                <label>Forum Name</label>
                                <div class="text-field">
                                    <div data-error="hasError" class="text-field">
                                        <input ng-keyup="prefill_url_forum(CreateUpdate.Name)" ng-model="CreateUpdate.Name" data-requiredmessage="Required" data-msglocation="errorForumName" data-mandatory="true" data-controltype="general" type="text" placeholder="Enter forum name">
                                        <label id="errorForumName" class="error-block-overlay"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo lang('description');?></label>
                                <div data-error="hasError" class="textarea-field">
                                    <textarea maxcount="200" maxlength="200" data-req-maxlen="200" ng-model="CreateUpdate.Description" data-requiredmessage="Required" data-msglocation="errorForumDesc" data-mandatory="true" data-controltype="general"  placeholder="<?php echo lang('enter_description');?>"></textarea>
                                    <label id="errorForumDesc" class="error-block-overlay"></label>
                                    <span class="char-counter" ng-bind="(200-CreateUpdate.Description.length)"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo lang('url');?></label>
                                <div class="text-field">
                                    <div data-error="hasError" class="text-field">
                                        <input maxlength="40" ng-model="CreateUpdate.URL" data-requiredmessage="Required" data-msglocation="errorForumURL" data-mandatory="true" data-controltype="general"  type="text" placeholder="<?php echo lang('enter_url');?>">
                                        <label id="errorForumURL" class="error-block-overlay"></label>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button id="submitForum" type="submit" onclick="return checkstatus('createUpdateForum')" class="btn btn-primary pull-right"><?php echo lang('finish');?> </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategory">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title" ng-if="!CreateUpdateCat.ForumCategoryID"><?php echo lang('add_category');?></h4>
                    <h4 class="modal-title" ng-if="CreateUpdateCat.ForumCategoryID"><?php echo lang('edit_category');?></h4>
                </div>
                <form id="AddCatForm" ng-submit="CreateUpdateCategory();" ng-cloak>
                <div class="modal-body">
                    <div ng-hide="category">    
                        <div class="form-group">
                            <aside class="profile-pic set-profile-pic browse-camra">
                                <figure class="user-wall-thumb">
                                    <img id="forumcatprofilepic" ng-src="{{ImageServerPath+'upload/profile/220x220/'+CreateUpdateCat.ProfilePicture}}" err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" alt="User" title="User" class="img-circle">
                                </figure>
                                <div class="dropdown thumb-dropdown">
                                    <a class="edit-profilepic dropdown-toggle" data-toggle="dropdown">
                                        <i class="ficon-camera"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a  ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                                                <span class="space-icon"><i class="ficon-upload"></i></span><?php echo lang('upload_new');?>
                                            </a>
                                            <div class="hiddendiv">
                                                <input type="file" name="" id="changeThumb">
                                            </div>
                                        </li>
                                        <li ng-if="CreateUpdateCat.ProfilePicture"><a ng-click="remove_category_picture();"><span class="space-icon"><i class="ficon-cross"></i></span><?php echo lang('remove');?></a></li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('select_forum');?></label>
                            <div class="text-field-select">
                                <select ng-init="CreateUpdateCat.ForumID=current_forum_id" ng-model="CreateUpdateCat.ForumID" ng-options="key as value for (key , value) in forum_names" data-chosen="" data-disable-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('category_name');?></label>
                            <div class="text-field">
                                <div data-error="hasError" class="text-field">
                                    <input ng-keyup="(!CreateUpdateCat.ForumCategoryID) ? prefill_url_cat(CreateUpdateCat.Name) : '' ;" data-requiredmessage="Required" data-msglocation="errorCatName" data-mandatory="true" data-controltype="general" ng-model="CreateUpdateCat.Name" type="text" placeholder="<?php echo lang('enter_category');?>">
                                    <label id="errorCatName" class="error-block-overlay"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('description');?></label>
                            <div data-error="hasError" class="textarea-field">
                                <textarea maxcount="200" maxlength="200" data-req-maxlen="200" data-requiredmessage="Required" data-msglocation="errorCatDesc" data-mandatory="true" data-controltype="general" ng-model="CreateUpdateCat.Description"  placeholder="<?php echo lang('enter_description');?>"></textarea>
                                <label id="errorCatDesc" class="error-block-overlay"></label>
                                <span class="char-counter" ng-bind="(200-CreateUpdateCat.Description.length)"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('url');?></label>
                            <div class="text-field">
                                <div data-error="hasError" class="text-field">
                                    <input maxlength="40" data-requiredmessage="Required" data-msglocation="errorCatURL" data-mandatory="true" data-controltype="general" ng-model="CreateUpdateCat.URL" type="text" placeholder="<?php echo lang('enter_url');?>">
                                    <label id="errorCatURL" class="error-block-overlay"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div ng-show="category">
                        <div class="form-group">
                            <label class="text-off"><?php echo lang('category_privacy');?></label>
                            <div class="privat-lisitng">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <div class="radio">
                                            <input id="open" ng-model="CreateUpdateCat.Visibility" type="radio" name="category" value="1">
                                            <label for="open"> <i class="icon-n-global"></i> <?php echo lang('open');?></label>
                                            <p><?php echo lang('category_visible_info');?></p>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="radio">
                                            <input id="secret" ng-model="CreateUpdateCat.Visibility" type="radio" name="category" value="2">
                                            <label for="secret"><i class="icon-n-group-secret"></i> <?php echo lang('restricted');?></label>
                                            <p><?php echo lang('category_restricted_visible_info');?></p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="text-off"><?php echo lang('category_content');?></label>
                            <ul class="treeview">
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" ng-model="CreateUpdateCat.IsDiscussionAllowed" ng-true-value="'1'" ng-false-value="'2'" >
                                        <span class="label"><?php echo lang('can_hold_discussions');?>
                                            <p class="text-off"><?php echo lang('can_hold_discussions_info');?></p>
                                        </span>
                                    </label>
                                    <ul>
                                        <li>
                                            <label class="checkbox">
                                                <input type="checkbox" ng-disabled="CreateUpdateCat.IsDiscussionAllowed=='2'" ng-model="CreateUpdateCat.CanAllMemberPost"  ng-true-value="'1'" ng-false-value="'2'" >
                                                <span class="label"><?php echo lang('all_members_can_post');?>
                                                <p class="text-off"><?php echo lang('all_members_can_post_info');?></p>
                                                </span>
                                            </label>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">           
                    <div class="pull-right">                                 
                        <button type="button" class="btn btn-default" ng-click="category = !category">
                        {{category?'PREVIOUS':'NEXT'}}
                        </button>
                        <button ng-show="category" type="submit" ng-click="category = !category" onclick="return checkstatus('AddCatForm')" class="btn btn-primary"><?php echo lang('done');?> </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
        <!-- Add Sub Category Modal -->
    <div class="modal fade" id="addSubCategory">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title" ng-show="SubCat.Name==''"><?php echo lang('add_sub_category');?></h4>
                    <h4 class="modal-title" ng-show="SubCat.Name!=''"><?php echo lang('edit_subcategory');?></h4>
                </div>
                <div class="modal-body">
                    <form ng-hide="subcategory" ng-cloak>
                        <div class="form-group">
                            <aside class="profile-pic set-profile-pic browse-camra">
                                <figure class="user-wall-thumb">
                                    <img id="SubCatMediaGUID" ng-src="{{ImageServerPath+'upload/profile/220x220/'+SubCat.ProfilePicture}}" err-SRC="{{ImageServerPath+'upload/profile/220x220/category_default.png'}}" alt="User" title="User" class="img-circle">
                                </figure>
                                <div class="dropdown thumb-dropdown">
                                    <a class="edit-profilepic dropdown-toggle" data-toggle="dropdown">
                                        <i class="ficon-camera"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a  ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                                                <span class="space-icon"><i class="ficon-upload"></i></span><?php echo lang('upload_new');?>
                                            </a>
                                        </li>
                                        <li ng-if="SubCat.ProfilePicture">
                                            <a ng-click="remove_subcategory_picture();">
                                                <span class="space-icon"><i class="ficon-cross"></i></span><?php echo lang('remove');?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </aside>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('parent_category');?> </label>
                            <div class="text-field-select">
                                <select ng-model="SubCat.ParentCategoryID" ng-options="key as value for (key , value) in forum_categories_list" data-chosen="" data-disable-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('sub_category_name');?></label>
                            <div class="text-field">
                                <div data-error="hasError" class="text-field">
                                    <input ng-keyup="prefill_url_scat(SubCat.Name)" ng-model="SubCat.Name" type="text" placeholder="Enter Subcategory">
                                    <label class="error-block-overlay"></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('description');?></label>
                            <div data-error="hasError" class="textarea-field">
                                <textarea maxcount="200" maxlength="200" data-req-maxlen="200" ng-model="SubCat.Description" maxcount="200" placeholder="<?php echo lang('enter_description');?>"></textarea>
                                <span class="char-counter" ng-bind="(200-SubCat.Description.length)"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php echo lang('url');?></label>
                            <div class="text-field">
                                <div data-error="hasError" class="text-field">
                                    <input maxlength="40" ng-model="SubCat.URL" type="text" placeholder="<?php echo lang('enter_url');?>">
                                    <label class="error-block-overlay"></label>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form ng-show="subcategory" ng-cloak>
                        <div class="form-group">
                            <label class="text-off"><?php echo lang('sub_category_privacy');?></label>
                            <div class="privat-lisitng">
                                <ul class="list-group">
                                    <li class="list-group-item" >
                                        <div class="radio" ng-click="SubCat.Visibility=1">
                                            <input id="open" type="radio"   name="category" value="1" ng-model="SubCat.Visibility" >
                                            <label for="open"> <i class="icon-n-global"></i> <?php echo lang('open');?></label>
                                            <p><?php echo lang('category_visible_info');?></p>
                                        </div>
                                    </li>
                                    <li class="list-group-item" >
                                        <div class="radio" ng-click="SubCat.Visibility=2">
                                            <input id="secret" type="radio" value="2" name="category" ng-model="SubCat.Visibility" >
                                            <label for="secret"><i class="icon-n-group-secret"></i> <?php echo lang('restricted');?> </label>
                                            <p><?php echo lang('category_restricted_visible_info');?></p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="text-off"><?php echo lang('sub_category_content');?></label>
                            <ul class="treeview">
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" ng-model="SubCat.CanAllMemberPost" ng-true-value="'1'" ng-false-value="'2'">
                                        <span class="label"><?php echo lang('all_members_can_post');?>
                                            <p class="text-off"><?php echo lang('all_members_can_post_info');?></p>
                                        </span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">           
                    <div class="pull-right">                                 
                        <button type="submit" class="btn btn-default" ng-click="subcategory = !subcategory">
                        {{subcategory?'PREVIOUS':'NEXT'}}
                        </button>
                        <button ng-show="subcategory" type="submit" ng-click="subcategory = !subcategory; CreateUpdateSubCategory();" class="btn btn-primary"><?php echo lang('done');?> </button>
                    </div>
                </div>
            </div>
        </div>
    </div>