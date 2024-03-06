
 <div class="modal fade" id="editGroup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
   
  	<div class="modal-dialog">
     <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="icon-close"></i></span></button>
        <h4 class="modal-title" id="myModalLabel">EDIT GROUP</h4>
      </div>
      <div class="modal-body">
        <form id="formGroup" >

          <div class="form-group">
            <label><?php echo lang('group_name');?></label>
            <div class="text-field">
              <div data-error="hasError" class="text-field">
       <input type="text"
                uix-input
                name="GroupName"
                id="group_name"
                value=""
                placeholder=""
                data-controltype="general"
                data-mandatory="true"
                data-msglocation="errorGroupName"
                data-requiredmessage="Required"
                data-ng-model="GroupName"
                 />

            <label id="errorGroupName" class="error-block-overlay"></label>
              </div>
            </div>
          </div>



           <div class="form-group" ng-controller="FormCtrl" id="FormCtrl" ng-init="GroupCategories()">
            
            <label>Category</label>
            <div class="text-field-select">

                   <select  name="CategoryIds" id="CategoryIds" ng-model="CategoryIds"  data-placeholder="Select Category"
                    data-chosen=""
                    data-disable-search="false"                                                    
                    data-ng-options="category.Name for category in GroupCategories track by category.CategoryID">
                  </select>      

            </div>
         

          </div>

          <div class="form-group">
            <label><?php echo lang('group_description');?></label>
            <div data-error="hasError" class="textarea-field">
                      <textarea maxcount="400" rows="5" maxlength="400" uix-textarea data-mandatory="true" class="form-control" data-controltype="generalTextArea" 
                           id="group_description" data-msglocation="errorGroupDesc" name="GroupDescription" placeholder="Description about the group" tabindex="2" data-requiredmessage="Required" data-ng-model="GroupDescription"></textarea>

                            <label class="error-holder" id="errorGroupDesc"></label>
            </div>
          </div>

          <div class="form-group">
            <label>Group Privacy</label>
            <div class="privat-lisitng">
              <ul class="list-group">
                <li class="list-group-item">
                  <div class="radio">
                    <input type="radio" checked="checked" value="1" name="IsPublic" ng-model = "IsPublic" id="openGroup">
                    <label for="openGroup"> <i class="icon-n-global"></i> Open</label>
                    <p>Anyone can see the group, its members and their posts.</p>
                  </div>
                </li>
                <li class="list-group-item">
                  <div class="radio">
                     <input type="radio" value="0" name="IsPublic" ng-model = "IsPublic" id="closeGroup">
                    <label for="closeGroup">   <i class="icon-n-closed"></i>  Closed </label>
                    <p>Anyone can see the group, but only members can post.</p>
                  </div>
                </li>
                <li class="list-group-item">
                  <div class="radio">
                    <input id="secret" type="radio" name="IsPublic" ng-model = "IsPublic" value="2" >
                    <label for="secret"><i class="icon-n-group-secret"></i> Secret </label>
                    <p>Only invited members can see group.</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <input type="hidden" name="GroupGUID" data-ng-model="GroupName" />

        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary pull-right" ng-controller="FormCtrl" ng-click="FormSubmit()" >UPDATE</button>
      </div>
    </div>
    </div>
   
</div>    