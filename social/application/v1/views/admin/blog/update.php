
<div class="row-flued">
<h2>Upload Blog</h2>
      <div class="add-category mTop35">
        <div class="category-left">
          <table class="addcategory-table rolestable">
            <tr>
              <td class="valign">
                  <label class="label">Title<span class="required">*</span></label>
              </td>
              <td>
                    <div>
                        <div class="text-field large" data-type="focus">
                            <input type="text" ng-model='blog.title' name="title" id="title" value="">
                        </div>
                        <div class="clearfix">&nbsp;</div>
                        <div class="error-holder usrerror">{{error_name}}</div>
                        <div class="clearfix">&nbsp;</div>
                    </div>
              </td>
            </tr>
            <tr>
               <td class="valign">
                  <label class="label">ARTICLE<span class="required">*</span></label>
              </td>
               <td>
                  <div class="marb-20">
                  <div class="form-group" data-ng-controller="SummernoteController">
                    <textarea data-summernote="" rows="10" data-placeholder="123">Start Writing... You can use the tools  above to format your text and add or embed images &amp; videos inline your article. If you embed any media hosted elsewhere, it’ll show only as long as it</textarea>
                  </div>
                  </div>
               </td>
               
            </tr>
            
            <tr> 
              <td class="valign">
                  <label class="label">Upload Media<span class="required">*</span></label>
              </td>
              <td>
                <div class="media-upload">
                  <ul>
                    <li>
                     <div class="upload-panel">
                       <div class="media-wrap">
                         <div class="upload-icon"><img src="../../assets/admin/img/upload-img.png"></div>
                         <div class="upload-name">
                          <span class="bold-text">Choose Photos</span>
                          <span>Upload photos from, you can add multiple</span>
                        </div>
                         <div class="button btn-upload"><input type="file" name="image-uplaod">Upload</div>
                      </div>
                       <span class="or"></span>
                       <div class="media-wrap video-wrap">
                       <span>Upload video</span>
                        <section class="vid-wrap">
                           <div class="overflow">
                         <div class="upload-icon"><img src="../../assets/admin/img/link-icon.png"></div>
                         <div class="upload-name">
                          <span class="bold-text">Choose Video</span>
                          <span>Upload Videos</span>
                        </div>
                         <div class="button btn-upload"><input type="file" name="image-uplaod">Upload</div>
                      </div>
                          <!--<div class="vidup">
                             <div class="upload-name">
                              <span>Upload</span>
                              <div>
                                  <div class="text-field large" data-type="focus">
                                    <input type="file" ng-model='blog.embedcode' name="embed_code" id="embed_code" value="" placeholder="Upload">
                                  </div>
                              </div>
                            </div>
                             <div class="btn-upload"><button class="button">Add</button></div>
                          </div>-->
                          <span class="or"></span>
                          <div class="vidup  mTop35">
                             <div class="upload-name">
                              <span>Add youtube URL</span>
                              <div>
                                  <div class="text-field large" data-type="focus">
                                    <input type="text" ng-model='blog.embedcode' name="embed_code" id="embed_code" value="" placeholder="https://www.youtube.com/embed/nCD2hj6zJEc">
                                  </div>
                              </div>
                             </div>
                             <div class="btn-upload"><button class="button">Add</button></div>
                          </div> 
                        </section>  
                      </div>
                     
                      </div>
                    </li> 
                    <li>
                      <ul class="attached-media">
                       <li> 
                         <a class="smlremove"></a>
                        <figure><img width="270" height="150"  class="img-full" src="../../assets/admin/img/dummy1.jpg"></figure>
                        <span class="radio">
                        <input type="radio" name="coverpic" id="coverpicId1" checked>
                        <label for="coverpicId1">COVER PIC</label>
                        </span>
                       
                      </li>
                      <li> 
                         <a class="smlremove"></a>
                        <figure><img width="270" height="150"  class="img-full" src="../../assets/admin/img/dummy1.jpg"></figure>
                        <span class="radio">
                        <input type="radio" name="coverpic" id="coverpicId1">
                        <label for="coverpicId1">COVER PIC</label>
                        </span>
                       
                      </li>
                      <li> 
                         <a class="smlremove"></a>
                        <figure><img width="270" height="150"  class="img-full" src="../../assets/admin/img/dummy1.jpg"></figure>
                        <span class="radio">
                        <input type="radio" name="coverpic" id="coverpicId1">
                        <label for="coverpicId1">COVER PIC</label>
                        </span>
                       
                      </li>
                      <li> 
                         <a class="smlremove"></a>
                        <figure><img width="270" height="150"  class="img-full" src="../../assets/admin/img/dummy1.jpg"></figure>
                        <span class="radio">
                        <input type="radio" name="coverpic" id="coverpicId1">
                        <label for="coverpicId1">COVER PIC</label>
                        </span>
                       
                      </li>
                     
                  
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
              <div class="float-right relative">
                   <div class="btnloader hide"><div id="ImageThumbLoader" class="uplaodLoader">
                    <img src="http://localhost/527-schollyme/assets/admin/img/loading22.gif" id="spinner"></div>
                 </div>
                 <button type="submit" id="btnpublish">Publish</button>
                </div>
                <div class="float-right m-r10 relative">
                 <div class="btnloader hide"><div id="ImageThumbLoader" class="uplaodLoader"><img src="http://localhost/527-schollyme/assets/admin/img/loading22.gif" id="spinner"></div></div>
                <button class="button">Save as Draft</button>
                </div>
                <a href="javascript:void(0);" class="cancel-link float-right m-r10">Cancel</a> 
              </td>
            </tr>
          </table>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    
    <script>
$(function () {
	mainNav(4);
    $(".responsive-tabs").responsiveTabs();
});

function createModal(tab){
	$('#createModal').on('show.bs.modal',function(){
	  setTimeout(function(){
		if(!$('.note-dialog>div').is(':visible')){
		  $('.modal-create .tab-pane').removeClass('active in');
		  $('.modal-create .nav-tabs li').removeClass('active');
		  $('.modal-create .nav-tabs a[href="#' + tab + '"]').tab('show');
		}
	  },0);
	  if(( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )) {
		$('.note-toolbar .btn').tooltip('destroy');
	  }
	});
}
$('#createModal').on('hide.bs.modal',function(){
  if($('body').find('.modal-backdrop').length<=2){
	$('body').find('.modal-backdrop').not(':first').remove();
  }
});
$('#createModal').on('show.bs.modal',function(){
	$('.note-dialog>div').on('hidden.bs.modal',function(){
	  setTimeout(function(){
		$('body').addClass('modal-open');
	  },0);
	});
	$('.note-dialog>div').modal('hide');
});

</script>