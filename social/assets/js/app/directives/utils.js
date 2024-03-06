!(function () {
    'use strict';
    app.directive('customAutocomplete', customAutocomplete);

    customAutocomplete.$inject = [];
    function customAutocomplete() {
        return {
            restrict: 'A',
            link: function (scope, el, attrs) {
                $(el).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: base_url + 'api/users/get_user_list?showFriend=1&selectedUsers=',
                            data: { term: request.term },
                            dataType: "json",
                            success: function(data) {
                                $('#ShareEntityUserGUID').val('');
                                if (data.ResponseCode == 502) {
                                    data.Data = { '0': { "FirstName": "Invalid LoginSessionKey.", "LastName": "", "value": request.term } };
                                }
                                if (data.Data.length <= 0) {
                                    data.Data = { '0': { "FirstName": "No result found.", "LastName": "", "value": request.term } };
                                }
                                response(data.Data);
                            }
                        });
                    },
                    select: function(event, ui) {
                        if (ui.item.FirstName !== 'No result found.' && ui.item.FirstName !== 'Invalid LoginSessionKey.') {
                            $('#ShareEntityUserGUID').val(ui.item.UserGUID);

                        }
                    }
                }).data("ui-autocomplete")._renderItem = function(ul, item) {
                    item.value = item.label = item.FirstName + " " + item.LastName;
                    item.id = item.UserGUID;
                    return $("<li>")
                        .data("item.autocomplete", item)
                        .append("<a>" + item.label + "</a>")
                        .appendTo(ul);
                };
            }
        };

    }

})();