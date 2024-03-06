
!(function (app, angular) {

    angular.module('likeDetailsMdl', [])
            .controller('likeDetailsCtrl', likeDetailsCtrl);

    function likeDetailsCtrl($scope, $http, WallService, appInfo) {

        $scope.wallScope = {};
        var likeDetialsUrl = 'activity/getLikeDetails';
        $scope.$on('likeDetailsMdlInit', function (event, data) {
            $scope.wallScope = data.wallScope;
            //$("#addForum").modal();
            
            if(data.mediaLikeDetails) {
                likeDetialsUrl = 'media/like_details';
            }
            
            onInit(data);
            if(data.fn == 'likeDetailsEmit') {
                $scope.likeDetailsEmit(data.EntityGUID, data.EntityType);
            } else {
                $scope.likeDetails(data.EntityGUID, data.EntityType);
            }
            

        });
        
        $scope.likeDetails = function (EntityGUID, EntityType) {
            $scope.LastLikeActivityGUID = EntityGUID;
            jsonData = {
                MediaGUID: EntityGUID,
                EntityGUID: EntityGUID,
                EntityType: EntityType,
                PageNo: $('#LikePageNo').val(),
                PageSize: 8
            };
            WallService.CallApi(jsonData, likeDetialsUrl).then(function (response) {
                if (response.ResponseCode == 200) {
                    if (!$('#totalLikes').is(':visible')) {
                        $('#totalLikes').modal('show');
                        $('#LikePageNo').val(0);
                        $scope.likeDetails = [];
                        if (response.Data == '') {
                            $scope.likeDetails = [];
                            $scope.totalLikes = 0;
                            $scope.likeMessage = 'No likes yet.';
                        }
                    }
                    if (response.Data !== '') {
                        $(response.Data).each(function (k, v) {
                            var append = true;
                            $($scope.likeDetails).each(function (key, val) {
                                if (v.ProfileURL == val.ProfileURL) {
                                    append = false;
                                }
                            });
                            if (append) {
                                $scope.likeDetails.push(response.Data[k]);
                            }
                        });
                        $scope.totalLikes = response.TotalRecords;
                        $scope.likeMessage = '';
                        $('#LikePageNo').val(parseInt($('#LikePageNo').val()) + 1);
                    }
                }
            });
        }

        $scope.likeDetailsEmit = function (EntityGUID, EntityType) {


            if ( !((($scope.totalLikes === 0) || ($scope.likeDetails.length < $scope.totalLikes)) && !$scope.isLikeDetailsProcessing)) {
                return;
            }



            $scope.isLikeDetailsProcessing = true;
            var likePageNo = $('#LikePageNo').val(),
                    jsonData = {};
            if (likePageNo == 0) {
                likePageNo = 1;
            }

            if ((likePageNo == 1) && EntityGUID) {
                $scope.LastLikeActivityGUID = EntityGUID;
                $scope.LastLikeEntityType = EntityType;
            }
            jsonData = {
                MediaGUID: $scope.LastLikeActivityGUID,
                EntityGUID: $scope.LastLikeActivityGUID,
                EntityType: $scope.LastLikeEntityType,
                PageNo: likePageNo,
                PageSize: 8
            };
            WallService.CallApi(jsonData, likeDetialsUrl).then(function (response) {
                if (response.ResponseCode == 200) {
                    if (!$('#totalLikes').is(':visible')) {
                        $('#totalLikes').modal('show');
                    }
                    if (response.Data.length > 0) {
                        if ($scope.likeDetails.length === 0) {
                            $scope.likeDetails = angular.copy(response.Data);
                        } else {
                            $scope.likeDetails = $scope.likeDetails.concat(response.Data);
                        }
                        $scope.totalLikes = parseInt(response.TotalRecords);
                        $scope.likeMessage = '';
                        $('#LikePageNo').val(parseInt(likePageNo) + 1);
                    } else if ($scope.likeDetails.length === 0) {
                        $scope.likeDetails = [];
                        $scope.totalLikes = 0;
                        $scope.likeMessage = 'No liked yet.';
                    }
                }
                $scope.isLikeDetailsProcessing = false;
            });



            


        }
        
        function onInit(data) {
            
            $scope.likeDetails = [];
            $scope.totalLikes = 0;
            $scope.likeMessage = 'No liked yet.';
            $('#LikePageNo').val(1);
            
            
            
            if(!data.Params) {
                return;
            }
            
            if (parseInt($('#LikePageNo').val()) == 1) {
                $('#totalLikes').on('hide.bs.modal', function () {
                    $scope.likeDetails = [];
                    $scope.totalLikes = 0;
                    $scope.likeMessage = 'No liked yet.';
                    $('#LikePageNo').val(1);
                });
            }
        }

    }

    likeDetailsCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo'];


})(app, angular);

