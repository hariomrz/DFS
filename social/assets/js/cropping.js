/* Jcrop  */

//function initFineUploader(element_id, type, unique_id){
//	new qq.FineUploaderBasic({
//		multiple: false,
//		autoUpload:true,
//		button:$("#"+element_id)[0],
//		request: {
//			endpoint:site_url+"api/upload_image",
//			params:{
//				Type:type,
//				unique_id: unique_id,
//				need_cropping: true,
//				DeviceType:'Native'
//			}
//		},
//		
//		validation: {
//			allowedExtensions: ['jpeg', 'jpg', 'gif', 'png','JPEG', 'JPG', 'GIF', 'PNG'],
//			sizeLimit: 4194304 /* 4mb */
//		},
//		
//		callbacks: {
//			onError: function(){ console.log(2); },
//			onUpload: function(id, fileName) {
//				$('.cropping-btn').hide();
//				$('.cropping-btn-loader').show();
//			},
//			
//			onProgress: function(id, fileName, loaded, total) {
//				c = parseInt($('#image_counter').val());
//				c = c+1;
//				// $('#image_counter').val(c);
//			},
//			
//			onComplete: function(id, fileName, responseJSON) {
//
//				//console.log(responseJSON);
//				$('.cropping-btn').show();
//				$('.cropping-btn-loader').hide();
//
//				if (responseJSON.result=='success') 
//				{
//					$('.image_for_cropping').attr('src',responseJSON.file_path);
//					crop.InitializeJcrop($('.image_for_cropping'),type, unique_id );
//					$('#bannerImage').modal('show');
//
//					// $("#ShowSelectedItem").html('<img width="118" src="'+responseJSON.thumb+'">');
//				} else {
//					alertify.error(responseJSON.reason);
//				}
//			},
//			
//			onValidate:function(b) {
//				var validExtensions=['jpeg','jpg','gif','png','JPEG','JPG','GIF','PNG']; /* array of valid extensions */
//				var fileName = b.name;
//				var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
//				if($.inArray(fileNameExt, validExtensions) == -1)
//				{
//					alertify.error("Allowed file types only jpeg, jpg, gif and png.");       
//					$(".alertify-message").css('padding-bottom','10px');
//					$(".alertify-buttons").css('float','none');
//					return false;
//				}
//				
//				if(b.size>4194304)
//				{
//					alertify.error('Image file should be less than 4 MB');
//					$(".alertify-message").css('padding-bottom','10px');
//					$(".alertify-buttons").css('float','none');
//				}
//			}
//		}
//	});
//
//}

var Cropping =  function (){
	// this.target_id = target_id;
	self_c = this;
};

$.extend(Cropping.prototype,{

	self_c          : {},
	cropping_ratio  : { 
						'profilebanner'  :{'width':1200, 'height': 200},
						'group_banner'   :{'width':1200, 'height': 200},
						'article_banner' :{'width':1200, 'height': 200},
						'profile_image'  :{'width':50, 'height':50} ,
					 },
	target          : '',
	aspect_ratio    : 2.87,
	x1              : 0,
	y1              : 0,
	x2              : 0,
	y2              : 0,
	file_path       : '',
	resp_img_width  : 0,
	resp_img_height : 0,
	upload_type     : '',
	unique_id : '',

	InitializeJcrop:function(target, type, unique_id){
		self_c.target = target ;
		var dimensions =  self_c.cropping_ratio[type];
		var width      = dimensions.width;
		var height     = dimensions.height;

		$(target).Jcrop({
							boxWidth    : 510,   //Maximum width you want for your bigger images
							boxHeight   : 400,  //Maximum Height for your bigger images
							aspectRatio : width/height, 
							onSelect    : Cropping.prototype.GetSelectedCoordinates,
							minSize     : [width,height],
							setSelect   :   [ 0, 0, width, height ],
							onRelease   : Cropping.prototype.GetSelectedCoordinates,
							allowSelect : false, 
						});
		self_c.upload_type = type;
		self_c.unique_id   = unique_id;
		//console.log(type);
	},

	DestroyJcrop : function(){
		JcropAPI = $(self_c.target).data('Jcrop');
		JcropAPI.destroy();
	},

	GetSelectedCoordinates:function(c){
		self_c.x1              = c.x;
		self_c.y1              = c.y;
		self_c.x2              = c.x2;
		self_c.y2              = c.y2;
		self_c.file_path       = $(self_c.target).attr('src');
		self_c.resp_img_width  = $(self_c.target).width();
		self_c.resp_img_height = $(self_c.target).height();
		
	},

	SendDataForCropping:function(){
		var data         = {};
		data.x1          = self_c.x1 ;
		data.x2          = self_c.x2;
		data.y1          = self_c.y1;
		data.y2          = self_c.y2;
		data.img_width   = self_c.resp_img_width ;
		data.img_height  = self_c.resp_img_height;
		data.file_path   = self_c.file_path ;
		data.upload_type = self_c.upload_type;
		data.unique_id   = self_c.unique_id;

		//console.log(data);
	    $.ajax({
			url  : site_url + 'uploadimage/create_cropped_image',
			data : data,
			type : "POST",
			success: function(response){
				
				self_c.DestroyJcrop();
				
				var res = JSON.parse(response);
				
				setCoverImage(self_c.upload_type, res['1200x300']);				
				
			}
		});		

	},

	CancelCropping : function(){
		$('#bannerImage').modal('hide');
		self_c.DestroyJcrop();
	}

});


function setCoverImage(upload_type, url){
	$('#bannerImage').modal('hide');
	angular.element(document.getElementById('UserProfileCtrl')).scope().changeProfileCover(url);
	setTimeout(function(){
		$('#imgCoverAdj').imagefill();
	},500);
}

$(document).ready(function() {
	crop = new	Cropping();	
});
