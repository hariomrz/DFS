!(function () { 
  'use strict';
  app.controller('PopupController', ['$scope', '$sce','$log', '$window', 'appInfo', 'CommonHttpService','$q', function ($scope, $sce,$log, $window, appInfo, CommonHttpService,$q) {
      var PopupCtrl = this;

      $scope.totalRecord = 0;
      $scope.currentPage = 1,
      $scope.numPerPage = 10,//pagination,
      $scope.PageNo = 0;
      // PopupCtrl.PageSize = 10;
      $scope.noOfObj = pagination,
      $scope.maxSize = pagination_links;
      $scope.orderByField = 'CreatedDate';
      $scope.reverseSort = 'DESC';
      PopupCtrl.isCreatePopupProcessing = false;      
      PopupCtrl.totalPopups = 0;
      PopupCtrl.showError = false;
      PopupCtrl.errorMessage = null;
      PopupCtrl.searchKeyword = '';
      PopupCtrl.CurrentPopupID = {};
      PopupCtrl.PopupAction = 'add';
      PopupCtrl.isPreview = false;
      PopupCtrl.MediaGUID = "";
      PopupCtrl.NewPostContent = "";
      PopupCtrl.ImageName = "";
      PopupCtrl.ImageServerPath = "";
      PopupCtrl.OriginalName = "";
      var CreatePopupDefault = {
        AnnouncementPopupID: '',
        PopupTitle: '',
        PopupContent: '',
        CreatorName: '',
        CreatedDate:'',
        PublishedDate:'',
        IsImageData:'0',
        PopupContentRadio:2,
        Status: '2'        
      };
      PopupCtrl.createPopup = angular.copy(CreatePopupDefault);
      
      //PopupCtrl.rolename = '';

      PopupCtrl.popupList = function () {
        showLoader();      
                         
        var reqData = {    
          PageSize:$scope.numPerPage,     
          PageNo:$scope.currentPage,
          SortBy: $scope.orderByField,
          OrderBy: $scope.reverseSort,
          SearchKeyword: PopupCtrl.searchKeyword,
          AdminLoginSessionKey: angular.element('#AdminLoginSessionKey').val()
        }
        
        //Call to get list of Popups
        CommonHttpService.CallPostApi(appInfo.serviceUrl + 'admin_api/announcementpopup/list', reqData, function (successResp) {
                    
          var response = successResp.data;
          PopupCtrl.popupData = [];            
          if (response.ResponseCode == 200) { 
            //Push data into Controller in view file
            $scope.noOfObj = response.TotalRecords;
            $scope.totalRecord = $scope.noOfObj;
            //If no of records equal 0 then hide
            if ($scope.noOfObj == 0)
            {
                $('.download_link,#selectallbox').hide();
                $('#userlist_table').append('<tr id="noresult_td"><td colspan="7"><div class="no-content text-center"><p>No popup created till now. You may create a new one by clicking \'Create Popup\' button above.</p></div></td></tr>');
                // $('.result_message').show();
                $('.simple-pagination').hide();
            }
            PopupCtrl.popupData  = response.Data
          } else if (checkApiResponseError(response)) {
            ShowWentWrongError();
          } else {
            ShowErrorMsg(response.Message);
          }
          hideLoader();
        }, function (error) {
          hideLoader();
        });        
      };

      //Get no. of pages for data
      PopupCtrl.numPages = function () {
            return Math.ceil($scope.totalRecord / $scope.numPerPage);
      };

      //Call function for get pagination data with new request data
    /* $scope.$watch(function(scope) { return (PopupCtrl.currentPage + PopupCtrl.numPerPage) }, function () {
        if (PopupCtrl.currentPage == 1 && $scope.PageNo == 0) {
          //Make request data parameter for users listing
          PopupCtrl.PageNo = 0;//$scope.currentPage;
        } else {
          // $scope.PageNo = ((PopupCtrl.currentPage - 1) * PopupCtrl.numPerPage)
          PopupCtrl.PageNo = PopupCtrl.currentPage;

          PopupCtrl.popupList();        
        }
        // if($scope.PageNo > 1)
        // {
        //   $scope.PageNo = $scope.PageNo+1; 
        //   PopupCtrl.popupList();        
        // }
        //SetUserStatus($('#hdnUserStatus').val());
    }); */
     /* Here we check if current page is not equal 1 then set new value for var begin */
      /*var PageNo = '';
      if (PopupCtrl.currentPage == 1) {
        //Make request data parameter for users listing
        PageNo = 0;//$scope.currentPage;
      } else {
        PageNo = ((PopupCtrl.currentPage - 1) * PopupCtrl.numPerPage)
      }*/

      
      PopupCtrl.isPopupPicUploading = false;
      PopupCtrl.MediaGUID = '';
      PopupCtrl.uploadPopupPicture = function(file, errFiles) {
          var c = 0;
          var cc = 0;
          var serr = 1;          
          if (!(errFiles.length > 0) && file) {

              var patt = new RegExp("^image");
              PopupCtrl.isPopupPicUploading = true;
              var paramsToBeSent = {
                  Type: 'popup',
                  DeviceType: 'Native',
                  ModuleID: $('#module_id').val(),
                  ModuleEntityGUID: $('#entity_id').val(),
                  qqfile: file
              };
              if (!patt.test(file.type)) {
                  showResponseMessage('Only image files are allowed.', 'alert-danger');
                  return false;
              }

              $('.cropit-image-loaded').css('background', '');
              $('.cropit-image-background').attr('src', '');
              //showProfileLoader();
              
              PopupCtrl.OriginalName='';
              CommonHttpService.CallUploadFilesApi(
                  paramsToBeSent,
                  appInfo.serviceUrl + 'api/upload_image',
                  function (response) {
                                   
                    var responseJSON = response.data;
                    if (responseJSON.ResponseCode === 200) {
                      if (responseJSON.Message == 'Success') {
                        //save MEDIAGUID
                        PopupCtrl.MediaGUID = responseJSON.Data.MediaGUID;
                        PopupCtrl.ImageName = responseJSON.Data.ImageName;
                        PopupCtrl.ImageServerPath = responseJSON.Data.ImageServerPath;
                        PopupCtrl.OriginalName = responseJSON.Data.OriginalName;
                        // $("#PopupContentDiv").removeClass('post-content').addClass('text-center');
                      } else {
                        ShowErrorMsg(responseJSON.Message);
                      }
                    } else {
                      ShowErrorMsg(responseJSON.Message);
                    }
                    PopupCtrl.isPopupPicUploading = false;
                    hideLoader();
                  },
                  function (response) {
                    PopupCtrl.isPopupPicUploading = false;
                    hideLoader();
                  },
                  function (evt) {
                    evt
                  });
                } else {
                  hideLoader();
                  PopupCtrl.isPopupPicUploading = false;
                  //            ShowErrorMsg(errFiles[0].$errorMessages);
                }
      };

      PopupCtrl.validateFileSize = function (file, config) {
          var defer = $q.defer();
          var isResolvedToFalse = false;
          var fileName = file.name;
          var mediaPatt = new RegExp("^image|video");
          var videoPatt = new RegExp("^video");
          config = (config) ? config : {};

          if (config.validExtensions) {
            var validExtensions = (config.validExtensions.constructor === Array) ? config.validExtensions : ['jpeg', 'jpg', 'gif', 'png', 'JPEG', 'JPG', 'GIF', 'PNG']; //array of valid extensions
            var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
            if ($.inArray(fileNameExt, validExtensions) == -1) {
              ShowErrorMsg('File type ' + fileNameExt + ' not allowed.');
              defer.resolve(false);
              defer.promise;
              isResolvedToFalse = true;
            }
          }

          var maxFileSize = (config.maxFileSize) ? config.maxFileSize : 4194304 /*4194304 Bytes = 4Mb*/;
          if (videoPatt.test(file.type)) {
            maxFileSize = (config.maxFileSize) ? parseInt(config.maxFileSize) : 41943040 /*41943040 Bytes = 40 Mb*/;
            if (file.size > maxFileSize) { // if video size > 41943040 Bytes = 40 Mb
              file.$error = 'size';
              file.$error = 'Size Error';
              ShowErrorMsg(file.name + ' is too large.');
              defer.resolve(false);
              isResolvedToFalse = true;
            }
          } else {
            if (parseInt(file.size) > maxFileSize) { // if image/document size > 4194304 Bytes = 4 Mb
              file.$error = 'size';
              file.$error = 'Size Error';
                // file.$errorMessages = file.name + ' is too large.';
                // ShowErrorMsg(file.name + ' is too large.');
              ShowErrorMsg(file.name + ' is too large.');
              defer.resolve(false);
              isResolvedToFalse = true;
            }
          }

          if (!isResolvedToFalse) {
            defer.resolve(true);
          }
          return defer.promise;
      }

      PopupCtrl.sanitizeMe = function(text,isTitle) {console.log('text');
          // text = $.parseHTML(text);   alert(text);       
          $("#AnnouncementPopup").find('i').css("background", "none" );
          
          // $(text).find( "<i>" ).css( "background", "none" );
//          return text = $scope.textToLink(text);
          if(PopupCtrl.OriginalName && !isTitle && PopupCtrl.createPopup.PopupContentRadio==2){
            if(PopupCtrl.createPopup.ImageLink)
            {
              text='<img src="'+PopupCtrl.ImageServerPath+'/'+PopupCtrl.ImageName+'">';
              text = '<a href="'+PopupCtrl.createPopup.ImageLink+'" target="_blank">'+text+'</a>';              
            }
            else
            {
              text='<img src="'+PopupCtrl.ImageServerPath+'/'+PopupCtrl.ImageName+'">';               
            }
            PopupCtrl.NewPostContent = text;
          }          
          text  = PopupCtrl.parseAnchor(text);
          text = $scope.textToLink(text);
          return text;
            // return $sce.trustAsHtml(text)
      };
      
      PopupCtrl.parseAnchor = function(contentToParse) {
        if ( contentToParse ) {
          var taggedContentRegex = /<a\s+(?:[^>]*?\s+)?href=(["'])(.*?)\1/gm,
          matchedInfo;
          while ( ( matchedInfo = taggedContentRegex.exec( contentToParse ) ) ) {
            if ( !/^https?:\/\//i.test( matchedInfo[2] ) ) {
              var url = 'http://' + matchedInfo[2];
              contentToParse = contentToParse.replace('href="' + matchedInfo[2] + '"', 'target="_blank" href="' + url + '"');
//              contentToParse = contentToParse.replace(matchedInfo[2], url);
            } else {
              var url = matchedInfo[2];
              contentToParse = contentToParse.replace('href="' + matchedInfo[2] + '"', 'target="_blank" href="' + url + '"');
            }
          }
          return contentToParse;
        } else {
          return '';
        }
      }

      PopupCtrl.SetPopupDetail = function(popupData,action){
        PopupCtrl.PopupAction = action;        
        PopupCtrl.createPopup = popupData;
        // PopupCtrl.CurrentPopupID = popupData.PopupID;  
             
        $scope.PreviewPopup = popupData;
      };
      //active deactive
      PopupCtrl.toggleActive = function(action){
        if(action=='Active')      
        {
          var msg = "This popup will start showing on user-side if you mark it Active. Continue?";
        }
        else
        {
          var msg = "This popup will stop showing on user-side if you mark it Inactive. Continue?";
        }
	      showAdminConfirmBox('Mark '+action,msg,function(e){
	                if(e)
	                {
	                	PopupCtrl.createPopup.Status = (PopupCtrl.createPopup.Status == '1') ? '2' : '1';
    				        //send request        
    				        PopupCtrl.callToApi(PopupCtrl.createPopup,action);
	                }
	            });                	
      };
      
      PopupCtrl.deletePopup = function(){     
	      showAdminConfirmBox('Delete Popup','Are you sure you want to delete this popup?',function(e){
		                if(e)
		                {
		                	PopupCtrl.createPopup.Status = '3';
					        //send request					        
					        PopupCtrl.callToApi(PopupCtrl.createPopup,'delete');
		                }
		            });           
        
      };

      PopupCtrl.savePopup = function (CreatePopupForm,isPreview) {     	      	
        if (CreatePopupForm.$submitted || CreatePopupForm.$valid) { 
        	$scope.isPreview  = true;						
    			if(!isPreview)
    			{
    	        	showAdminConfirmBox('Save Popup','Are you sure you want to create this popup?',function(e){
    			                if(e)
    			                {		                	
    					          		PopupCtrl.callToApi(CreatePopupForm,'save'); 
    			                }
    			            }); 				
    			}
        }
        else {
        	PopupCtrl.isCreatePopupProcessing = false;
        }
      };


      $scope.textToLink = function (inputText, onlyShortText,count) {
          if (typeof inputText !== 'undefined' && inputText !== null) {
            inputText = inputText.toString();
            inputText=inputText.replace(new RegExp('contenteditable', 'g'), 'contenteditabletext');
            var replacedText, replacePattern1, replacePattern2, replacePattern3;
            inputText = inputText.replace(new RegExp('contenteditable', 'g'), "contenteditabletext");
            replacedText = inputText.replace("<br>", " ||| ");
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = replacedText.replace(replacePattern1, function ($1) {
              var link = $1;
              var link2 = '';
              var href = $1;
              if (link.length > 35) {
                link2 = link.substr(0, 25);
                link2 += '...';
                link2 += link.slice(-5);
                link = link2;
              }
              var youtubeid = $scope.parseYoutubeVideo($1);
              if (youtubeid) {
                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
              } else {
                return href;
              }
            });
            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            replacedText = replacedText.replace(replacePattern2, function ($1, $2) {

              var link = $1;
              var link2 = '';
              var href = $1;
              if (link.length > 35) {
                link2 = link.substr(0, 25);
                link2 += '...';
                link2 += link.slice(-5);
                link = link2;
              }
              href = href.trim();
              var youtubeid = $scope.parseYoutubeVideo($1);
              if (youtubeid) {
                return '<iframe width="420" height="315" src="https://www.youtube.com/embed/' + youtubeid + '" frameborder="0" allowfullscreen></iframe>';
              } else {
                return href;
              }

            });
            //Change email addresses to mailto:: links.
            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            replacedText = replacedText.replace(replacePattern3, '<a class="chat-anchor" href="mailto:$1">$1</a>');
            replacedText = replacedText.replace(" ||| ", "<br>");
            replacedText = checkTaggedData(replacedText);
            var repTxt = removeTags(replacedText);
            var totalwords = 200;
            if($('#IsForum').length>0)
            {
              totalwords = 80;
              if(count)
              {
                totalwords = count;
              }
            }

            if($scope.IsSinglePost)
            {
              replacedText = $sce.trustAsHtml(replacedText);
              return replacedText
            }

//            if ( repTxt && ( repTxt.length > totalwords ) ) {
//              if (onlyShortText) {
//                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... </span>';
//              } else {
//                replacedText = '<span class="show-less">' + smart_substr(totalwords, replacedText) + '... <a onclick="showMoreComment(this);">See More</a></span><span class="show-more">' + replacedText + '</span>';
//              }
//            }
            replacedText = $sce.trustAsHtml(replacedText);
            return replacedText
          } else {
            return '';
          }
        }


$scope.getHighlighted = function (str) {
          var advancedSearchKeyword = angular.element('#advancedSearchKeyword').val();
          if ( advancedSearchKeyword ) {

              if ( !advancedSearchKeyword ) {
                advancedSearchKeyword = $('#srch-filters').val();
              }

              if (typeof str === 'undefined') {
                  str = '';
              }
              if (str.length > 0 && advancedSearchKeyword.length > 0) {
                  str = str.replace(new RegExp(advancedSearchKeyword, 'gi'), "<span class='highlightedText'>$&</span>");
              }
              return str;
          } else {
              return str;
          }
      }

    $scope.parseYoutubeVideo = function (url) {
            var videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if (videoid != null) {
                return videoid[1];
            } else {
                return false;
            }
        }

        function checkTaggedData(replacedText) {
          if (replacedText) {
            var regex = /<a\shref[\s\S]*>([\s\S]*)<\/a>/g,
                matched,
                highLightedText;
            if ((matched = regex.exec(replacedText)) !== null) {
              replacedText = replacedText.replace(matched[0], '{{:*****:}}');
              replacedText = $scope.getHighlighted(replacedText);
              if ( matched[1] ) {
                highLightedText = $scope.getHighlighted(matched[1]);
                matched[0] = matched[0].replace(matched[1], highLightedText);
              }
              replacedText = replacedText.replace('{{:*****:}}', matched[0]);
              return replacedText;
            } else {
              return $scope.getHighlighted(replacedText);
            }
          }
        }
        
        function removeTags(txt) {
          if (txt) {
            var rex = /(<([^>]+)>)/ig;
            return txt.replace(rex, "");
          } else {
            return txt;
          }
        }


      PopupCtrl.callToApi = function(CreatePopupForm,Action){
        if(PopupCtrl.NewPostContent && PopupCtrl.createPopup.PopupContentRadio == 2)
        {
          PopupCtrl.createPopup.PopupContent = PopupCtrl.NewPostContent;          
          PopupCtrl.createPopup.IsImageData = '1';
        }
        PopupCtrl.createPopup.PopupContent  = PopupCtrl.parseAnchor(PopupCtrl.createPopup.PopupContent);

        PopupCtrl.isCreatePopupProcessing = true;
        CommonHttpService.CallPostApi(appInfo.serviceUrl + 'admin_api/announcementpopup/save', PopupCtrl.createPopup, function (successResp) {
            var response = successResp.data;
            if (response.ResponseCode == 200) {      
              //refresh list on front end
              PopupCtrl.popupList();
              ShowSuccessMsg("Information processed successfully.");                                  
              PopupCtrl.isCreatePopupProcessing = false;
              if(Action == 'save')
              {
		          setTimeout(function() { $window.location.href = appInfo.serviceUrl+'admin/popup'; }, 1000);
              }
            } else {                            
              ShowErrorMsg(response.Message);
              PopupCtrl.isCreatePopupProcessing = false;
            }
          }, function (error) {
            PopupCtrl.isCreatePopupProcessing = false;
            // showResponseMessage('Something went wrong.', 'alert-danger');
          });
      };      

      PopupCtrl.removeImage = function(){
          PopupCtrl.MediaGUID = '';
          PopupCtrl.ImageName = '';
          PopupCtrl.ImageServerPath = '';
          PopupCtrl.OriginalName = '';
      };

    }]);
})();