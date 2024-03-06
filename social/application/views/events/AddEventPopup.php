<!-- Create Group Modal -->
<div class="modal fade" id="createEvent" data-backdrop="static">
  <div class="modal-dialog modal-mmd">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="CreateEventClose"><span aria-hidden="true"><i class="icon-close"></i></span></button>
        <h4 class="modal-title" ng-bind="lang.create_event_btn"></h4>        
      </div>
      <div class="modal-body">
        <form id="formEvent" class="form">
          <div class="form-body">
            <div class="row">
              <div class="col-sm-10 col-sm-offset-1">
                <div class="row">
                  <div class="col-sm-12 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.event_title"></label>
                      <input tabindex="1" type="text" maxlength="100" data-requiredmessage="Required" data-msglocation="errorNamefield" data-mandatory="true" data-controltype="" id="namefieldCtrlID" placeholder="{{lang.event_title}}" uix-input="" data-ng-model="events.Title" class="form-control">
                      <span class="block-error" id="errorNamefield"></span>
                    </div>
                  </div>
                  <div class="col-sm-12 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.Category"></label>
                        <select class="form-control" tabindex="2" name="CategoryIds" id="CategoryIds" data-mandatory="true" data-msglocation="errorCategories"  data-placeholder="Select Category" data-controltype="general" data-requiredmessage="Required" chosen  option="listData[0].CatObj" ng-options="Cat.CategoryID as Cat.Name for Cat in CategoryData[0].CatObj"  ng-show="CatObj.length>0" data-ng-model="events.CategoryID">
                          <option value=""></option>
                        </select>
                      <span class="block-error" id="errorCategories"></span>
                    </div>
                  </div>
                  <div class="col-sm-12 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.event_url"></label>
                      <input tabindex="4" class="form-control" type="text" data-ng-model="events.URL" data-msglocation="errorValidurl"  data-controltype="validurl" value="" id="validurlCtrlID" placeholder="e.g. https://www.exampleurl.com" uix-input="" >
                      <span id="errorValidurl" class="block-error"></span>
                    </div>
                  </div>
                  <div class="col-sm-12 col-xs-12">  
                    <div class="form-group">
                      <label ng-bind="lang.event_detail"></label>
                      <div data-error="hasError" class="textarea-field">
                        <textarea tabindex="3" maxcount="400" id="textareaID" uix-textarea data-ng-model="events.Description" placeholder="{{lang.event_detail}}"></textarea>
                        <span id="spn2textareaID"></span> </div>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.venue"></label>
                      <input tabindex="5" class="form-control" type="text" data-ng-model="events.Venue" data-requiredmessage="Required" data-msglocation="errorVenuefield" data-mandatory="true" data-controltype="" value="" id="venuefieldCtrlID" placeholder="{{lang.enter_venue}}" uix-input="">
                      <span id="errorVenuefield" class="block-error"></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.location"></label>
                      <input tabindex="6" type="text" class="form-control" data-requiredmessage="Required" data-msglocation="errorTimefield" id="Street1CtrlID" data-ng-model="events.StreetAddress" data-mandatory="true" data-controltype="" value=""  placeholder="{{lang.select_location}}" uix-input="">
                        <span id="errorTimefield" class="block-error"></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.start_date"></label>
                      <div class="input-group">
                        <input tabindex="7" data-ng-model="events.StartDate" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidStartDate" data-mandatory="true"  data-controltype="" placeholder="__ /__ /__" id="datepicker3"  />
                        <label for="startdate" class="input-group-addon addon-white"><i class="ficon-calc"></i></label>
                      </div>
                      <span id="errorValidStartDate" class="block-error"></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.enter_time"></label>
                      <input tabindex="8" data-ng-model="events.StartTime" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidStartTime" data-mandatory="true"  data-controltype="" placeholder="Enter Time" id="timepicer" />
                      <span class="block-error" id="errorValidStartTime" ></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.end_date"></label>
                      <div class="input-group">
                        <input tabindex="9" data-ng-model="events.EndDate" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required" data-msglocation="errorValidEndDate" data-mandatory="true"  data-controltype="" placeholder="__ /__ /__" id="datepicker4"  />
                        <label for="enddate" class="input-group-addon addon-white"><i class="ficon-calc"></i></label>
                      </div>
                      <span id="errorValidEndDate" class="block-error"></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="form-group" data-error="has-error">
                      <label class="control-label" ng-bind="lang.enter_time"></label>
                      <input tabindex="10" data-ng-model="events.EndTime" type="text" class="form-control readonly-normal" readonly="readonly" autocomplete="off" data-requiredmessage="Required"  data-msglocation="errorValidEndTime" data-mandatory="true"  data-controltype="" placeholder="Enter Time" id="timepicer2" />
                      <span id="errorValidEndTime" class="block-error"></span>
                    </div>
                  </div>  
                  <div class="col-sm-12 col-xs-12">
                    <div class="form-group" data-error="has-error" ng-init="PrivacyOptions=[{Name:'Public',PKey:'PUBLIC'},{Name:'Invite Only',PKey:'INVITE_ONLY'},{Name:'Private',PKey:'PRIVATE'}]">
                      <lable class="control-label" ng-bind="lang.event_privacy"></lable>
                      <select tabindex="12"  data-chosen="" class="form-control" data-disable-search="true"  ng-options="POptions.PKey as POptions.Name for POptions in PrivacyOptions" data-ng-model="events.Privacy">
                        <option value=""></option>
                      </select>
                      <span class="block-error">Error message</span>
                    </div>
                  </div>
                  <div class="col-sm-12 col-xs-12">
                    <div class="form-actions clearfix">
                      <div class="btn-toolbar btn-toolbar-xs right">
                        <span class="alert-danger" ng-bind="error_message"></span>
                        <button type="submit" id="AddEventFormBtn" class="btn btn-primary pull-right" data-ng-click="FormSubmit()" ng-bind="lang.create">
                          <!-- <span class="btn-loader"> <span class="loader">&nbsp;</span> </span> -->
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>    
            </div>
          </div>  
        </form>
      </div>
    </div>
  </div>
</div>
<input type="hidden" id="ModuleID" value="">
<input type="hidden" id="ModuleEntityID" value="">