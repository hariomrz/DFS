!(function () {
    'use strict';
    app.directive('customPopover', customPopover);
    
    
    customPopover.$inject = ['$compile'];
    function customPopover($compile) {
        return {
            restrict: 'A',
            template: '<span>{{label}}</span>',
            link: function (scope, el, attrs) {                
                scope.label = scope.$eval(attrs.label);                 
                //attrs.content = getCompiledCode(attrs, scope);
                var content = '';
                content = scope.$eval(attrs.contentng);  
                
                if(!content) {
                    return;
                }
                
                $(el).data('content', content);
                
                //attrs.trigger
                
                $(el).popover({
                    trigger: attrs.trigger,
                    html: true,
                    content: content,
                    placement: attrs.placement
                });
            }
        };
        
        
        function getCompiledCode(attrs, scope) {
            
            if(attrs.content) {
                return attrs.content;
            }
            
            var domEle = $('#hidden_div');
            if(!domEle.length) {
                $('body').append('<div id="hidden_div" class="hide"></div>');
                domEle = $('#hidden_div');
            }
            
            var tmplData = scope.$eval(attrs.data);  
            domEle.html(attrs.tmpl);
            attrs.content = $compile(domEle.contents())(tmplData);
        }
        
    }
    
    
    
    

})();