<!-- Main Content -->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><a>Home</a></li>
                    <li>/</li>
                    <li><span>Announcement</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--Info row-->
<section class="main-container">
<div ng-controller="announcementController" id="announcementController" class="container">
    <div ng-init="list_view=1;">
        <div class="info-row row-flued">
            <h2>Announcement</h2>
            <div class="info-row-right rightdivbox" >
                <a href="javascript:void(0);" class="btn-link" ng-click="add_new_message()">
                                <ins class="buttion-icon" style="margin: 0;"><i class="icon-add">&nbsp;</i></ins> <span>Add Announcement</span> </a>
              

               <!-- <div class="text-field search-field" data-type="focus">
                    <div class="search-block">
                        <input type="text" ng-model="search_blog_model" value="" id="searchField">
                        <div class="search-remove">
                            <i class="icon-close10" id="clearText" ng-click="blog_reset_search();">&nbsp;</i>
                        </div>
                    </div> 
                    <input type="button" id="searchButton" ng-click="search_blog();" class="icon-search search-btn">
                </div>
                -->

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
                    <p><?php echo lang('Sure_Delete'); ?> <b>{{currentUserName}}</b>?</p>
                    <div class="communicate-footer text-center">
                        <button class="button wht" onClick="closePopDiv('delete_popup', 'bounceOutUp');"><?php echo lang('Confirmation_popup_No'); ?></button>
                        <button class="button" ng-click="delete_blog();" id="button_on_delete" name="button_on_delete">
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
        <!--/Info row-->
        <div class="row-flued">
            <div class="panel panel-secondary">
                <div class="panel-body">
                <table class="table table-hover" id="userlist_table1">
                    <tbody>
                    <tr>  
                        <!-- <th id="Title" class="ui-sort selected" style="width:15%">                           
                            <div class="shortdiv sortedDown">
                            Title
                           <span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="Description" class="ui-sort selected" style="width:27%" >                           
                            <div class="shortdiv sortedDown">
                            Description
                            <span class="icon-arrowshort">&nbsp;</span></div>
                        </th>
                        <th id="EntityType" class="ui-sort selected" style="width:6%">                           
                            <div class="shortdiv sortedDown">
                           Type
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        -->
                        <th  id="Image" style="width:40%">                           
                            <div class="shortdiv sortedDown">
                            Image
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <th  id="Url" style="width:25%">                           
                            <div class="shortdiv sortedDown">
                            URL
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        <!-- <th class="ui-sort" id="Action" style="width:10%">                           
                            <div class="shortdiv sortedDown">
                            Call to Action
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                        -->
                        <th  id="CreatedDate" style="width:10%">                           
                            <div class="shortdiv sortedDown">Published On
                            <span class="icon-arrowshort hide">&nbsp;</span></div>
                        </th>
                       
                        <th style="width:8%"><?php echo lang('Actions')?></th>
                    </tr>
                    <tr class="rowtr" ng-repeat="Data in listData" ng-init="Data.indexArr=$index">
                        <!-- <td ng-bind-html="Data.Title"></td>
                        <td ng-bind-html="Data.Description"></td>
                        <td ng-bind="Data.Type==2 ? 'Image' : 'Text'">
                        </td> -->
                        <td>
                            <img ng-if="Data.ImageName!=''" style="max-height:100px; " ng-src="{{imageServerPath + 'upload/blog/220x220/' + Data.ImageName}}">
                        </td>
                        <td >
                            {{Urls_arr[Data.URL]}}
                            <br><span ng-if="Data.URL=='CUSTOM_URL'">{{Data.RedirectTo}}</span>
                            <span ng-if="Data.URL=='POST'">Activity ID: {{Data.RedirectTo}}</span>
                            <span ng-if="Data.URL=='FEEDBACK'">User ID: {{Data.RedirectTo}}</span>
                            <span ng-if="Data.URL=='POST_TAG' || Data.URL=='QUESTION_CATEGORY' || Data.URL=='CLASSIFIED_CATEGORY'">{{Data.TagName}} ({{Data.RedirectTo}})</span>
                            <span ng-if="Data.URL=='QUIZ'">{{Data.QuizTitle}}</span>
                        </td>
                        <!-- <td ng-bind="Data.ActionText"></td> -->
                        <td ng-bind="Data.CreatedDate"></td>
            
                        <td>
                            <a href="#"  ng-click="set_data(Data);" class="user-action" onClick="userActiondropdown()">
                                <i class="icon-setting">&nbsp;</i>
                            </a>
                        </td>
                    </tr>   
                    <tr id="noresult_td" ng-if="listData.length==0"><td colspan="4"><div class="no-content text-center"><p>No record found</p></div></td></tr>
                    </tbody>
                </table>
                
              

               
            </div>
            </div>

             <!--Actions Dropdown menu-->
                <ul class="dropdown-menu userActiondropdown" style="left: 1191.5px; top: 297px; display: none;">  
                    <li><a onclick="openPopDiv('delete_popup', 'bounceInDown');" href="javascript:void(0);"><?php echo lang("User_Index_Delete"); ?></a></li> 
                </ul>
                <!--/Actions Dropdown menu-->

                <span id="result_message" class="result_message"><?php echo lang("ThereIsNoRecordToShow"); ?></span>
        </div>
    </div>

        <div class="modal fade" id="addNewMessage" ng-cloak data-backdrop="static">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close dis-cret-m" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="icon-close" ng-click="resetPopup();"></i></span></button>
                        <h4 ng-if="announcement.EntityType ==2 ">Welcome Message</h4>
                        <h4 ng-if="announcement.EntityType ==4 ">Announcement</h4>
                        <h4 ng-if="announcement.EntityType ==3 ">Introduction Text</h4>
                    </div>
                    <div class="modal-body">
                    <div class="popup-content">
                        <div class="communicate-footer row-flued">
                            <div class="from-subject">
                                <label for="subjects" class="label">Type</label>
                                <div> 
                                    <select ng-change="reset_form();" data-chosen="" data-disable-search="true"  ng-options="POptions.MKey as POptions.Name for POptions in TypeOptions" data-ng-model="announcement.Type">
                                        
                                    </select>                        
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{errorTypeMessage}}</div>
                            </div>
                            <div ng-if="announcement.Type==1" class="from-subject">
                                <label for="subjects" class="label">Title</label>
                                <div class="text-field">
                                    <input type="text" maxlength="40" data-req-maxlen="40" ng-model="announcement.Title">
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_title}}</div>
                            </div>
                            <div ng-if="announcement.Type==1" class="from-subject"> 
                                <label class="label" for="subject">Description</label>
                                <div class="text-field ">
                                    <textarea class="textarea" maxlength="100" data-req-maxlen="100" ng-model="announcement.Description"></textarea> 
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_description}}</div>
                            </div> 

                            <div class="from-subject">
                                <label for="subjects" class="label">Redirect</label>
                                <div> 
                                    <select data-chosen="" data-disable-search="true"  ng-options="PUrl.MKey as PUrl.Name for PUrl in Urls" data-ng-model="announcement.Url" ng-change="show_url_option()">
                                        
                                    </select>                        
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;"></div>
                            </div>
                            
                            <div ng-if="UrlOptions==1" class="from-subject">
                                <label for="subjects" class="label">Activity ID</label>
                                <div class="text-field">
                                    <input type="text" ng-model="announcement.ActivityGUID">
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_activity}}</div>
                            </div>

                            <div ng-if="UrlOptions==6" class="from-subject">
                                <label for="subjects" class="label">User ID</label>
                                <div class="text-field">
                                    <input type="text" ng-model="announcement.UserGUID">
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_user}}</div>
                            </div>

                            <div ng-if="UrlOptions==5" class="from-subject">
                                <label for="subjects" class="label">URL</label>
                                <div class="text-field">
                                    <input type="text" ng-model="announcement.CustomURL">
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_custom_url}}</div>
                            </div>

                            <div ng-if="UrlOptions==7" class="from-subject">
                                <label class="label">Select Quiz</label>    
                                <div>
                                    <tags-input 
                                        ng-model="announcement.Quiz" 
                                        display-property="Name" 
                                        placeholder="Select quiz" 
                                        on-tag-added="addQuizAdded($tag)"
                                        on-tag-removed="addQuizAdded($tag)"
                                        max-tags=1
                                        replace-spaces-with-dashes="false" 
                                        add-from-autocomplete-only="true"
                                        template="tagsTemplate">
                                        <auto-complete source="getQuiz($query)" load-on-focus="true" min-length="0" max-results-to-show="25" ></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="tagsTemplate">
                                        <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                            <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                        </div>
                                    </script>                                    
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_quiz}}</div>
                            </div>

                            <div ng-if="UrlOptions==2" class="from-subject">
                                <label class="label">Post Tag</label>    
                                <div>
                                    <tags-input 
                                        ng-model="announcement.Tag" 
                                        display-property="Name" 
                                        placeholder="Select post tag" 
                                        on-tag-added="addTagAdded($tag)"
                                        on-tag-removed="addTagAdded($tag)"
                                        max-tags=1
                                        replace-spaces-with-dashes="false" 
                                        add-from-autocomplete-only="true"
                                        template="tagsTemplate">
                                        <auto-complete source="getActivityTags($query,'ACTIVITY')" load-on-focus="true" min-length="0" max-results-to-show="15" ></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="tagsTemplate">
                                        <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                            <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                        </div>
                                    </script>                                    
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_tag}}</div>
                            </div>

                            <div ng-if="UrlOptions==3" class="from-subject">
                                <label class="label">Question Category</label>    
                                <div>
                                    <tags-input 
                                        ng-model="announcement.Tag" 
                                        display-property="Name" 
                                        placeholder="Select question category" 
                                        on-tag-added="addTagAdded($tag)"
                                        on-tag-removed="addTagAdded($tag)"
                                        max-tags=1
                                        replace-spaces-with-dashes="false" 
                                        add-from-autocomplete-only="true"
                                        template="tagsTemplate">
                                        <auto-complete source="loadTagCategories($query, 20)" load-on-focus="true" min-length="0" max-results-to-show="15" ></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="tagsTemplate">
                                        <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                            <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                        </div>
                                    </script>                        
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_tag}}</div>
                            </div>

                            <div ng-if="UrlOptions==4" class="from-subject">
                                <label class="label">Classified Category</label>    
                                <div>
                                    <tags-input 
                                        ng-model="announcement.Tag" 
                                        display-property="Name" 
                                        placeholder="Select classified category" 
                                        on-tag-added="addTagAdded($tag)"
                                        on-tag-removed="addTagAdded($tag)"
                                        max-tags=1
                                        replace-spaces-with-dashes="false" 
                                        add-from-autocomplete-only="true"
                                        template="tagsTemplate">
                                        <auto-complete source="loadTagCategories($query, 6)" load-on-focus="true" min-length="0" max-results-to-show="15" ></auto-complete>
                                    </tags-input>
                                    <script type="text/ng-template" id="tagsTemplate">
                                        <div ng-init="tagname = $getDisplayText();" data-toggle="tooltip" data-original-title="{{data.Name}}" tag-tooltip ng-cloak>
                                            <span ng-bind="$getDisplayText()" class="ng-binding ng-scope"></span>
                                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                                        </div>
                                    </script>                        
                                </div>
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_tag}}</div>
                            </div>
                            <div class="from-subject">
                                
                                <div class="form-group">
                                <input type="checkbox" id="CanIgnore" name="CanIgnore" ng-checked="CanIgnore == '1'">
                                <label for="">Allow user to ignore</label>
                                </div>

                            </div>
                           <!-- <div ng-if="announcement.Type==2" class="from-subject"> 
                                <label for="" class="label">Image </label>

                                <div class="upload-image" id="uploadimageBTN">
                                    <div class="button-wrapper">
                                        <a class="btn btn-primary"  ngf-select="uploadProfilePicture($file, $invalidFiles);" accept="image/*" ngf-validate-async-fn="validateFileSize($file);">
                                            Upload
                                        </a>
                                    </div>
                                </div>

                                
                                <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_image}}</div>
                                <ul class="attached-media"  ng-if="isLoadingImage">
                                    <li><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='../assets/admin/img/loading22.gif'  id='spinner'></div></div></li>
                                </ul>
                                <ul class="attached-media" id="attached-media-1" ng-if="showImage">
                                    <li>
                                        <a ng-click="remove_announcement_image()" class='smlremove'></a>
                                        <figure>
                                            <img width='98px' class='img-category-full'   ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/blog/{{AnnouncementImageName}}'>
                                        </figure>
                                    </li>
                                </ul>
                            </div>
-->

                            <div ng-if="announcement.Type==2" class="form-group" ng-init="initializeCropper();">
                                <label class="label" for="severName">Upload Image </label>
                                <div class="p-v-sm"><small>Note : For best result please upload image size 600 X 112</small></div>
                                <div class="browse-image row" data-type="focus">
                                    <div class="col-sm-3">
                                        <div class="support-search-new btn btn-primary relative">
                                            <input type="file" id="fileInputBanner" value="Browse">
                                            <label class="label-white">Browse</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group" style="margin: 10px 0px;">
                                    <input type="hidden" id="BannerSize" ng-init="BannerData.BannerSize = '600x112'" ng-model="BannerData.BannerSize">
                                    <div class="cropArea" style="height: 280px;"  ng-show="myImageBanner">
                                        <img-crop image="myImageBanner" 
                                            area-type="rectangle" 
                                            aspect-ratio="5.4" 
                                            result-image-size='{w: 600,h: 112}' 
                                            area-min-size='{w: 240,h: 45}'
                                            result-image="myCroppedImageBanner" ></img-crop>
                                    </div>
                                    <div>
                                        <img id="CroppedImgData" ng-src="{{myCroppedImageBanner}}" style="max-width: 100%;" />
                                        
                                    </div>
                                    <div style="display: none" class="error-holder" id="ErrorValideImage">
                                        Please upload only jpg or png file
                                    </div>
                                    <div class="error-holder" ng-show="showError" style="color: #CC3300;">{{Error.error_image}}</div>
                                </div>
                            </div>


                            
                            <div class="from-subject" style="height: 50px;">
                                <button class="button wht dis-cret-m"  ng-click="resetPopup()">Cancel</button>
                                <button class="button dis-cret-m" ng-click="save_announcement('PUBLISHED');">Save</button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>        

    
</div>
</section>