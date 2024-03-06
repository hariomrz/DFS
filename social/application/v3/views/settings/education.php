  <aside id="educationInfo" class="content-block-region">
    <div class="title">
      <div class="editSave">
        <a class="btn btn-default btn-icon hide"  title="Edit"
            data-ng-class="{'show': !educationInfoEdit, 'hide': educationInfoEdit}"
            data-ng-click="educationInfoEdit= !educationInfoEdit;ChangePanelStatus('educationInfoEdit');CheckEducationInfoExists();"
            ng-cloak >
            <i class="ficon-pencil"></i>Edit
        </a>
        <div class="save-cancel hide" data-ng-class="{'show': ShowEducationInfoEditBtn, 'hide': !ShowEducationInfoEditBtn}" ng-cloak>
            <a class="cancelEdit btn btn-link gray-clr"  title="Cancel"
                data-ng-click="educationInfoEdit= !educationInfoEdit; ShowEducationInfoEditBtn = !ShowEducationInfoEditBtn;getResetValue('EductionDtl');ChangeEducationInfoPanelStatus();">Cancel</a>
            <input type="button" class="saveAccount btn btn-default" title="Save" onClick="return checkstatus('educationInfo');" value="Save" ng-click="saveProfile('EduInfo');" />
        </div>
      </div>
      <span class="title-text">EDUCATION</span> </div>
    <div on="educationInfoEdit"  data-ng-switch="" class="ng-scope">
      <!-- ngSwitchDefault:  -->
      <div class="table-content"  data-ng-switch-default="">
        <aside class="row workdetail-group" ng-if="UserEducation.length>0" ng-repeat="Edu in UserEducation">
          <aside class="col-xs-12 col-sm-6">
            <div class="form-group">
              <label>University Name</label>
              <div class="viewMode"> <span ng-bind="Edu.University"></span> </div>
            </div>
          </aside>
          <aside class="col-xs-12 col-sm-6">
            <div class="form-group">
              <label>Course Name</label>
              <div class="viewMode"> <span ng-bind="Edu.CourseName"></span> </div>
            </div>
          </aside>
          <aside class="col-xs-12 col-sm-6">
            <div class="form-group">
              <label>Dates Attended</label>
              <div class="viewMode"> <span ng-bind="Edu.StartYear+' - '+Edu.EndYear"></span> </div>
            </div>
          </aside>
        </aside>
      </div>
      <!-- ngSwitchWhen: true -->
      <div class="table-content work-edit hide" data-ng-switch-when="true" data-ng-class="{'show': educationInfoEdit, 'hide': !educationInfoEdit}">
        <div class="inner-edit UserEducation" ng-if="$parent.UserEducationEdit.length>0" ng-repeat="Edu in $parent.UserEducationEdit">
          <input type="hidden" name="EducationGUID[]" ng-value="Edu.EducationGUID" />
          <a href="javascript:void(0);" class="remove-current" onclick="deleteParent(this)" ><i class="icon-smremove"></i></a>
          <aside class="row">
              <aside class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>University Name</label>
                  <div data-error="hasError" class="text-field" ng-init="InitSectionAutocomplete('Education','University',Edu.University);">
                    <input type="text" 
                      class="University" 
                      data-req-minlen="2" data-req-maxlen="50" maxlength="50"
                      data-msglocation="errorUniversityName{{$index + 1}}"
                      data-controltype="customval" value="" 
                      ng-value="Edu.University" 
                      id="universityfieldCtrlID{{$index + 1}}"
                      name="University[]" 
                      placeholder="University Name" 
                      uix-input="" />
                      <label id="errorUniversityName{{$index + 1}}" class="error-block-overlay"></label>
                  </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-6">
                <div class="form-group">
                  <label>Course Name </label>
                    <div data-error="hasError" class="text-field" ng-init="InitSectionAutocomplete('Education','CourseName',Edu.CourseName);">
                      <input type="text" 
                        class="CourseName" 
                        data-req-minlen="2" data-req-maxlen="50" maxlength="50"
                        data-msglocation="errorCourse{{$index + 1}}"
                        data-controltype="alphanum" value="" 
                        ng-value="Edu.CourseName"
                        id="coursefieldCtrlID{{$index + 1}}" 
                        name="CourseName[]"
                        placeholder="Course Name" uix-input="" />
                      <label id="errorCourse{{$index + 1}}" class="error-block-overlay"></label>
                    </div>
                </div>
              </aside>
              <aside class="col-xs-12 col-sm-12">
                <div class="form-group">
                  <label>Dates Attended</label>
                  <div class="row"> 
                    <aside class="col-xs-12 col-sm-6 small-select">
                      <div class="text-field-select left">
                        <select onchange="resetChosen(this)" name="EStartYear[]" ng-model="Edu.StartYearObj" data-chosen="" data-disable-search="true" data-placeholder="From Year" ng-options="year for year in yearsArr track by year">
                        <option value=""></option>
                        </select>
                        <label class="error-block-overlay" style="display:none;">Invalid Date</label>
                      </div>
                      <div class="text-field-select mid">
                        <select onchange="resetChosen(this)" name="EEndYear[]" ng-model="Edu.EndYearObj" data-chosen="" data-disable-search="true" data-placeholder="To Year" ng-options="year for year in yearsArr track by year">
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
      <a class="add-more" href="javascript:void(0);" data-ng-click="newEducationItem()"><i class="icon-smadd"></i> Add Education</a>                            </div>
    </div>
  </aside>