<!--Container-->
<div class="container wrapper" data-ng-controller="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')" ng-cloak> 
  <div class="back-link"> <a class="btn-link" href="javascript:void(0)" data-ng-click="cancel()"><i class="ficon-arrow-left"></i> <span class="text" ng-bind="lang.back_caps"></span></a> </div>
    <div class="panel panel-info">   
        <div class="panel-body p-v-mlg">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div class="page-type"> 
                        <figure>
                            <img ng-src="<?php echo ASSET_BASE_URL.'img/page/'.$CategoryDetails["Icon"]; ?>" alt="topic-page" />
                         </figure>    
                         <h4 class="title"><?php echo $CategoryDetails["Name"];?></h4>
                    </div>                
                    <div class="pageform">                  
                         <form id="crtPageBusiness" name="crtPageBusiness" class="form">
                            <div class="form-body">

                                <div class="form-group" ng-class="(!pages.Title && !crtPageBusiness.Title.$pristine) ? 'has-error' : '' ;">
                                  <label class="control-label">{{::lang.Title}}<span class="help-block" ng-bind="100-pages.Title.length"></span></label>
                                  <input data-ng-model="pages.Title" name="Title" type="text" maxcount="100" class="form-control" value="" id="pagetitlefieldCtrlID" placeholder="Title"/>
                                  <span class="block-error">Required</span>
                                </div>
                                <div class="form-group" ng-class="(!CategoryId && !crtPageBusiness.CategoryIds.$pristine) ? 'has-error' : '' ;">
                                  <label class="control-label">{{::lang.Category}}</label>
                                  <div class="text-field-select" ng-init="PageCategories('<?php echo $CategoryID;?>','SubCategory')">
                                  <select  name="CategoryIds" data-requiredmessage="Required"  id="CategoryIds" ng-model="CategoryId"  data-placeholder="Select Category"
                                  data-chosen=""
                                  data-disable-search="false"                                                    
                                  data-ng-options="category.Name for category in CategoryData track by category.CategoryID" class="form-control">
                                    <option></option>
                                  </select>
                                </div>
                                <span class="block-error">Required</span>
                               </div>
                                <?php if($CategoryID == 3){?>
                                  <div class="form-group" ng-class="(!pages.Location && !crtPageBusiness.Location.$pristine) ? 'has-error' : '' ;">
                                    <label class="control-label">{{::lang.city}}/{{::lang.state}}/{{::lang.country}}</label>
                                    <input data-ng-model="pages.Location" type="text" placeholder="Select city/state/country" id="stateCtrlID" name="Location" value="" class="form-control">  
                                    <span class="block-error">Required</span>
                                  </div>

                                  <div class="row">
                                    <div class="col-sm-6">
                                      <div class="form-group" ng-class="(!pages.PostalCode && !crtPageBusiness.PinCode.$pristine) ? 'has-error' : '' ;">
                                        <label class="control-label" ng-bind="::lang.pin_code"></label>
                                        <input data-ng-model="pages.PostalCode" name="PinCode" type="text"  data-mandatory="true" placeholder="Enter pin code" id="postcodeCtrlID" value="" data-controltype="number" data-msglocation="errorPostcode" data-requiredmessage="Required" maxlength="6" class="form-control">
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
                                          <input data-ng-model="pages.Phone" class="form-control" type="text"  placeholder="" id="phoneCtrlID" value="" data-controltype="phonenumber" data-msglocation="errorPhone" data-requiredmessage="Required" message="Valid Number Require">
                                        </div> 
                                      </div>
                                    </div>
                                  </div>
                                <?php }?>
                                <div class="form-group">
                                  <label class="control-label" ng-bind="::lang.website_url"></label>
                                  <input data-ng-model="pages.WebsiteURL" type="text"  placeholder="www.mysite.com" id="validurlCtrlID" value="" data-controltype="validurl" data-mandatory="false" data-msglocation="errorValidurl" data-requiredmessage="Required" class="form-control">
                                </div>
                                <div class="form-group" ng-class="(!pages.PageURL && !crtPageBusiness.PageName.$pristine) ? 'has-error' : '' ;">
                                  <label class="control-label" ng-bind="::lang.page_url"></label>         
                                  <div class="input-group ">
                                    <span class="input-group-addon">
                                        <span title="<?php echo base_url(); ?>page/">
                                            <?php echo base_url(); ?>page/
                                        </span> 
                                    </span>
                                    <input class="form-control" data-ng-model="pages.PageURL" type="text" data-requiredmessage="Required" name="PageName" data-msglocation="errorPagename" data-mandatory="true" data-controltype="pagename" id="pagename" value="" placeholder="Vinfotech"  />
                                   <span class="block-error">Required</span>
                                   </div><!-- /input-group -->
                                </div>
                                <div class="form-group" ng-class="(!pages.Description && !crtPageBusiness.Desc.$pristine) ? 'has-error' : '' ;">
                                  <label class="control-label">{{::lang.about}} <span class="help-block" ng-bind="1000-pages.Description.length"></span></label>
                                  <textarea data-ng-model="pages.Description" maxcount="1000" id="textareaID" placeholder="Write something..." name="Desc" class="form-control"></textarea>
                                  <span class="block-error">Required</span>
                                </div>
                                <div class="form-group">
                                  <div class="checkbox check-primary custom-check">
                                  <input class="requestCheckBox form-control" data-ng-model="pages.VerificationRequest" type="checkbox" value="" id="till-date-checkbox" data-requiredmessage="Required">
                                  <label for="till-date-checkbox" ng-bind="::lang.request_verification"></label>
                                 </div>
                                 <label id="errorRequestVerification" class="error-block"></label>
                                </div>
                            </div>
                            <div class="form-action">    
                                <div class="btn-toolbar right btn-toolbar-xs-right btn-toolbar-xs">
                                    <a class="btn btn-default btn-xs-size" data-ng-click="cancel()" ng-bind="::lang.cancel"></a>
                                    <button type="submit" id="CreatePage" class="btn btn-primary btn-xs-size" ng-click="ValidateCreatePage();">{{::lang.create}} <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span></button>                                    
                                  </div>
                                <input type="hidden" ng-model="pages.PageType" ng-init="pages.PageType = '<?php echo $CategoryID;?>'">                                
                            </div>
                        </form>
                    </div>
                  </div>
            </div>
        </div>
    </div>
</div>
<!--//Container -->
<script type="text/javascript">
  window.onload = function(){ setTimeout(function(){ $('#CategoryIds').trigger('chosen:updated'); },1000); }
</script>