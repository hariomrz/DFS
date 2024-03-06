<div ng-if="showGroupPopup" ng-cloak ng-init="GroupCategories()">  
    <!-- Create Group Modal -->
    <div class="modal fade" id="createGroup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4 class="modal-title" id="myModalLabel" ng-bind="FormName ? 'Edit Group' : 'Create Group'"></h4>
                </div>
                <div class="modal-body">
                    <div class="no-scrollbar">
                        <form id="formGroup">
                            <div class="form-group">
                                <label>
                                    <?php echo lang('group_name');?>
                                </label>
                                <div class="text-field">
                                    <div data-error="hasError" class="text-field">
                                        <uix-input type="text" name="GroupName" id="group_name" value="" placeholder="Group Name" data-controltype="general" data-mandatory="true" data-msglocation="errorGroupName" data-requiredmessage="Required" data-ng-model="EditGroupName" data-req-minlen="2" maxlength="50" data-req-maxlen="50"></uix-input>
                                        <label id="errorGroupName" class="error-block-overlay"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <?php echo lang('Category');?>
                                </label>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="text-field-select" data-error="hasError">
                                            <select name="CategoryIds" id="CategoryIds" data-mandatory="true" data-msglocation="errorCategories" ng-model="EditCategory" data-placeholder="Select Category" data-controltype="general" data-chosen="" data-disable-search="false" data-ng-options="category.Name for category in GroupCategories|orderBy:'Name' track by category.CategoryID" data-requiredmessage="Required" ng-change="getSubCategories(EditCategory.CategoryID);">
                                                <option value=""></option>
                                            </select>
                                            <label class="error-block-overlay" id="errorCategories"></label>
                                        </div>
                                    </div>
                                    <!--<div class="col-sm-6" ng-show="SubCategories.length>0">
                                        <div class="text-field-select" data-error="hasError">
                                            <select name="SubCategory" id="SubCategory" ng-model="SubCategory" data-placeholder="Select Sub Category" data-chosen="" data-disable-search="false" data-ng-options="category.Name for category in SubCategories track by category.CategoryID">
                                                <option value=""></option>
                                            </select>
                                            <label class="error-block-overlay" id="errorCategories"></label>
                                        </div>
                                    </div>-->
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <?php echo lang('group_description');?>
                                </label>
                                <div data-error="hasError" class="textarea-field">
                                    <textarea maxcount="400" rows="5" maxlength="400" uix-textarea data-mandatory="true" class="form-control" data-controltype="generalTextArea" id="group_description" data-msglocation="errorGroupDesc" name="GroupDescription" placeholder="Description about the group" tabindex="2" data-requiredmessage="Required" data-ng-model="EditGroupDescription"></textarea>
                                    <label class="error-block-overlay" id="errorGroupDesc"></label>
                                </div>
                            </div>
                            <div class="form-group" ng-init="GetAllowedGroupTypes()">
                                <label>
                                    <?php echo lang('group_content');?>
                                </label>
                                <div class="privat-lisitng">
                                    <ul class="list-group">
                                        <li class="col-sm-4" ng-repeat="Content in ContentTypes" ng-cloak>
                                            <label class="checkbox">
                                                <input type="checkbox" name="AllowedGroupTypes[]" value="{{Content.Value}}" ng-checked="checkAllowedType(Content.Value)">
                                                <span class="label"> {{Content.Label}}</span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <?php echo lang('group_privacy');?>
                                </label>
                                <div class="privat-lisitng">
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <div class="radio">
                                                <input type="radio" checked="checked" value="1" name="IsPublic" ng-model="EditIsPublic" id="openGroup">
                                                <label for="openGroup"> <i class="icon-n-global"></i>
                                                    <?php echo lang('open');?>
                                                </label>
                                                <p>
                                                    <?php echo lang('open_group_help');?>
                                                </p>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="radio">
                                                <input type="radio" value="0" name="IsPublic" ng-model="EditIsPublic" id="closeGroup">
                                                <label for="closeGroup"> <i class="icon-n-closed"></i>
                                                    <?php echo lang('close');?> </label>
                                                <p>
                                                    <?php echo lang('close_group_help');?>
                                                </p>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="radio">
                                                <input id="secret" type="radio" name="IsPublic" ng-model="EditIsPublic" value="2">
                                                <label for="secret"><i class="icon-n-group-secret"></i>
                                                    <?php echo lang('secret');?> </label>
                                                <p>
                                                    <?php echo lang('secret_group_help');?>
                                                </p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" name="GroupGUID" value="{{EditGroupGUID}}">
                            <input type="hidden" name="GroupType" value="{{EditGroupType}}">
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary pull-right" ng-click="FormSubmit()" ng-bind="FormButtonName ? 'UPDATE' : 'CREATE'"></button>
                </div>
            </div>
        </div>
    </div>
</div>
