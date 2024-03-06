
<aside id="workInfo" class="content-block-region">
  <div class="title">
    <div class="editSave"> 
      <a class="btn btn-default btn-icon hide"  title="Edit"
            data-ng-class="{'show': !workInfoEdit, 'hide': workInfoEdit}"
            data-ng-click="workInfoEdit= !workInfoEdit;ChangePanelStatus('workInfoEdit');CheckWorkInfoExists();"
            ng-cloak > <i class="ficon-pencil"></i>Edit </a>
      <div class="save-cancel hide" data-ng-class="{'show': ShowWorkInfoEditBtn, 'hide': !ShowWorkInfoEditBtn}"  ng-cloak> 
        <a class="cancelEdit btn btn-link gray-clr"  title="Cancel"
              data-ng-click="workInfoEdit= !workInfoEdit;ShowWorkInfoEditBtn=!ShowWorkInfoEditBtn; getResetValue('WorkExp');ChangeWorkInfoPanelStatus();">Cancel</a>
        <input type="button" class="saveAccount btn btn-default" title="Save" onClick="return checkstatus('workInfo');" ng-click="saveProfile('workExp');" value="Save" />
      </div>
    </div>
    <span class="title-text">WORK EXPERIENCE</span> </div>
  <div on="workInfoEdit"  data-ng-switch="" class="ng-scope">
    <!-- ngSwitchDefault:  -->
    <div class="table-content" data-ng-switch-default>
      <aside class="row workdetail-group" ng-if="WorkExperience.length>0" ng-repeat="WExp in WorkExperience">
        <aside class="col-xs-12 col-sm-6">
          <div class="form-group">
            <label>Organisation Name</label>
            <div class="viewMode"> <span ng-bind="WExp.OrganizationName"></span> </div>
          </div>
        </aside>
        <aside class="col-xs-12 col-sm-6">
          <div class="form-group">
            <label>Designation/Project </label>
            <div class="viewMode"> <span ng-bind="WExp.Designation"></span> </div>
          </div>
        </aside>
        <aside class="col-xs-12 col-sm-6">
          <div class="form-group">
            <label>Time Period</label>
            <div class="viewMode"> <span><span ng-bind="getMonthNameFromNum(WExp.StartMonth)+', '+WExp.StartYear"></span> to <span ng-if="WExp.CurrentlyWorkHere=='1'">Present</span><span ng-if="WExp.CurrentlyWorkHere=='0'" ng-bind="getMonthNameFromNum(WExp.EndMonth)+', '+WExp.EndYear"></span></span> </div>
          </div>
        </aside>
      </aside>
    </div>

    <!-- Editable Mode -->

    <div class="table-content work-edit hide" data-ng-switch-when="true" data-ng-class="{'show': workInfoEdit, 'hide': !workInfoEdit}">
      <div class="multiple-experience inner-edit" ng-if="$parent.WorkExperienceEdit.length>0" ng-repeat="WExp in $parent.WorkExperienceEdit">
        <input type="hidden" ng-value="WExp.WorkExperienceGUID" name="WorkExperienceGUID[]">
        <a href="javascript:void(0);" class="remove-current" onClick="deleteParent(this)"><i class="icon-smremove"></i></a>
        <aside class="row">
          <aside class="col-xs-12 col-sm-6">
            <div class="form-group">
              <label>Organisation Name</label >
              <div data-error="hasError" class="text-field" ng-init="InitSectionAutocomplete('WorkExperience','OrganizationName',WExp.OrganizationName);">
                <input class="OrganizationName" data-controltype="customval" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="OrganizationName[]" ng-value="WExp.OrganizationName" placeholder="Organisation Name" uix-input="" />
                <label id="errorOrgname" class="error-block-overlay"></label>
              </div>
            </div>
          </aside>
          <aside class="col-xs-12 col-sm-6">
            <div class="form-group">
              <label>Designation/Project </label>
              <div data-error="hasError" class="text-field" ng-init="InitSectionAutocomplete('WorkExperience','Designation',WExp.Designation);">
                <input data-controltype="alphanum" class="Designation" data-req-minlen="2" maxlength="50" data-req-maxlen="50" type="text" name="Designation[]" ng-value="WExp.Designation" placeholder="Designation/Project" uix-input=""/>
                <label id="errorDesig" class="error-block-overlay"></label>
              </div>
            </div>
          </aside>
          <aside class="col-xs-12 col-sm-12">
            <div class="form-group">
              <label>Time Period</label>
              <div class="checkbox check-default">
                <input type="checkbox" value="1" id="TillDate{{$index}}" name="TillDate[]" class="till-date-checkbox-1" ng-click="resetEnd($index);" ng-checked="(WExp.CurrentlyWorkHere ==1 ? true:false)" >
                <label class="till-date" onClick="updateCheckBoxStatus(this);" for="till-date-checkbox-1">Till Date</label>
              </div>
              <div class="row">
                <aside class="col-xs-12 col-sm-6 small-select">
                  <div class="visible-xs">Show</div>
                  <div class="text-field-select left">
                    <select onChange="resetChosen(this)" name="StartMonth[]" class="start-year" data-chosen="" ng-model="WExp.StartMonthObj" data-disable-search="true" data-placeholder="From Month" ng-options="month.month_name for month in monthsArr track by month.month_val">
                    <option value=""></option>
                    </select>
                    <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                  </div>
                  <div class="text-field-select mid">
                    <select onChange="resetChosen(this)" name="StartYear[]" ng-model="WExp.StartYearObj" class="start-month" data-chosen="" data-disable-search="true" data-placeholder="From Year"  ng-options="year for year in yearsArr track by year">
                    <option value=""></option>
                    </select>
                    <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                  </div>
                </aside>
                <aside class="col-xs-12 col-sm-6 small-select"> <span class="relate hidden-xs">-</span>
                  <div class="text-field-select left">
                    <select onChange="resetChosen(this)" ng-change="resetTillDate($index)" name="EndMonth[]" class="end-year" ng-model="WExp.EndMonthObj" data-chosen="" data-disable-search="true" data-placeholder="To Month"  ng-options="month.month_name for month in monthsArr track by month.month_val">
                    <option value=""></option>
                    </select>
                    <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                  </div>
                  <div class="text-field-select mid">
                    <select onChange="resetChosen(this)" ng-change="resetTillDate($index)" name="EndYear[]" class="end-month" ng-model="WExp.EndYearObj" data-chosen="" data-disable-search="true" data-placeholder="To Year"  ng-options="year for year in yearsArr track by year">
                    <option value=""></option>
                    </select>
                    <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                  </div>
                </aside>
              </div>
            </div>
          </aside>
        </aside>
      </div>
      <a class="add-more" data-ng-click="newItem()" href="javascript:void(0);"><i class="icon-smadd"></i> Add Work Experience</a> </div>
    <!-- ngSwitchWhen: true -->
  </div>
</aside>
