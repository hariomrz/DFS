<div class="bread-crumbs">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <ul class="bread-crumb-nav">
                    <li><span>Blog</span></li>
                    <li>/</li>
                    <li><span><?php if(!empty($blog_guid)){echo lang('Edit');}else{ echo "Create";}?></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<section class="main-container">
<div class="container">
  <div class="row-flued">
    <h2><?php if(!empty($blog_guid)){echo "Edit Blog";}else{ echo "Create Blog";}?></h2>
      <div class="add-category m-t">
      	<div class="panel">
      		<div class="panel-body">
		        <div class="category-left" data-ng-controller="blogController" id="blogController">
		          <table class="addcategory-table rolestable" ng-init="initialize();<?php if(!empty($blog_guid)){echo "details('$blog_guid')";}?>">
		            <tr>
		              <td class="valign">
		                  <label class="label"><?php echo lang('Title');?><span class="required">*</span></label>
		              </td>
		              <td>	
		              <div class="form-group">	                     
	                        <div class="text-field large" data-type="focus">
	                            <input  type="text" ng-model='Blog.Title' name="title" id="title" value="">
	                        </div> 
	                        <div class="error-holder usrerror" ng-if="Error.error_blog_title">{{Error.error_blog_title}}</div>
                        </div>
		              </td>
		            </tr>
		            <tr>
		               <td class="valign">
		                  <label class="label"><?php echo lang('Description');?><span class="required">*</span></label>
		              </td>
		               <td>
		                  <div class=" marb-20">
			                  <div class="form-group" data-ng-controller="SummernoteController">
			                    <summernote ng-model="Blog.Description"   id="description"></summernote>
			                  </div> 
		                    <div class="error-holder usrerror" ng-if="Error.error_blog_description">{{Error.error_blog_description}}</div>
		                  </div>
		               </td>
		               
		            </tr>
		            
		            <tr> 
		              <td class="valign">
		                  <label class="label"><?php echo lang('upload_media');?></label>
		              </td>
		              <td>
		                <div class="media-upload">
		                  <ul>
		                    <li>
		                     <div class="upload-panel">
		                       <div class="media-wrap">
		                         <div class="upload-icon"><img src="<?php echo base_url();?>assets/admin/img/upload-img.png"></div>
		                         <div class="upload-name">
		                          <span class="bold-text"><?php echo lang('choose_photo');?></span>
		                          <span><?php echo lang('upload_photo_desc');?></span>
		                        </div>
		                         <div class="button btn-upload"><div id="blog_photo"><?php echo lang('Upload');?></div></div>
		                      </div>
		                       <span class="or"></span>
		                       <div class="media-wrap video-wrap">
		                       <span><?php echo lang('upload_video');?></span>
		                        <section class="vid-wrap">
		                           <div class="overflow">
		                         <div class="upload-icon"><img src="<?php echo base_url();?>assets/admin/img/link-icon.png"></div>
		                         <div class="upload-name">
		                          <span class="bold-text"><?php echo lang('choose_video');?></span>
		                          <span><?php echo lang('upload_videos');?></span>
		                        </div>
		                         <div class="button btn-upload"><div id="blog_video"><?php echo lang('Upload');?></div></div>
		                      </div>
		                          
		                          <span class="or"></span>
		                          <div class="vidup  mTop35">
		                             <div class="upload-name">
		                              <span><?php echo lang('add_youtube');?></span>
		                              <div>
		                                  <div class="text-field large" data-type="focus">
		                                    <input type="text" ng-model='Blog.youtube' name="embed_code" id="embed_code" value="" placeholder="Add youtube URL">
		                                  </div>
		                              </div>
		                             </div>
		                             <div class="btn-upload"><button class="button" ng-click="add_youtube_thumb();"><?php echo lang('Add');?></button></div>
		                          </div> 
		                        </section>  
		                      </div>
		                     
		                      </div>
		                    </li> 
		                    <li>
		                      <ul class="attached-media" >
		                       
		                      </ul>                      
		                    </li>		                    
		                  </ul> 
		                </div>
		              </td>
		            </tr>
		             <tr>
		              <td>&nbsp;</td>
		              <td>&nbsp;</td>
		              </tr>
		            <tr>
		              <td>&nbsp;</td>
		              <td>
		              	<div class="button-group clearfix">
			                <div class="float-right relative">
			                     <div class="btnloader hide"><div id="ImageThumbLoader" class="uplaodLoader">
			                      <img src="http://localhost/527-schollyme/assets/admin/img/loading22.gif" id="spinner"></div>
			                   </div>
			                 <button type="submit" id="btnpublish" class="btn btn-primary" <?php if(!empty($blog_guid)){ ?> ng-click="update_blog('PUBLISHED','<?php echo $blog_guid;?>');"<?php } else { ?> ng-click="save_blog('PUBLISHED');" <?php }?>><?php echo lang('publish');?></button>
			                </div>
			                <div class="float-right m-r10 relative">
			                  <div class="btnloader hide">
			                    <div id="ImageThumbLoader" class="uplaodLoader">
			                      <img src="<?php echo base_url();?>assets/admin/img/loading22.gif" id="spinner">
			                    </div>
			                  </div>
			                <button class="btn btn-primary" <?php if(!empty($blog_guid)){ ?> ng-click="update_blog('DRAFT','<?php echo $blog_guid;?>');"<?php } else { ?> ng-click="save_blog('DRAFT');" <?php }?> ><?php echo lang('save_as_draft');?></button>
			                </div>
			                <a href="<?php echo base_url()?>admin/blog" class="btn btn-default btn-link float-right m-r10"><?php echo lang('Cancel');?></a> 
		                </div>
		              </td>
		            </tr>
		          </table>
		        </div>
         </div>
        </div>

        <div class="clearfix"></div>
      </div>
  </div>
</div>
</section>  
    
