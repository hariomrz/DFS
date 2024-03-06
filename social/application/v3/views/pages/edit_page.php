<!--Container-->
<div class="container wrapper" ng-cloak data-ng-controller="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')">
    <div class="back-link"> <a class="btn-link" href="javascript:void(0)" data-ng-click="cancel()"><i class="ficon-arrow-left"></i> <span class="text" ng-bind="::lang.back_caps"></span></a> </div>
    <div class="panel panel-info" ng-init="PageCategories('<?php echo $MainCatID;?>','SubCategory','<?php echo $auth["PageGUID"];?>')" ng-cloak>      
        <div class="panel-body p-v-mlg">
            <div class="col-sm-6 col-sm-offset-3">
                <div class="page-type"> 
                   <figure>
                       <img  ng-src="<?php echo ASSET_BASE_URL.'img/page/{{pages.Icon}}'; ?>" alt="topic-page" />
                    </figure>    
                   <h4 class="title" ng-bind="pages.CategoryName"></h4>
                </div>
                <div class="pageform">
                   <div class="alert alert-danger"><span id="commonError"></span></div>
                   <form id="crtPageBusiness" name="crtPageBusiness" class="form">
                       <div class="form-body">
                            <div class="form-group" ng-class="(!pages.Title && !crtPageBusiness.Title.$pristine) ? 'has-error' : '' ;">
                               <label class="control-label">{{::lang.Title}}<span class="help-block" ng-bind="100-pages.Title.length"></span></label>
                                <input data-ng-model="pages.Title" name="Title" type="text" maxcount="100" value="" class="form-control" id="pagetitlefieldCtrlID" placeholder="Vinfotech" />
                                <span class="block-error">Required</span>
                            </div>
                            <div class="form-group" ng-class="(!CategoryId && !crtPageBusiness.CategoryIds.$pristine) ? 'has-error' : '' ;">
                               <label class="control-label">{{::lang.Category}}</label>
                               <div class="text-field-select" >
                                   <select  name="CategoryIds"  id="CategoryIds" ng-model="CategoryId"  data-placeholder="Select Category"
                                     data-chosen=""
                                     data-disable-search="false"                                                    
                                     data-ng-options="category.CategoryID as category.Name for category in CategoryData"> 
                                     <option></option>
                                   </select>
                               </div>
                               <span class="block-error">Required</span>
                            </div>
                            <div class="form-group" ng-show="pages.PageType == '3'" ng-class="(!pages.Location && !crtPageBusiness.Location.$pristine) ? 'has-error' : '' ;">
                                <label class="control-label">{{::lang.city}}/{{::lang.state}}/{{::lang.country}}</label>
                                <div data-error="hasError" class="text-field">
                                  <input data-ng-model="pages.Location" type="text" placeholder="Select city/state/country" id="stateCtrlID" name="Location" value="">
                                </div>
                                <span class="block-error">Required</span>
                            </div>

                           <div class="row" ng-if="pages.PageType == '3'">
                               <div class="col-sm-6">
                                 <div class="form-group" ng-class="(!pages.PostalCode && !crtPageBusiness.PinCode.$pristine) ? 'has-error' : '' ;">
                                     <label class="control-label" ng-bind="::lang.pin_code"></label>
                                     <input data-ng-model="pages.PostalCode" type="text" placeholder="Enter pin code" name="PinCode" id="postcodeCtrlID" value="" class="form-control">
                                     <span class="block-error">Required</span>
                                 </div>
                               </div>
                               <div class="col-sm-6">
                                 <div class="form-group">
                                     <label class="control-label" ng-bind="::lang.phone"></label>
                                     <div class="input-group"> 
                                         <span class="input-group-addon">
                                             +91
                                         </span>
                                         <div data-error="hasError" class="text-field">
                                           <input data-ng-model="pages.Phone" class="form-control" type="text"  placeholder="" id="phoneCtrlID" value="">
                                         </div>
                                     </div>
                                 </div>
                               </div>
                           </div>
                           <div class="form-group">
                               <label class="control-label" ng-bind="::lang.website_url"></label>
                               <div data-error="hasError" class="text-field">
                                 <input data-ng-model="pages.WebsiteURL" type="text" placeholder="www.mysite.com" id="validurlCtrlID" value="">
                               </div>
                           </div>
                           <div class="form-group" ng-class="(!pages.PageURL && !crtPageBusiness.PageName.$pristine) ? 'has-error' : '' ;">
                               <label class="control-label" ng-bind="::lang.page_url"></label>         
                               <div class="input-group ">
                                   <span class="input-group-addon">
                                       <span title="<?php echo base_url(); ?>page/">
                                       <?php echo base_url(); ?>page/
                                       </span> 
                                  </span>
                                     <input class="form-control" data-ng-model="pages.PageURL" ng-trim="false" ng-change="pages.PageURL = pages.PageURL.split(' ').join('')" type="text" name="PageName" id="pagename" value="" placeholder="Vinfotech" />
                                     <span class="block-error">Required</span>
                               </div><!-- /input-group -->        
                           </div>
                           <div class="form-group" ng-class="(!pages.Description && !crtPageBusiness.Desc.$pristine) ? 'has-error' : '' ;">
                             <label class="control-label">{{::lang.about}} <span class="help-block" ng-bind="1000-pages.Description.length"></span></label>
                                <textarea data-ng-model="pages.Description" maxcount="1000" id="textareaID" placeholder="Write something..." name="Desc"
                               class="form-control"></textarea>
                                <span class="block-error">Required</span>
                           </div>
                           <div class="form-group">
                               <div class="checkbox check-primary custom-check">
                                 <input class="requestCheckBox" data-ng-model="pages.VerificationRequest" ng-true-value="'1'" ng-false-value="'0'" type="checkbox" value="" id="till-date-checkbox">
                                 <label class="m-l-10" for="till-date-checkbox" ng-bind="::lang.request_verification"></label>
                               </div>
                               <label id="errorRequestVerification" class="error-block"></label>
                           </div>
                           <input type="hidden" ng-model="pages.PageType" ng-init="pages.PageType">
                           <input type="hidden" ng-model="pages.PageGUID" ng-init="pages.PageGUID">
                       </div>
                       <div class="form-action">    
                           <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                               <a class="btn btn-default btn-xs-size" data-ng-click="cancel()" ng-bind="::lang.cancel"></a>
                               <input type="submit" value="<?php echo lang('update'); ?>" class="btn btn-primary btn-xs-size" ng-click="ValidateCreatePage()" />
                           </div>
                       </div>
                   </form>
               </div>
            </div>        
        </div> 
    </div> 
</div>
 <!--//Container