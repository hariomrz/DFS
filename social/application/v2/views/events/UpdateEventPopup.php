<!-- Create Group Modal -->
<div class="modal fade" id="updateEvent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="UpdateEventClose"><span aria-hidden="true"><i class="icon-close"></i></span></button>
          <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_event');?></h4>
        </div>        
        <form  id="formupdateEvent" ng-init="get_event_categories();">
          <div class="modal-body scrollbar">
          <div class="default-scroll scrollbar">
            <div class="form-group">
              <label><?php echo lang('event_title');?></label>
              <div class="text-field">
                <div data-error="hasError" class="text-field">
                  <input tabindex="1" type="text" maxlength="100" data-requiredmessage="Required" data-msglocation="errorEditNamefield" data-mandatory="true"  data-controltype="" id="EditNamefield" placeholder="<?php echo lang('event_title');?>" uix-input="" data-ng-model="eventUpdate.Title">
                  <label id="errorEditNamefield" class="error-block-overlay"></label>
                </div>
              </div>
            </div>
            <div class="form-group" >
              <label><?php echo lang('Category');?></label>
              <div class="text-field-select" data-error="hasError">
                <select ng-selected="1"  tabindex="2" name="CategoryIds" id="CategoryIds" data-mandatory="true" data-msglocation="errorCategories"  data-placeholder="Select Category" data-controltype="general" data-requiredmessage="Required" chosen  option="listData[0].CatObj"  data-disable-search="true" ng-options="Cat.CategoryID as Cat.Name for Cat in CategoryData[0].CatObj"  ng-show="CatObj.length>0" data-ng-model="eventUpdate.CategoryID">
                  <option value=""></option>
                </select>
                <label class="error-block-overlay" id="errorCategories"></label>
              </div>
            </div>
            <div class="form-group">
              <label><?php echo lang('event_detail');?></label>
              <div data-error="hasError" class="textarea-field">
                <textarea tabindex="3" maxcount="400" id="textareaDID" uix-textarea data-ng-model="eventUpdate.Description" placeholder="<?php echo lang('event_detail');?>"></textarea>
                <span id="spn2textareaID"></span> </div>
            </div>
            <div class="form-group">
              <label><?php echo lang('event_url');?></label>
              <div data-error="hasError" class="text-field">
                <div data-error="hasError" class="text-field">
                  <input tabindex="4" type="text" data-msglocation="errorEditValidurl" data-controltype="validurl" value="" id="EditvalidurlCtrlID" placeholder="e.g. https://www.exampleurl.com" uix-input="" data-ng-model="eventUpdate.URL">
                  <label id="errorEditValidurl" class="error-block-overlay"></label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-lg-12 col-xs-12 xs-gutter">
                  <label><?php echo lang('venue');?></label>
                  <div class="text-field">
                    <input tabindex="5" type="text" data-ng-model="eventUpdate.Venue" data-requiredmessage="Required" data-msglocation="errorvenuefieldCtrlID" data-mandatory="true" data-controltype="namefield" value="" id="venuefieldCtrlID" placeholder="<?php echo lang('enter_venue');?>" uix-input="">
                    <label id="errorvenuefieldCtrlID" class="error-block-overlay"></label>
                  </div>
                </div>
                <div class="col-lg-12  col-xs-12">
                  <label><?php echo lang('location');?></label>
                  <div class="text-field-addonright">
                    <input tabindex="6" type="text" data-requiredmessage="Required" data-msglocation="errorEditStreet1CtrlID" id="EditStreet1CtrlID" data-ng-model="eventUpdate.StreetAddress" data-mandatory="true" data-controltype="" value="" placeholder="<?php echo lang('select_location');?>" uix-input="">
                    <span class="input-addon"> <i class="icon-location"></i> </span>
                    <label id="errorEditStreet1CtrlID" class="error-block-overlay"></label>
                    
                    <div class="drag-scrollbar drag-xs mCustomScrollbar" ng-if="eventUpdate.Locations.length" custom-scroll>                  
                        <ul class="list-draging"  dnd-list="Locations" >
                            <li class="items" ng-repeat="(index, location) in eventUpdate.Locations">
                                <span class="icon">
                                    <i class="ficon-reorder"></i>
                                </span>
                                <div class="overflow">
                                    <a class="close icon" ng-click="removeLocation(index, eventUpdate.Locations)"><i class="ficon-cross"></i></a>
                                    <span class="text">                                                
                                        <span class="regular" ng-bind="location.FormattedAddress"></span>                                                    
                                    </span>
                                </div>
                            </li>                                                                                                            
                        </ul>
                    </div>
                    
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label><?php echo lang('start_date');?></label>
              <div class="input-group">
                <div class="row">
                  <div class="col-lg-6 col-xs-12 xs-gutter">
                    <div class="text-field">
                      <input tabindex="7" data-ng-model="eventUpdate.StartDate" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidStartDate" data-mandatory="true"  data-controltype="" placeholder="__ /__ /__" id="datepicker33"  />
                      <label id="errorValidStartDate" class="error-block-overlay"></label>
                    </div>
                  </div>
                  <!--  <span class="input-group-addon"></span> -->
                  <div class="col-lg-6 col-xs-12 xs-gutter">
                    <div class="text-field">
                      <input tabindex="8" data-ng-model="eventUpdate.StartTime" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidStartTime" data-mandatory="true"  data-controltype="" placeholder="Enter Time" id="timepicer3" />
                      <label id="errorValidStartTime" class="error-block-overlay"></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label><?php echo lang('end_date');?></label>
              <div class="input-group">
                <div class="row">
                  <div class="col-lg-6 col-xs-12 xs-gutter">
                    <div class="text-field">
                      <input tabindex="9" data-ng-model="eventUpdate.EndDate" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required" data-msglocation="errorValidEndDate" data-mandatory="true"  data-controltype="" placeholder="__ /__ /__" id="datepicker44"  />
                      <label id="errorValidEndDate" class="error-block-overlay"></label>
                    </div>
                  </div>
                  <div class="col-lg-6 col-xs-12 xs-gutter">
                    <div class="text-field">
                      <!-- <span class="input-group-addon"></span> -->
                      <input tabindex="10" data-ng-model="eventUpdate.EndTime" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidEndTime" data-mandatory="true"  data-controltype="" placeholder="Enter Time" id="timepicer4" />
                      <label id="errorValidEndTime" class="error-block-overlay"></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="row">
                <div class="col-lg-6  col-xs-12 xs-gutter">
                  <label><?php echo lang('recurring');?></label>
                  <div class="text-field-select" ng-init="RecurringOptions=[{Name:'No',RKey:'No'}]">
                    <select tabindex="11"  chosen data-disable-search="true" option="PrivacyOptions" ng-options="ROptions.RKey as ROptions.Name for ROptions in RecurringOptions"  data-ng-model="eventUpdate.RRule" id="RRULE">
                      <option value=""></option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-6  col-xs-12">
                  <label><?php echo lang('event_privacy');?></label>
                  <div class="text-field-select" ng-init="PrivacyOptions=[{Name:'Public',PKey:'PUBLIC'},{Name:'Invite Only',PKey:'INVITE_ONLY'},{Name:'Private',PKey:'PRIVATE'}]">
                    <select tabindex="12"  chosen data-disable-search="true" option="PrivacyOptions" ng-options="POptions.PKey as POptions.Name for POptions in PrivacyOptions" id="Privacy" data-ng-model="eventUpdate.Privacy">
                      <option value=""></option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
         </div>
         
         <div class="modal-footer"> <span class="alert-danger" ng-bind="error_message"></span>
            <button type="submit" id="UpdateEventFormBtn" class="btn btn-primary pull-right" data-ng-click="UpdateEvent()">
            <?php echo lang('update');?>
            <span class="btn-loader"> <span class="spinner-btn">&nbsp;</span> </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  
</div>
