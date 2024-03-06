<?php
$default_value = '';
?>
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Tools</span></li>
                    <li>/</li>
                    <li><span>Manage Popups</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">

<div  class="container" ng-controller="PopupController as PopupCtrl" ng-init="PopupCtrl.popupList()" id="PopupController">

    <div class="page-heading">
        <div class="row">
            <div class="col-xs-3">
                <h2 class="page-title">Manage Popups</h2>
            </div>
            <div class="col-xs-9">
                <div class="page-actions row-flued">
                    <div class="row ">
                        <div class="col-xs-10 col-xs-offset-2">
                            <div class="row gutter-5">
                                <div class="col-sm-9">
                                </div>
                                <div class="col-sm-3">
                                    <a class="btn btn-default btn-block" href="<?php echo base_url('admin/popup/create'); ?>" data-toggle="modal" ng-click="PopupCtrl.SetPopupDetail(popupList,'add',createPopupForm)">
                                        <span class="icn"><svg height="12" width="12" class="svg-icons">
                                            <use xlink:href="../assets/admin/img/sprite.svg#plusIco"></use></svg></span>
                                        <span class="text">Create Popup</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-secondary">
        <div class="panel-body">
        <!-- Pagination -->
        <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
        <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="PopupCtrl.numPages" class="pagination-sm" boundary-links="false" ng-change="PopupCtrl.popupList()" ></ul>
        <table class="table table-hover" id="userlist_table">
            <thead>
                        <tr>
                            <th class="text-center">Name</th>
                            <th class="text-center">Created By</th>
                            <th class="text-center">Created On</th>
                            <!-- <th>Published On</th> -->
                            <th class="text-center">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tr ng-repeat="popupList in PopupCtrl.popupData" >
                        <td ng-bind="popupList.PopupTitle" class="max-width"></td>
                        <td ng-bind="popupList.CreatorName"></td>
                        <td ng-bind="popupList.CreatedDate"></td>
                        <!-- <td ng-bind="popupList.PublishedDate"></td> -->
                        <td ng-if="(popupList.Status == '2')" >Active</td>
                        <td ng-if="(popupList.Status != '2')" >Inactive</td>
                        <td><a class="user-action" onClick="userActiondropdown()" ng-click="PopupCtrl.SetPopupDetail(popupList,'edit');"><i class="icon-setting">&nbsp;</i></a>                           
                        </td>
                    </tr>
                </table>
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="PopupCtrl.numPages" class="pagination-sm" boundary-links="false" ng-change="PopupCtrl.popupList()" ></ul>       
                <!-- Pagination -->
        </div>
    </div>
    <ul class="action-dropdown userActiondropdown">
        <li><a data-toggle="modal"  data-target="" ng-click="PopupCtrl.deletePopup()">Delete</a></li>
        <li>
        <a ng-click="PopupCtrl.toggleActive('Inactive');" ng-if="(PopupCtrl.createPopup.Status==2)">Make Inactive</a>
        <a ng-click="PopupCtrl.toggleActive('Active');" ng-if="(PopupCtrl.createPopup.Status!=2)">Make Active</a>
        </li>
        <!-- <li><a data-toggle="modal" data-target="#confirm">Delete</a></li> -->
    </ul>

    <span id="result_message" class="result_message"><?php echo "No popup created till now. You may create a new one by clicking 'Create Popup' button above."; ?></span>
            <!--/Actions Dropdown menu-->


    <!-- Confirm Box -->
    <div class="popup confirme-popup animated" id="confirmeCommissionPopup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center">Are you sure you want to delete?</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeCommissionPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="updateStatus()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div> 

    <!-- Modal Start --> 
    <div class="modal fade" tabindex="-1" role="dialog" id="addPopup">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">


                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <svg height="16px" width="16px" class="svg-icons">
                            <use xlink:href="../assets/admin/img/sprite.svg#closeIco"></use>
                            </svg>
                        </span>
                    </button>
                    <h4 class="modal-title" ng-if="(PopupCtrl.PopupAction=='add')">Add Popup</h4>
                    <h4 class="modal-title" ng-if="(PopupCtrl.PopupAction=='edit')">Edit Popup</h4>
                </div>
                <form name="createPopupForm" ng-submit="PopupCtrl.savePopup(createPopupForm);" novalidate>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">Popup<span class="required">*</span></label> 
                            <div data-error="hasError" ng-cloak ng-class="{'hasError' : (createPopupForm.$submitted && createPopupForm.Popup.$error.required) }">

                                <input type="text" class="form-control" name="Popup" ng-model="PopupCtrl.createPopup.PopupName" required>

                                <label class="error-block-overlay" ng-if="(createPopupForm.$submitted && createPopupForm.Popup.$error.required)">Please enter Popup Name.</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Description</label>         
                            <div data-error="hasError" ng-cloak ng-class="{'hasError' : (createPopupForm.$submitted && createPopupForm.Description.$error.maxlength) }">

                                <textarea class="form-control" id="Description" name="Description" ng-model="PopupCtrl.createPopup.Description" ng-maxlength="500"></textarea>

                                <label class="error-block-overlay" ng-if="(createPopupForm.$submitted && createPopupForm.Description.$error.maxlength)">Popup Description must be entered in 500 characters limit.</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">
                                    <label class="control-label">Status</label>
                                </div>
                                <div class="col-xs-10">
                                    <div class="radio-list">
                                        <label class="radio radio-inline">
                                            <input type="radio" ng-value="2" ng-checked="PopupCtrl.createPopup.Status" name="status" ng-model="PopupCtrl.createPopup.Status">
                                            <span class="label">Active</span>
                                        </label>
                                        <label class="radio radio-inline">
                                            <input type="radio" ng-value="1" name="status" ng-model="PopupCtrl.createPopup.Status">
                                            <span class="label">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" ng-click="PopupCtrl.popupList()">CANCEL</button>
                        <button type="submit" class="btn btn-primary" ng-disabled="PopupCtrl.isCreatePopupProcessing" ng-if="(PopupCtrl.PopupAction=='add')">ADD</button>
                        <button type="submit" class="btn btn-primary" ng-disabled="PopupCtrl.isCreatePopupProcessing" ng-if="(PopupCtrl.PopupAction=='edit')">EDIT</button>
                    </div>
                </form>


            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="editMessage">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <svg height="16px" width="16px" class="svg-icons">
                            <use xlink:href="../assets/admin/img/sprite.svg#closeIco"></use>
                            </svg>
                        </span>
                    </button>
                    <h4 class="modal-title">Edit Popup</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Popup<span class="required">*</span></label>                  
                        <input type="text" value="Administration" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>                  
                        <textarea class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-2">
                                <label class="control-label">Status</label>
                            </div>
                            <div class="col-xs-10">
                                <div class="radio-list">
                                    <label class="radio radio-inline">
                                        <input type="radio" value="1" checked name="status">
                                        <span class="label">Active</span>
                                    </label>
                                    <label class="radio radio-inline">
                                        <input type="radio" value="1" name="status">
                                        <span class="label">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-primary">UPDATE</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="confirm">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <svg height="16px" width="16px" class="svg-icons">
                            <use xlink:href="../assets/admin/img/sprite.svg#closeIco"></use>
                            </svg>
                        </span>
                    </button>
                    <h4 class="modal-title">Confirmation</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary">YES</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>        
    <!-- / Modal -->
</div>
</section>
