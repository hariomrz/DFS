<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Ward</span></li>
                    <li>/</li>
                    <li><span>Locality</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Bread crumb-->
<section class="main-container">
<div class="container" ng-controller="WardListCtrl" id="WardListCtrl" ng-init="initLocalityFn()">
    <!--Info row-->
    <div class="info-row row-flued">
        <h2><span id="spnh2">Locality </span> ({{totalRecord}})</h2>
        
        <div class="info-row-right rightdivbox">
                <div class="row">
                    <div class="col-sm-4">
                    </div>
                    <div class="col-sm-5">
                        <div class="row">
                            <div class="col-sm-2">
                            </div>
                            <div class="col-sm-2">
                                <label class="label">Ward</label>
                            </div>
                            <div class="col-sm-8">
                                <select  data-chosen="" ng-change="filter_locality();" ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_list" data-ng-model="filter.WID" data-disable-search="true">
                                    <option value=""></option>
                                </select> 
                            </select>
                            </div> 
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-icon right search-group open">
                            <i class="ficon-search" ng-if="!filter.Keyword" ng-click="applyFilter(0)"></i>
                            <i class="ficon-cross" ng-if="filter.Keyword" ng-click="searchFn($event, 1)" ></i>
                            <input type="text" class="form-control" ng-model="filter.Keyword" ng-keyup="searchFn($event, 0)" >
                        </div>
                    </div>
                    <!--<div class="col-sm-1">
                         <div class="btn-toolbar btn-toolbar-right" >
                            <button class="btn btn-default" ng-click="AddDetailsPopUp()" ng-show="userList.length != 0"><i class="ficon-plus"></i> <?php echo lang('Add'); ?></button>                    
                        </div> 
                    </div>-->
                </div>
            </div>       
        
    </div>
    <!--/Info row-->

    <div class="row-flued" ng-cloak>
        <div class="panel panel-secondary">
            <div class="panel-body">
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination total-items="totalRecord" 
                items-per-page="numPerPage" 
                ng-model="currentPage" 
                ng-change="getThisPage()"
                max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->
            <table class="table table-hover ips_table">
                <tr>
                    <th id="Name" class="ui-sort selected" ng-click="orderByField('L.Name')" ng-class="getOrderByClass('L.Name')">                           
                        Locality
                        <a class="sort" ng-if="getOrderByClass('L.Name')">
                            <span class="icn">
                                <i class="ficon-sort-arrow"></i>
                            </span>
                        </a>
                    </th>
                    <th id="PWName" class="ui-sort" ng-click="orderByField('W.Name')" ng-class="getOrderByClass('W.Name')">
                        Ward Name
                        <a class="sort" ng-if="getOrderByClass('W.Name')">
                            <span class="icn">
                                <i class="ficon-sort-arrow"></i>
                            </span>
                        </a>
                    </th>

                    <th id="ModuleName" class="ui-sort" ng-click="orderByField('W.Number')" ng-class="getOrderByClass('W.Number')">
                        Ward Number
                        <a class="sort" ng-if="getOrderByClass('W.Number')">
                            <span class="icn">
                                <i class="ficon-sort-arrow"></i>
                            </span>
                        </a>
                    </th>
                    <th>
                        <?php echo lang('Actions'); ?>
                    </th>
                </tr>

                <tr ng-repeat="(key, locality) in localities">
                    <td>
                        <p>{{locality.Name}} <span class="color-green" ng-if="locality.StatusID==4">(Suggested)</span></p>
                    </td>
                    <td>
                        <p data-ng-bind="locality.WName"></p>
                    </td>
                    <td>
                        <p data-ng-bind="locality.WNumber"></p>
                    </td>
                    <td> <div class="action">
                            <a class="ficon-edit mrgn-l-20" ng-click="setCurrentLocality(locality)" uib-tooltip="Edit" tooltip-append-to-body="true" data-toggle="modal" data-target="#EditLocality"></a>
                         </div>   
                        
                    </td>
                </tr>
            </table>
            <div id="ipdenieddiv"></div>
            <!-- Pagination -->
                <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
                <ul uib-pagination 
                total-items="totalRecord" 
                items-per-page="numPerPage" 
                ng-model="currentPage" 
                ng-change="getThisPage()"
                max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
            <!-- Pagination -->

            </div>
        </div>

            <!--Actions Dropdown menu-->
            <ul class="dropdown-menu smtpActiondropdown" style="left: 1191.5px; top: 297px; display: none;">                  
                <li id="ActionEdit"><a ng-click="EditDetailsPopUp()" href="javascript:void(0);"><?php echo lang('Edit'); ?></a></li>                
            </ul>
        </div>
        <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
    <style>
        .cus-class .from-subject{
                width: 50%;
                padding: 7px 0 0 19px;
                float: left;
        }

    </style>


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


    <div class="modal fade" id="EditLocality">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
                    <h4>Edit Locality</h4>
                </div>
                <div class="modal-body">
                <div class="popup-content">
                        <div class="communicate-footer row-flued">    
                        
                            <div class="form-group"> 
                                <label class="label">Ward</label>                                
                                <select  data-chosen="" ng-options="wards.WID as wards.WName+' (Ward - '+wards.WNumber+')' for wards in ward_list | filter: { WID: '!1'  }" data-ng-model="current_locality.WID" data-disable-search="true">
                                    <option value=""></option>
                                </select> 
                            </div>                         
                                   
                            <div class="form-group"> 
                                <label for="" class="label">Name (English) </label>
                                <input class="form-control" type="text" name="name" data-req-maxlen="50" id="name" placeholder="Enter Locality Name (English)"  ng-model="current_locality.Name" maxlength="50" >                                
                            </div> 
                            <div class="form-group"> 
                                <label for="" class="label">Name (Hindi)</label>
                                <input class="form-control" type="text" name="hname" data-req-maxlen="50" id="hname" placeholder="Enter Locality Name (Hindi)"  ng-model="current_locality.HindiName" maxlength="50" >                                
                            </div>
                            <div class="form-group"> 
                                <label for="" class="label">Short Name</label>
                                <input class="form-control" type="text" name="sname" data-req-maxlen="50" id="sname" placeholder="Enter Locality Name (Hindi)"  ng-model="current_locality.ShortName" maxlength="50" >                                
                            </div>
                            <div class="form-group"> 
                                <label for="" class="label">Alternate Name (English) </label>
                                <textarea class="form-control"  name="aen" id="aen" placeholder="Enter English Alternate Name" ng-model="current_locality.aen"></textarea>                                
                            </div>
                            <div class="form-group"> 
                                <label for="" class="label">Alternate Name (Hindi) </label>
                                <textarea class="form-control" name="ahn" id="ahn" placeholder="Enter Hindi Alternate Name" ng-model="current_locality.ahn"></textarea>                                
                            </div> 
                            <div class="form-group" ng-if="current_locality.StatusID==4"> 
                                <label for="" class="checkbox1 checkbox-inline">
                                    <input type="checkbox" ng-change="checkboxvalue(verifyStatus)" ng-model="verifyStatus" name="status_id" id="status_id"  class="status_id"> Verify
                                </label>                               
                            </div>                            

                            <button class="button btn EditTag" ng-disabled=" ( !current_locality.Name || !current_locality.HindiName ) " ng-click="saveLocality('EditTag');">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>


