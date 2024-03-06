// UserList Controller
app.controller('NotesCtrl', function ($http, $q, $scope, $rootScope, getData, $window, apiService) {
    $scope.NotesList    = [];
    $scope.IsEdit       = 0;
    
    $scope.set_user_details = function(UserID,Name)
    {
        $scope.UserID = UserID;
        $scope.Name = Name;
    }

    $scope.get_notes = function(UserID)
    {
        $scope.AdminLoginSessionKey     = $('#AdminLoginSessionKey').val();
        $scope.UserID                   =  UserID;
        var reqData                     = {};
        reqData.AdminLoginSessionKey    = $scope.AdminLoginSessionKey;
        reqData.ModuleID                = 3;
        reqData.ModuleEntityID          = $scope.UserID;
        getData.CallApi(reqData,'dashboard/get_note_list').then(function (response) 
        {
            if(response.ResponseCode == 200) 
            {
                console.log(response.Data);
                $scope.NotesList = response.Data;            
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    $scope.reset_popup = function () 
    {
        $scope.PostContentNotes = "";
        $scope.IsEdit           = 0;
    }

    $scope.SubmitNote = function() 
    {
        var reqData = {
          ModuleID: "3",
          ModuleEntityID: $scope.UserID,
          Description: $scope.PostContentNotes,
          AdminLoginSessionKey:$scope.AdminLoginSessionKey
        }
        getData.CallApi(reqData,'dashboard/save_note').then(function (response) 
        {
            $('#addNotes').modal('hide');
            if(response.ResponseCode == 200) 
            {
                $scope.NotesList = response.Data;  
                $scope.get_notes($scope.UserID);  
                ShowSuccessMsg(response.Message);        
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    $scope.deleteNote = function(NoteID,Index)
    {
        var reqData = {
          NoteID:NoteID,
          AdminLoginSessionKey:$scope.AdminLoginSessionKey
        }
        getData.CallApi(reqData,'dashboard/delete_note').then(function (response) 
        {
            $('#addNotes').modal('hide');
            if(response.ResponseCode == 200) 
            {
                $scope.NotesList.splice(Index, 1);   
                ShowSuccessMsg(response.Message);       
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    $scope.open_edit_popup = function (data,Index) 
    {
        $('#addNotes').modal();
        $scope.Index = Index;
        $scope.IsEdit           = 1;
        $scope.PostContentNotes = data.Description;
        $scope.NoteID = data.NoteID;
    }

    $scope.UpdateNote = function() 
    {
        var reqData = {
          ModuleID: "3",
          ModuleEntityID: $scope.UserID,
          Description: $scope.PostContentNotes,
          NoteID:$scope.NoteID,
          AdminLoginSessionKey:$scope.AdminLoginSessionKey
        }
        getData.CallApi(reqData,'dashboard/save_note').then(function (response) 
        {
            $scope.reset_popup();
            $('#addNotes').modal('hide');
            if(response.ResponseCode == 200) 
            { 
                $scope.NotesList[$scope.Index].Description = reqData.Description;
                ShowSuccessMsg(response.Message);
            }
            else
            {
                ShowErrorMsg(response.Message);
            }
        });
    }

    var Summer_keyword='';
        $scope.summernote_options = {
            placeholder:'What’s on your mind',
            airMode: false,
            popover:{},
            callbacks: {
                onPaste: function (e) {
                    var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    setTimeout(function(){
                      document.execCommand('insertText', false, bufferText);
                    },10);
                }
            },
            toolbar: [
                    ['style', ['bold', 'italic', 'underline']]
                ],
            hint: {
                      
                  }
          };
});