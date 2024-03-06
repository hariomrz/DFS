/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
app.controller('GroupCtrl', function ($scope, $http, $rootScope, $window, $timeout, apiService, $q) {
    $scope.loadTags = function (query) 
    {
        if(query.length>0)
        {
            var req_data = {};
            req_data.LoginSessionKey = $('#AdminLoginSessionKey').val();
            req_data.SearchKeyword = query;
            return $http.post(base_url + 'api/search/user_n_group',req_data).then(function(response, status) {
                var data = [];
                angular.forEach(response.data.Data, function(filterObj , filterIndex) {
                     data.push({'text':filterObj.Name,'ModuleEntityGUID':filterObj.ModuleEntityGUID,'ModuleID':filterObj.ModuleID,'Privacy':filterObj.Privacy}); 
                    if(filterObj.ModuleID==3)
                    {
                          
                    }
                    else
                    {
                        //data.push({'text':filterObj.GroupName,'ModuleEntityGUID':filterObj.GroupGUID,'ModuleID':filterObj.ModuleID});
                    }
                }); 
                return data;
              });    
        }
    }

    $scope.disable_setting = function(type)
    {
        if(type=="discussion")
        {
            if($scope.discussion_check==1)
            {
                $scope.discussion = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
                $scope.discussion_checked = true;
            }
            else
            {
                $scope.discussion = [];
                $scope.discussion_checked = false;
            }
        }
        else if(type=="qa_check")
        {
            if($scope.qa_check==1)
            {
                $scope.question = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
                $scope.qa_checked = true;
            }
            else
            {
                $scope.question = [];
                $scope.qa_checked = false;
            }
        }
        else if(type=="kb_check")
        {
            if($scope.kb_check==1)
            {
                $scope.knowledge_base = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
                $scope.kb_checked = true;
            }
            else
            {
                $scope.knowledge_base = [];
                $scope.kb_checked = false;
            }
        }
        else if(type=="announcements_check")
        {
            if($scope.announcements_check==1)
            {
                $scope.announcements_base = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
                $scope.announcements_checked = true;
            }
            else
            {
                $scope.announcements_base = [];
                $scope.announcements_checked = false;
            }
        }
    }

    $scope.get_group_permission = function()
    {
        reqData = {};
        $scope.knowledge_base = [];
        $scope.announcements_base = [];
        $scope.discussion = [];
        $scope.question = [];
        apiService.call_api(reqData, 'admin_api/configuration/get_group_permission').then(function (response) {
            if (response.ResponseCode == 200) 
            {
                if(response.data.length>0)
                {
                    angular.forEach(response.data, function(filterObj , filterIndex) {
                        if(filterObj.PostTypeLabel=='Article')
                        {
                            $scope.knowledge_base.push({'text':filterObj.Name,'ModuleEntityGUID':filterObj.ModuleEntityGUID,'ModuleID':filterObj.ModuleID});  
                            if(filterObj.Name=='Everyone')
                            {
                                $scope.kb_check = true;
                                $scope.kb_checked = true;
                            }  
                        }
                        else if(filterObj.PostTypeLabel=='Announcements')
                        {
                            $scope.announcements_base.push({'text':filterObj.Name,'ModuleEntityGUID':filterObj.ModuleEntityGUID,'ModuleID':filterObj.ModuleID});  
                            if(filterObj.Name=='Everyone')
                            {
                                $scope.announcements_check = true;
                                $scope.announcements_checked = true;
                            }  
                        }
                        else if(filterObj.PostTypeLabel=='Discussion')
                        {
                            $scope.discussion.push({'text':filterObj.Name,'ModuleEntityGUID':filterObj.ModuleEntityGUID,'ModuleID':filterObj.ModuleID});
                            if(filterObj.Name=='Everyone')
                            {
                                $scope.discussion_check = true;
                                $scope.discussion_checked = true;
                            } 
                        }
                        else if(filterObj.PostTypeLabel=='Q & A')
                        {
                            $scope.question.push({'text':filterObj.Name,'ModuleEntityGUID':filterObj.ModuleEntityGUID,'ModuleID':filterObj.ModuleID});
                            if(filterObj.Name=='Everyone')
                            {
                                $scope.qa_check = true;
                                $scope.qa_checked = true;
                            } 
                        }
                    }); 
                }
            }
        });    
    }

    $scope.save_group_config = function()
    {
        if($scope.discussion_check==1 || $scope.announcements_check==1 || $scope.kb_check==1 || $scope.qa_check==1)
        {
            if($scope.discussion.length==0)
            {
                $scope.discussion = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
            }
            if($scope.question.length==0)
            {
                $scope.question = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
            }
            if($scope.knowledge_base.length==0)
            {
                $scope.knowledge_base = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
            }
            if($scope.announcements_base.length==0)
            {
                $scope.announcements_base = [{'text':'Everyone','ModuleEntityGUID':0,'ModuleID':0}];
            }
            reqData = {QA: $scope.question, Discussion: $scope.discussion, Wiki: $scope.knowledge_base, Announcements: $scope.announcements_base};
            apiService.call_api(reqData, 'admin_api/configuration/set_group_permission').then(function (response) {
                if (response.ResponseCode == 200) 
                {
                    ShowSuccessMsg(response.Message);
                    $scope.get_group_permission();
                }
            });    
        }
        else
        {
            ShowErrorMsg("Atleast one option should be enabled for everyone");
        }
    }

});




app.directive('fineUploader', function () {
    return {
        restrict: 'A',
        require: '?ngModel',
        scope: {model: '='},
        replace: false,
        link: function ($scope, element, attributes, ngModel) {
            var serr = 1;
            $scope.uploader = new qq.FineUploader({
                element: element[0],
                multiple: false,
                title: "Attach a Photo",
                request: {
                    endpoint: base_url + "api/upload_image",
                    params: {
                        Type: attributes.sectionType,
                        unique_id: function () {
                            return '';
                        },
                        LoginSessionKey: $('#AdminLoginSessionKey').val(),
                        DeviceType: 'Native'
                    }
                },
                validation: {
                    allowedExtensions: attributes.uploadExtensions.split(',')
                },
                failedUploadTextDisplay: {
                    mode: 'none'
                },
                callbacks: {
                    onUpload: function (id, fileName) {
                        // var html = "<li id='dummy_img'><div class='loader-box'><div id='ImageThumbLoader' class='uplaodLoader'><img src='"+base_url+"assets/admin/img/loading22.gif' id='spinner'></div></div></li>";
                        //$('#attached-media-'+$(element).attr('unique-id')).html(html);
                        $('.upload-btn-show').hide();
                        $('.upload-btn-loader').show();
                    },
                    onProgress: function (id, fileName, loaded, total) {
                    },
                    onComplete: function (id, fileName, responseJSON) {
                        $('.upload-btn-loader').hide();
                        if (responseJSON.Message == 'Success')
                        {
                            if ($(element).attr('image-type') == "landscape")
                            {
                                $('#attached-media-' + $(element).attr('unique-id')).html("<label>" + responseJSON.Data.ImageName + "</label>");
                            }
                            else
                            {
                                var CategoryCtrl = angular.element('#SkillCtrl').scope();

                                CategoryCtrl.$apply(function () {
                                    CategoryCtrl.currentData.ImageName = responseJSON.Data.ImageName;
                                    CategoryCtrl.currentData.MediaGUID = responseJSON.Data.MediaGUID;
                                    CategoryCtrl.currentData.OriginalName = responseJSON.Data.OriginalName;
                                });
                                /*click_function = 'remove_image("'+responseJSON.Data.MediaGUID+'");';
                                 var html = "<li id='"+responseJSON.Data.MediaGUID+"'><a class='smlremove' onclick='"+click_function+"'></a>";
                                 html+= "<figure><img alt='' width='98px' class='img-"+$(element).attr('image-type')+"-full' media_type='IMAGE' is_cover_media='0' media_name='"+responseJSON.Data.ImageName+"' media_guid='"+responseJSON.Data.MediaGUID+"' src='"+responseJSON.Data.ImageServerPath +'/220x220/'+responseJSON.Data.ImageName+"'></figure>";
                                 html+= "<span class='radio'></span></li>";
                                 
                                 $('#attached-media-'+$(element).attr('unique-id')).html(html);
                                 var $items = $('.img-full');*/
                            }
                        }
                        else if (responseJSON.ResponseCode !== 200)
                        {
                            $('#attached-media-' + $(element).attr('unique-id')).html("");
                        }
                    },
                    onValidate: function (b)
                    {
                        var allowed_extension = $(element).attr('upload-extensions');
                        var temp = new Array();
                        validExtensions = allowed_extension.split(",");
                        var fileName = b.name;
                        var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1);
                        if ($.inArray(fileNameExt, validExtensions) == -1)
                        {
                            $("html, body").animate({scrollTop: 0}, "slow");
                            if ($(element).attr('image-type') == "landscape")
                            {
                                PermissionError('Allowed file types only doc, docx, pdf and xls.');
                            }
                            else
                            {
                                PermissionError('Allowed file types only jpeg, jpg, gif and png.');
                            }
                            return false;
                        }
                    },
                    onError: function () {
                        $('#cm-' + attributes.uniqueId + ' .loading-class').remove();
                    }
                },
                showMessage: function (message) {
                    //showResponseMessage(message,'alert-danger');
                },
                text: {
                    uploadButton: '<i class="icon-upload icon-white"></i> Upload File(s)'
                },
                template: ' <a class="qq-upload-button"  title="Attach a Photo"><span class="up-icon"><label for="addIcon"></label><svg height="20px" width="25px" class="svg-icons"><use xlink:href="' + base_url + 'assets/admin/img/sprite.svg#defaultIcn" xmlns:xlink="http://www.w3.org/1999/xlink"/></svg></span><span class="up-text">Upload Icon</span></a><span class="qq-drop-processing qq-upload-drop-area" style="display:none;"></span>' + '<ul class="qq-upload-list" style="display:none;margin-top: 10px; text-align: center;"></ul>',
                chunking: {
                    //enabled: false
                    //onclick=$(\'#cmt-'+attributes.uniqueId+'\').trigger(\'focus\');
                }
            });
        }
    };
});
