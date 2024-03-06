<div id="AcitvityFilterController">

	<div class="modal fade" id="ward_visibility" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" ng-click="close_ward_visibility_modal();"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4>Ward List</h4>
                </div>
                <div class="modal-body custom-scroll scroll-md">
                	<div class="popup-content" style="padding: 0;">
	                    <ul ng-if="modal_ward_list">
	                    	<li ng-repeat="(key, item) in modal_ward_list">
	                    		<label ng-if="item.WID == 1" class="checkbox checkbox-inline checkbox-block" ng-click="select_ward(item.WID);">
                                    <input  type="checkbox" ng-checked="item.selected" ng-model="item.selected" value="{{item.WID}}" id="ward_visibilty_chk" class="ward_visibilty_checkbox">
                                    <span class="label"></span>
								</label>
								<label ng-if="item.WID != 1"  class="checkbox checkbox-inline checkbox-block" ng-click="select_ward(item.WID);">
                                    <input type="checkbox" ng-checked="item.selected" ng-model="item.selected" value="{{item.WID}}" id="ward_visibilty_chk_{{item.WID}}" class="ward_visibilty_checkbox" >
                                    <span class="label"></span>
                                </label>&nbsp;
	                    		<p ng-if="item.WID == 1" style="display: inline-block;">All</p>
	                    		<p ng-if="item.WID != 1" style="display: inline-block;">WARD ({{item.WNumber}})</p>
							</li>
	                    </ul>
	                    <ul ng-if="!modal_ward_list">
	                    	<p>No wards available</p>
	                    </ul>
                    </div>
                	<button class="button btn pull-right EditTag ward-visibility-save-btn" ng-click="save_visibility();">Update</button>
                </div>
            </div>
        </div>
    </div>

</div>
