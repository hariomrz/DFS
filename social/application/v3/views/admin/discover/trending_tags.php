<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Discover</span></li>
                    <li>/</li>
                    <li><span>Tags</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="TrendingTagCtrl" id="TrendingTagCtrl" ng-init="initFn()">
    <!--Info row-->
    <div class="info-row row-flued">                   
        <div class="row">
            <div class="col-sm-3">
                <select ng-if="is_tag_order==0"  data-chosen="" ng-change="filter_tag();" ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_list" data-ng-model="filter.WID" >
                    <option value=""></option>
                </select> 
                <h2 ng-if="is_tag_order==1">Reorder Trending Tag</h2>
            </div> 
            <div class="col-sm-9">
                <div class="info-row-right pull-right">
                    <ul ng-if="is_tag_order==0" class="list-unstyled pull-right">
                        <li class="pull-left manage_tag">
                            <a ng-click="manage_ward_trending_tag_order()" class="btn-link">Manage Tag Order</a>
                        </li>                        
                    </ul>
                </div>
            </div>
        </div>        
    </div>
    <!--/Info row-->

    <div class="tab-content">
        <div ng-if="is_tag_order==0" class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">            
                <table class="table table-hover ips_table">
                    <tr>
                        <th id="Name" class="ui-sort" >                           
                            Trending Tag                        
                        </th>                    
                        <th>
                            <?php echo lang('Actions'); ?>
                        </th>
                    </tr>

                    <tr ng-repeat="(key, tag) in trending_tags">
                        <td>
                            <p>{{tag.Name}}</p>
                        </td>
                        <td> 
                            <div class="action">
                                <a class="ficon-globe mrgn-l-20" ng-cloak ng-click="setCurrentTag(tag)" uib-tooltip="Visibility" tooltip-append-to-body="true" data-toggle="modal" data-target="#TagVisibility"></a>
                                    <span>&nbsp;</span>
                                <a ng-if="tag.WardID != 1 || filter.WID==1 " class="ficon-bin" ng-cloak ng-click="delete_trending_tag(tag)" uib-tooltip="Remove Visibility" tooltip-append-to-body="true"  ></a>                                
                            </div>
                        </td>
                    </tr>
                </table>                
                <div id="ipdenieddiv"></div>
            </div>            
        </div>       
    </div>

        <div ng-if="is_tag_order==0 && other_tags.length>0" class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">            
                <table class="table table-hover ips_table">
                    <tr>
                        <th id="Name" class="ui-sort" >                           
                            Other Tag                        
                        </th>                    
                        <th>
                            <?php echo lang('Actions'); ?>
                        </th>
                    </tr>

                    <tr ng-repeat="(key, tag) in other_tags">
                        <td>
                            <p>{{tag.Name}}</p>
                        </td>
                        <td> 
                            <div class="action">
                                <a class="ficon-trending mrgn-l-20" ng-click="setCurrentTag(tag, 1)" uib-tooltip="Mark as Trending" tooltip-append-to-body="true" data-toggle="modal" data-target="#TagVisibility"></a>
                            </div>
                        </td>
                    </tr>
                </table>                
                <div id="ipdenieddiv"></div>
            </div>            
        </div>       
    </div>
        
        <div ng-if="is_tag_order==1" class="row-flued">
            <div class="tag_managment_list">
                <ul dnd-list="re_order_trending_tags" class="list-drag ui-sortable drag_cursor">
                    <li class="item-drag move" ng-repeat="(key, tag) in re_order_trending_tags">
                        <img class="drag_img" src="<?php echo ASSET_BASE_URL ?>admin/img/dragdrop.svg"> {{tag.Name}} 
                    </li>
                </ul>
            </div>
            <div class="pull-right">
                <a href="javascript:void(0);" class="btn-link">
                    <button ng-click="cancel_ward_trending_tag_order()" class="btn btn-default">Cancel</button></a>
                <a href="javascript:void(0);" class="btn-link">
                    <button class="btn btn-primary" ng-click="change_ward_trending_tag_order()">Update</button></a>
            </div>                

        </div>
    </div>

<div class="modal fade" id="TagVisibility" data-backdrop="static" data-keyboard="false">
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
                	<button class="button btn pull-right TagVisibility ward-visibility-save-btn" ng-click="saveWardTagVisibility('TagVisibility');">Update</button>
                </div>
            </div>
        </div>
    </div>

</div>
</section>



