!(function (app, angular) {

    angular.module('seenDetailsMdl', [])
        .controller('seenDetailsCtrl', seenDetailsCtrl);

    function seenDetailsCtrl($scope, $http, WallService, appInfo) {

        $scope.wallScope = {};

        $scope.$on('seenDetailsMdlInit', function (event, data) {
            $scope.wallScope = data.wallScope;
            //$("#addForum").modal();
            onInit(data);

            $scope.seenDetailsEmit(data.EntityGUID, data.EntityType);

        });

        $scope.seenDetailsEmit = function (EntityGUID, EntityType) {
            if (!((($scope.totalSeen === 0) || ($scope.seenDetails.length < $scope.totalSeen)) && !$scope.isSeenDetailsProcessing)) {
                return;
            }

            $scope.isSeenDetailsProcessing = true;
            var seenPageNo = $('#SeenPageNo').val(),
                jsonData = {};
            if ((seenPageNo == 1) && EntityGUID) {
                $scope.LastSeenActivityGUID = EntityGUID;
                $scope.LastSeenEntityType = EntityType;
            }
            jsonData = {
                EntityGUID: $scope.LastSeenActivityGUID,
                EntityType: $scope.LastSeenEntityType,
                PageNo: seenPageNo,
                PageSize: 8
            };
            WallService.CallApi(jsonData, 'activity/seen_list').then(function (response) {
                if (response.ResponseCode == 200) {
                    if (!$('#totalSeen').is(':visible')) {
                        $('#totalSeen').modal('show');
                    }
                    if (response.Data.length > 0) {
                        if ($scope.seenDetails.length === 0) {
                            $scope.seenDetails = angular.copy(response.Data);
                        } else {
                            $scope.seenDetails = $scope.seenDetails.concat(response.Data);
                        }
                        $scope.totalSeen = parseInt(response.TotalRecords);
                        $scope.seenMessage = '';
                        $('#SeenPageNo').val(parseInt(seenPageNo) + 1);
                    } else if ($scope.seenDetails.length === 0) {
                        $scope.seenDetails = [];
                        $scope.totalSeen = 0;
                        $scope.seenMessage = 'No seen yet.';
                    }
                }
                $scope.isSeenDetailsProcessing = false;
            });
        };

        function onInit(data) {

            $scope.seenDetails = [];
            $scope.totalSeen = 0;
            $scope.seenMessage = 'No liked yet.';
            $('#SeenPageNo').val(1);

            if (!data.Params) {
                return;
            }

            if (parseInt($('#SeenPageNo').val()) == 1) {
                $('#totalSeen').on('hide.bs.modal', function () {
                    $scope.seenDetails = [];
                    $scope.totalSeen = 0;
                    $scope.seenMessage = 'No seen yet.';
                    $('#SeenPageNo').val(1);
                });
            }
        }

    }

    seenDetailsCtrl.$inject = ['$scope', '$http', 'WallService', 'appInfo'];

})(app, angular);

