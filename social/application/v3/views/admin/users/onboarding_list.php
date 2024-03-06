<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span><a target="_self" href="<?php echo base_url('admin/users'); ?>"><?php echo lang('User_Index_Users'); ?></a></span></li>
                    <li>/</li>
                    <li ><span>Onboarding</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Info row-->
<section class="main-container">
<div ng-controller="UserListCtrl" id="OnboardingListCtrl" ng-init="onboarding();"  class="container">
<div ng-init="list_view=1;" id="UserListCtrl">
<div class="info-row row-flued">
    <h2>ONBOARDING</h2>
    <div class="info-row-right rightdivbox" >


        <div id="ItemCounter" class="items-counter">
            <ul class="button-list">
                <?php if(in_array(getRightsId('delete_user_event'), getUserRightsData($this->DeviceType))){ ?>
                    <li><a href="javascript:void(0);" ng-hide="userStatus==3" onclick="openPopDiv('confirmeMultipleUniversityPopup', 'bounceInDown');"><?php echo lang("User_Index_Delete"); ?></a></li>
                <?php } ?>
            </ul>
            <div class="total-count-view"><span class="counter">0</span> </div>
        </div>
        
    </div>
    <!--Popup for Delete a user  -->
    <div class="popup confirme-popup animated" id="delete_popup">
        <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onClick="closePopDiv('delete_popup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p>Are you sure you want to delete Associated Question?</p>
            <div class="communicate-footer text-center">
                <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="delete_question();" id="button_on_delete" name="button_on_delete">
                    <span class="loading-button">&nbsp;</span><?php echo lang('Confirmation_popup_Yes'); ?>
                </button>
            </div>
        </div>
    </div>
    <!--Popup end Delete a user  -->
    <div class="popup confirme-popup animated" id="confirmeMultipleUniversityPopup">
    <div class="popup-title"><?php echo lang('Confirmation_popup_Confirmation'); ?> <i class="icon-close" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <p class="text-center"><?php echo lang('Sure_Delete')?></p>
            <div class="communicate-footer text-center">
                <button class="button wht" onclick="closePopDiv('confirmeMultipleUniversityPopup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                <button class="button" ng-click="delete_multiple_blogs()"><?php echo lang('Confirmation_popup_Yes'); ?></button>
            </div>
        </div>
    </div>
</div>
    <div class="row-flued">
              <p class="info-text">Drag and drop the field name/fow containing field to change the priority of the field.</p>
            </div>
<!--/Info row-->
<div class="row-flued">
    <div class="panel panel-secondary">
        <div class="panel-body">
            <table class="table table-hover blog" id="onboarding_table">
                <thead>
                <tr>
                    <th>                           
                        <div class="shortdiv sortedDown">
                        Field Name
                        </div>
                    </th>
                    <th>                           
                        <div class="shortdiv sortedDown">
                       Associated Question
                        </div>
                    </th>
                  
                    <th>                           
                        <div class="shortdiv sortedDown">
                        Priority
                        </div>
                    </th>
                    <th>                           
                        <div class="shortdiv sortedDown">Status
                        </div>
                    </th>
                    <th><?php echo lang('Actions')?></th>
                </tr>
            </thead>
            <tbody id="sortable">
                <tr class="rowtr"  ng-repeat="Data in OnboardingData" ng-init="Data.indexArr=$index" repeat-done="repeateDone();">
                    <td ng-bind="Data.Title"></td>
                    <td ng-bind="Data.Description"> </td>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <span ng-if="Data.StatusID==2">Active</span>
                        <span ng-if="Data.StatusID==10">Draft</span>
                    </td>
        
                    <td>
                        <a href="#"  ng-click="set_data(Data);" class="user-action" onClick="userActiondropdown()">
                            <i class="icon-setting">&nbsp;</i>
                        </a>
                    </td>
                </tr>   
                </tbody>
            </table>  
            <!-- Pagination -->
            <div class="showingdiv"><label class="ng-binding" paging-info total-record="totalRecord" num-per-page="numPerPage" current-page="currentPage"></label></div>
            <ul uib-pagination total-items="totalRecord" items-per-page="numPerPage" ng-model="currentPage" max-size="maxSize" num-pages="numPages" class="pagination-sm" boundary-links="false" ></ul>
        <!-- Pagination -->                 

        </div>
    </div>
        <!--Actions Dropdown menu-->
        <ul class="dropdown-menu  userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">
            <li><a ng-click="add_new_question();" href="javascript:void(0);" ng-if="OnboardingCurrentData.Description==''">Associate Question</a></li>   
            <li><a ng-click="add_new_question();" href="javascript:void(0);" ng-if="OnboardingCurrentData.Description">Edit Question</a></li>   
            <li><a ng-click="save_question(10);" href="javascript:void(0);" ng-if="OnboardingCurrentData.StatusID==2">Draft</a></li>   
            <li><a ng-click="save_question(2);" href="javascript:void(0);" ng-if="OnboardingCurrentData.StatusID==10">Publish</a></li>   
            <li><a onclick="openPopDiv('delete_popup', 'bounceInDown');" href="javascript:void(0);" ng-if="OnboardingCurrentData.Description">Delete Question</a></li> 
        </ul>
        <!--/Actions Dropdown menu-->
    

    <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
</div>
</div>
    <div class="popup communicate animated" id="addNewQuestion">
        <div class="popup-title">Edit Question <i class="icon-close" onClick="closePopDiv('addNewQuestion', 'bounceOutUp');">&nbsp;</i></div>
        <div class="popup-content">
            <div class="communicate-footer row-flued">
                <div class="from-subject">  
                    <div class="label">
                         {{OnboardingCurrentData.Title}} 
                    </div>
                    <div class="error-holder usrerror">{{Error.error_fieldname}}</div>
                </div> 
                <div class="from-subject"> 
                    <label class="label" for="subject">Question Associated</label>
                    <div class="text-field ">
                        <textarea class="textarea" ng-model="OnboardingCurrentData.Description"></textarea>
                        
                    </div>
                    <div class="error-holder usrerror">{{Error.error_description}}</div>
                </div> 
                <button class="button wht" onClick="closePopDiv('addNewQuestion', 'bounceOutUp');">Cancel</button>
                <button class="button"  ng-click="save_question('2');">Publish</button>
                <button class="button" ng-click="save_question('10');">Draft</button>
                   </div>
        </div>
 
    </div>
</div>
</section>