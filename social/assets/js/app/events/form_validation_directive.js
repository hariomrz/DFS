app.factory('showFormError', [function () {
    var bindError = function(error, element){
        var element_id = angular.element(element).attr('id');
        var field = '#'+element_id+'_error';
        angular.element(field).empty();
        angular.element(field).append(error.text());
        angular.element(field).show();
        return true;
    };
    return bindError;
}]);

app.factory('hideFormError', [function() {
    var bindError = function(error, element){
        var element_id = angular.element(element).attr('id');
        var field = '#'+element_id+'_error';
        angular.element(field).empty();
        angular.element(field).hide();
        return true;
    };
    return bindError;
}]);

app.directive('eventForm', ['showFormError', 'hideFormError', '$rootScope', function (showFormError, hideFormError, $rootScope) {
    return {
        restrict: 'A',
        scope: {
            submitSignup:'&'
        },
        link: function postLink(scope, iElement, iAttrs) {

            scope.validationRulesSignup = {
                namefieldCtrlID  :{required:true},
                CategoryIds      :{required:true}, 
                validurlCtrlID   :{required:false,valid_url: true},
                textareaID       :{required:true},
                textareaID1      :{required:true},
                venuefieldCtrlID :{required:true},
                //Street1CtrlID    :{required:true},
                datepicker3      :{required:true},
                datepicker4      :{required:true},
                timepicer        :{required:true},
                timepicer2       :{required:true},
                Privacy          :{required:true},
            };

            scope.validationSignupMessage    = {
                namefieldCtrlID        :{required:'Please enter title.'},
                CategoryIds            :{required:'Please select category.'},
                validurlCtrlID         :{required:'',url:'Please enter valid url'},
                textareaID             :{required:'Please enter description.'},
                textareaID1            :{required:'Please enter summary.'},
                venuefieldCtrlID       :{required:'Please enter venue.'},
                //Street1CtrlID          :{required:'Please select location.'},
                datepicker3            :{required:'Please select start date.'},
                datepicker4            :{required:'Please select end date.'},
                timepicer2             :{required:'Please select end time.'},
                timepicer              :{required:'Please select start time.'},
                Privacy                :{required:'Please select privacy.'},
            };

            iElement.validate({
                errorElement: "span",
                rules:scope.validationRulesSignup,
                messages:scope.validationSignupMessage,
                errorPlacement: function (error, element){
                    if(element.prop('type') == 'select-one'){
                        $('#'+element.attr('id')).change(function(){
                            $(this).valid();
                        });
                    }
                    showFormError(error, element);                   
                },
                highlight: function (element, errorClass) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function (element, errorClass) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                submitHandler: function(form) {
                    scope.submitSignup();
                },
                success: function(error, element) {
                    hideFormError(error, element);
                },
                ignore: ":hidden:not(select)"
            });
        }
    };
}]);

app.directive('editEventForm', ['showFormError', 'hideFormError', '$rootScope', function (showFormError, hideFormError, $rootScope) {
    return {
        restrict: 'A',
        scope: {
            submitSignup:'&'
        },
        link: function postLink(scope, iElement, iAttrs) {

            scope.validationRulesSignup = {
                EditNamefield    :{required:true},
                CategoryIds      :{required:true}, 
                validurlCtrlID   :{required:false,valid_url: true},
                textareaDID      :{required:true},
                textareaDID1     :{required:true},
                venuefieldCtrlID :{required:true},
                //EditStreet1CtrlID:{required:true},
                datepicker33     :{required:true},
                datepicker44     :{required:true},
                timepicer3       :{required:true},
                timepicer4       :{required:true},
                Privacy          :{required:true},
            };

            scope.validationSignupMessage    = {
                EditNamefield          :{required:'Please enter title.'},
                CategoryIds            :{required:'Please select category.'},
                validurlCtrlID         :{required:'',url:'Please enter valid url'},
                textareaDID            :{required:'Please enter description.'},
                textareaDID1           :{required:'Please enter summary.'},
                venuefieldCtrlID       :{required:'Please enter venue.'},
                //EditStreet1CtrlID      :{required:'Please select location.'},
                datepicker33           :{required:'Please select start date.'},
                datepicker44           :{required:'Please select end date.'},
                timepicer3             :{required:'Please select start time.'},
                timepicer4             :{required:'Please select end time.'},
                Privacy                :{required:'Please select privacy.'},
            };

            iElement.validate({
                errorElement: "span",
                rules:scope.validationRulesSignup,
                messages:scope.validationSignupMessage,
                errorPlacement: function (error, element){
                    if(element.prop('type') == 'select-one'){
                        $('#'+element.attr('id')).change(function(){
                            $(this).valid();
                        });
                    }
                    showFormError(error, element);                   
                },
                highlight: function (element, errorClass) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function (element, errorClass) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                submitHandler: function(form) {
                    scope.submitSignup();
                },
                success: function(error, element) {
                    hideFormError(error, element);
                },
                ignore: ":hidden:not(select)"
            });
        }
    };
}]);

jQuery.validator.addMethod("custom_method_one", function (value, element) {
    if (this.optional(element)) {
    return true;
    }

    var emails = value.split(','),
    valid = true;

    for (var i = 0, limit = emails.length; i < limit; i++) {
    value = emails[i];
    valid = valid && jQuery.validator.methods.email.call(this, value, element);
    }

    return valid;
}, "Invalid email address.");

jQuery.validator.addMethod("valid_url", function(value, element) { 
    if(value.substr(0,7) != 'http://' && value.substr(0,8) != 'https://'){
        value = 'http://' + value;
    }

    if(value.substr(value.length-1, 1) != '/'){
        value = value + '/';
    }
    console.log(value);
    return this.optional(element) || /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(value); 
}, "Please enter valid url.");

app.directive('shareBySocialEmail', ['showFormError', 'hideFormError', '$rootScope', function (showFormError, hideFormError, $rootScope) {
    return {
        restrict: 'A',
        scope: {
            submitSignup:'&'
        },
        link: function postLink(scope, iElement, iAttrs) {

            scope.validationRulesSignup = {
                form1email       :{required:true,custom_method_one: true },
                shareEmailtext   :{required:true},
            };

            scope.validationSignupMessage    = {
                form1email             :{required:'Please enter email address.'},
                shareEmailtext         :{required:'Please enter description.'}
            };

            iElement.validate({
                errorElement: "span",
                rules:scope.validationRulesSignup,
                messages:scope.validationSignupMessage,
                errorPlacement: function (error, element){
                    if(element.prop('type') == 'select-one'){
                        $('#'+element.attr('id')).change(function(){
                            $(this).valid();
                        });
                    }
                    showFormError(error, element);                   
                },
                highlight: function (element, errorClass) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function (element, errorClass) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                submitHandler: function(form) {
                    scope.submitSignup();
                },
                success: function(error, element) {
                    hideFormError(error, element);
                },
                ignore: ":hidden:not(select)"
            });
        }
    };
}]);