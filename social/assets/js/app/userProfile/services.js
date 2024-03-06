!(function (app, angular) {

    app.factory('utilFactory', utilFactory);

    function utilFactory($http, WallService) {

        var checkTabMessage = {
            CHECK_TAB_MESSAGE: 'CHECK_TAB_MESSAGE',
            CHECK_TAB_MESSAGE_ID: new Date().getTime()
        };
        
        
        var TabsCmunication = {
            
            registerMyTab : function() {
                if(!$.cookie('TabIds')) {
                    $.cookie('TabIds', [], {expires: null});
                }
                var tabIds = $.cookie('TabIds') || [];
                tabIds.push(checkTabMessage.CHECK_TAB_MESSAGE_ID);
                
                $.cookie('TabIds', tabIds, {expires: null});
            },
            
            checkUnusedCookies : function() {
                var tabIds = $.cookie('TabIds') || $.cookie('TabIds', [], {expires: null});
                localStorage.setItem('checkTabIds', JSON.stringify([]));
                
                var checkTabMessageExt = angular.extend({}, checkTabMessage, {showYourId : 1});
                TabsCmunication.message_broadcast(checkTabMessageExt);
                
                setTimeout(function(){                                        
                    TabsCmunication.registerMyTab();                                        
                }, 200);
                
            },
            
            eventBind : function() {
                $(window).on('storage', this.message_receive);
            },
            
            message_broadcast: function(message)
            {
                localStorage.setItem('CHECK_TAB_MESSAGE', JSON.stringify(message));
                localStorage.removeItem('CHECK_TAB_MESSAGE');
            },

            message_receive: function(ev)
            {
                if (ev.originalEvent.key != 'CHECK_TAB_MESSAGE') {
                    return; // ignore other keys
                }

                var message = JSON.parse(ev.originalEvent.newValue);

                if (!message || !('CHECK_TAB_MESSAGE' in message) || message.CHECK_TAB_MESSAGE != 'CHECK_TAB_MESSAGE') {
                    return;
                }

                if (message.CHECK_TAB_MESSAGE_ID != checkTabMessage.CHECK_TAB_MESSAGE_ID && message.windowUnload) {
                    
                    setTimeout(function () {
                        var jsonData = {
                            EntityType: 'LogInEndTimeRemove',
                            LogInEndId: message.CHECK_TAB_MESSAGE_ID
                        };
                        WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });

                    }, 500);
                    
                    return;
                }
                
                
                if (message.showYourId) {
                    var checkTabIds = localStorage.getItem('checkTabIds');
                    checkTabIds = JSON.parse(checkTabIds);
                    checkTabIds.push(checkTabMessage.CHECK_TAB_MESSAGE_ID);
                    localStorage.setItem('checkTabIds', JSON.stringify(checkTabIds));
                }
                

                //console.log('Event response from other tabs  11111111111 : ' + message.CHECK_TAB_MESSAGE_ID);

                // etc.
            }
        }
        
        return {
            isCookieTypeCreated: isCookieTypeCreated,
            whenUserComesAgain: whenUserComesAgain,
            userSessionEndTimeEvents: userSessionEndTimeEvents
        };
        
        function isCookieTypeCreated(cookieName) {

            var cookieType = $.cookie(cookieName);

            if (cookieType) {
                return false;
            }

            $.cookie(cookieName, 1, {
                expires: null
            });

            return true;
        }

        function whenUserComesAgain() {
            if (LoginSessionKey == '') {
                return false;
            }
            
            if (!isCookieTypeCreated('LogInEvent')) {
                return;
            }

            var jsonData = {
                EntityType: 'LogIn'
            };

            
            WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });

        }


        function userSessionEndTimeEvents() {
            window.onunload = function () {
                var jsonData = {
                    EntityType: 'LogInEndTime',
                    LogInEndId: checkTabMessage.CHECK_TAB_MESSAGE_ID
                };
                WallService.CallApi(jsonData, 'log/log_activity').then(function (response) { });
                var checkTabMessageExt = angular.extend({}, checkTabMessage, {windowUnload : 1});
                TabsCmunication.message_broadcast(checkTabMessageExt);
            };
            
            TabsCmunication.eventBind();
            TabsCmunication.checkUnusedCookies();
        }

        






    }

    utilFactory.$inject = ['$http', 'WallService'];

})(app, angular)