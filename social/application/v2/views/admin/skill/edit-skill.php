<!--Bread crumb-->
<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><?php echo lang('AnalyticsTools_Tools'); ?></li>
                    <li>/</li>
                    <li><span>Edit Skill</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--/Bread crumb-->
<section class="main-container">
<div class="container edit-new-skill" id="SkillCtrl" ng-controller="SkillCtrl"  ng-init="getCategory();
                get_single_skill('<?php echo $skill_id; ?>');"> 
    <h4 class="semibold pageHeading">EDIT SKILL</h4>
    <div class="panel panel-info">
        <div class="panel-body">  
            <div class="form-group with-border">
                <label class="well-title">Skill Name <span>*</span></label>
                <input type="text" maxlength="50" data-req-maxlen="50" class="form-control"  ng-model="addskill.Name" ng-blur="checkSkillData();"   placeholder="Skill Name">
                <div class="error-holder" ng-if="ShowSkillNameError != ''" ng-bind="ShowSkillNameError" ></div>
            </div>
            <div class="form-group with-border">
                <label class="well-title">Select Category</label>
                <!--<input type="text" class="form-control"  value="Cricket" placeholder="Category name">-->

                <tags-input ng-model="addskill.category" 
                            placeholder="Select Category"
                            id="MergecategoryAdd"
                            display-property="Name" 
                            max-tags='1'
                            replace-spaces-with-dashes="false"
                            add-from-autocomplete-only="true"
                            on-tag-added="tagAdded_merge_category($tag)"
                            on-tag-removed="tagRemoved_merge_category($tag)">
                    <auto-complete source="loadTags_merge_category($query)"
                                   min-length="1"
                                   load-on-focus="false"
                                   load-on-empty="true"
                                   max-results-to-show="32"
                                   highlight-matched-text=true
                                   ></auto-complete>
                </tags-input>

                <div class="error-holder"><span class="error-text">Please select category from drop down</span></div>
            </div>
            <div class="form-group with-border">
                <label class="well-title">Select Subcategory</label>
                <!--<input type="text" class="form-control"  value="Bowling" placeholder="Subcategory name">-->
                <tags-input ng-model="addskill.subcategory" 
                            placeholder="Select Subcategory"
                            display-property="Name" 
                            replace-spaces-with-dashes="false"
                            add-from-autocomplete-only="true"
                            on-tag-added="tagAdded_merge_subcategory($tag)"
                            on-tag-removed="tagRemoved_merge_subcategory($tag)">
                    <auto-complete source="loadTags_merge_subcategory($query)"
                                   load-on-focus="true"
                                   load-on-empty="true"
                                   max-results-to-show="32"
                                   highlight-matched-text=true
                                   ></auto-complete>
                </tags-input>
                 <div class="error-holder"><span class="error-text">Please select subcategory from drop down</span></div>
            </div>
            <h4 class="well-title">Icon</h4>
            <div class="well well-sm">
                <img style="display: none" class="upload-btn-loader" ng-src="<?php echo base_url('assets/admin/img/loader.gif') ?>">
                <div class="upload-btn">                         
                    <span ng-if="currentData.ImageName == '' "class="upload-btn-show" template="commentTemplate" fine-uploader upload-destination="api/upload_image" unique-id="1" image-type="skill" section-type="skill" upload-extensions="jpeg,jpg,gif,png,JPEG,JPG,GIF,PNG" title="Attach a Photo"></span>
                    <div ng-if="currentData.ImageName !== ''">
                        <span class="up-icon">
                            <img  width='14px' class='img-category-full'  media_name='currentData.ImageName' media_type='IMAGE' ng-src='<?php echo IMAGE_SERVER_PATH; ?>upload/skill/220x220/{{currentData.ImageName}}'>                          
                        </span>
                        <span class="up-text after-up" >
                            <span ng-bind="currentData.OriginalName"></span>
                            <span>
                                <a class="closeIcn" ng-click="delete_skill_image()">
                                    <svg class="svg-icons" width="10px" height="10px">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="<?php echo ASSET_BASE_URL.'admin/img/sprite.svg#closeIcn' ?>"></use>
                                    </svg> 
                                </a>
                            </span>
                        </span>
                    </div>
                </div> 
            </div>

            <h4 class="well-title">Similar Skill</h4>
            <div class="well well-sm suggested-bolck ">  
                <div class="well-content">
                    <ul class="skill-listing similar-skill">
                        <li  ng-repeat="similarSkill in similarSkill" ng-click="SelectsimilarSkill(similarSkill);
                             " ng-class="{'active': similarSkill.IsSelecte}">
                            <span class="endorse-item-name">
                                <span ng-if="similarSkill.ParentCategorName != ''" ng-bind="similarSkill.ParentCategorName"></span>
                                <span ng-if="similarSkill.CategoryName != ''" ng-bind="similarSkill.CategoryName"></span>
                                <span  ng-if="similarSkill.Name != ''" ng-bind="similarSkill.Name"></span>
                            </span>
                        </li>
                    </ul>

                    <div class="clearfix"></div>
                    <div class="form-group with-border m-t-10 withAdd-btn max-w404 similarskill-tag">
                        <label class="label">&nbsp;</label>
                        <!--<input type="text" placeholder="Type skill name here..." class="form-control " >-->

                        <tags-input ng-model="similarskill_search" 
                                    placeholder="Type skill name here..."
                                    enforce-max-tags
                                    display-property="Name" 
                                    replace-spaces-with-dashes="false"
                                    replace-spaces-with-dashes="false"
                                    add-from-autocomplete-only="true"
                                    on-tag-added="tagAdded_similar_skill($tag)"
                                    on-tag-removed="tagRemoved_similar_skill($tag)"
                                    template="tag-template">
                            <auto-complete source="load_similar_skill($query)"
                                           min-length="1"
                                           load-on-focus="false"
                                           load-on-empty="true"
                                           max-results-to-show="32"
                                           template="autocomplete-template123"
                                           ></auto-complete>
                        </tags-input>
                        <script type="text/ng-template" id="tag-template">
                            <div class="tag-template">
                            <span ng-if="data.ParentCategorName != ''" ng-bind="data.ParentCategorName"></span>
                            <span ng-if="data.CategoryName != ''" ng-bind="data.CategoryName"></span>
                            <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                            </div>
                        </script>
                        <script type="text/ng-template" id="autocomplete-template123">
                            <div class="autocomplete-template">
                            <div class="right-panel">

                            <span ng-if="data.ParentCategorName != ''" ng-bind="data.ParentCategorName"></span>
                            <span ng-if="data.CategoryName != ''" ng-bind="data.CategoryName"></span>
                            <span ng-if="data.Name != ''" ng-bind="data.Name"></span>

                            </div>
                            </div>
                        </script>

                        <a class="add-btn" ng-click="AddSimilierSkill();">ADD</a>
                        <div class="error-holder"><span>Error</span></div>
                    </div>
                </div>
                <div class="well-footer" ng-if="addskill.SkillID != ''">
                    <a id="suggestMore" class="button button-link" ng-click="getsimilarSkill();">SUGGEST SIMILAR SKILLS</a>
                </div>
            </div>
            <div class="btn-toolbar btn-toolbar-right">
                <a class="button button-link" href="<?php echo base_url('admin/skill') ?>" >Cancel</a>
                <button class="button"  ng-click="SaveSkill();">Update</button>
            </div>
        </div>
    </div>
    </div>
</section>