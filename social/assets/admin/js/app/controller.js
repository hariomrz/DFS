

var app = angular.module('App', [
    // 'ReUsableControl',
    'ngSanitize',
    'ngTagsInput',
    'ngStorage',
    'localytics.directives',
    'pasvaz.bindonce',
    'ngRoute',
    'ngImgCrop',
    'ui.bootstrap',
    'ui.sortable',
    'ngFileUpload',
    'summernote',
    'infinite-scroll',
    'oc.lazyLoad',
    //'ngImageCompress'
]);
//For check every request response if login session key expire then redirect to login page
app.config(['$httpProvider', '$ocLazyLoadProvider', function ($httpProvider, $ocLazyLoadProvider) {

        $ocLazyLoadProvider.config({
            'debug': true, // For debugging 'true/false'
            'events': true, // For Event 'true/false'
        });

        $httpProvider.interceptors.push(function () {
            return {
                request: function ($config) {
                    $config.headers['AdminLoginSessionKey'] = $('#AdminLoginSessionKey').val();
                    $config.headers['APPVERSION'] = 'v3';
                    return $config;
                },
                response: function (response) {
                    /* This is the code that transforms the response. `res.data` is the
                     * response body */
                    if (response.data.ResponseCode == 502) {
                        ShowErrorMsg("Your login key expire. Please login again!!");
                        setTimeout(function () {
                            signout();
                            window.top.location = base_url + 'admin/login';
                        }, 5000);
                    }
                    return response;
                }
            };
        });


    }]);

//View Port Directive
(function (factory) {
    if (typeof define !== 'undefined' && define.amd) {
        define([], factory);
    } else if (typeof module !== 'undefined' && module.exports) {
        module.exports = factory();
    } else {
        window.scrollMonitor = factory();
    }
})
        (function () {

            var scrollTop = function () {
                return window.pageYOffset ||
                        (document.documentElement && document.documentElement.scrollTop) ||
                        document.body.scrollTop;
            };

            var exports = {};

            var watchers = [];

            var VISIBILITYCHANGE = 'visibilityChange';
            var ENTERVIEWPORT = 'enterViewport';
            var FULLYENTERVIEWPORT = 'fullyEnterViewport';
            var EXITVIEWPORT = 'exitViewport';
            var PARTIALLYEXITVIEWPORT = 'partiallyExitViewport';
            var LOCATIONCHANGE = 'locationChange';
            var STATECHANGE = 'stateChange';

            var eventTypes = [
                VISIBILITYCHANGE,
                ENTERVIEWPORT,
                FULLYENTERVIEWPORT,
                EXITVIEWPORT,
                PARTIALLYEXITVIEWPORT,
                LOCATIONCHANGE,
                STATECHANGE
            ];

            var defaultOffsets = {top: 0, bottom: 0};

            var getViewportHeight = function () {
                return window.innerHeight || document.documentElement.clientHeight;
            };

            var getDocumentHeight = function () {
                // jQuery approach
                // whichever is greatest
                return Math.max(
                        document.body.scrollHeight, document.documentElement.scrollHeight,
                        document.body.offsetHeight, document.documentElement.offsetHeight,
                        document.documentElement.clientHeight
                        );
            };

            exports.viewportTop = null;
            exports.viewportBottom = null;
            exports.documentHeight = null;
            exports.viewportHeight = getViewportHeight();

            var previousDocumentHeight;
            var latestEvent;

            var calculateViewportI;
            function calculateViewport() {
                exports.viewportTop = scrollTop();
                exports.viewportBottom = exports.viewportTop + exports.viewportHeight;
                exports.documentHeight = getDocumentHeight();
                if (exports.documentHeight !== previousDocumentHeight) {
                    calculateViewportI = watchers.length;
                    while (calculateViewportI--) {
                        watchers[calculateViewportI].recalculateLocation();
                    }
                    previousDocumentHeight = exports.documentHeight;
                }
            }

            function recalculateWatchLocationsAndTrigger() {
                exports.viewportHeight = getViewportHeight();
                calculateViewport();
                updateAndTriggerWatchers();
            }

            var recalculateAndTriggerTimer;
            function debouncedRecalcuateAndTrigger() {
                clearTimeout(recalculateAndTriggerTimer);
                recalculateAndTriggerTimer = setTimeout(recalculateWatchLocationsAndTrigger, 100);
            }

            var updateAndTriggerWatchersI;
            function updateAndTriggerWatchers() {
                // update all watchers then trigger the events so one can rely on another being up to date.
                updateAndTriggerWatchersI = watchers.length;
                while (updateAndTriggerWatchersI--) {
                    watchers[updateAndTriggerWatchersI].update();
                }

                updateAndTriggerWatchersI = watchers.length;
                while (updateAndTriggerWatchersI--) {
                    watchers[updateAndTriggerWatchersI].triggerCallbacks();
                }

            }

            function ElementWatcher(watchItem, offsets) {
                var self = this;

                this.watchItem = watchItem;

                if (!offsets) {
                    this.offsets = defaultOffsets;
                } else if (offsets === +offsets) {
                    this.offsets = {top: offsets, bottom: offsets};
                } else {
                    this.offsets = {
                        top: offsets.top || defaultOffsets.top,
                        bottom: offsets.bottom || defaultOffsets.bottom
                    };
                }

                this.callbacks = {}; // {callback: function, isOne: true }

                for (var i = 0, j = eventTypes.length; i < j; i++) {
                    self.callbacks[eventTypes[i]] = [];
                }

                this.locked = false;

                var wasInViewport;
                var wasFullyInViewport;
                var wasAboveViewport;
                var wasBelowViewport;

                var listenerToTriggerListI;
                var listener;
                function triggerCallbackArray(listeners) {
                    if (listeners.length === 0) {
                        return;
                    }
                    listenerToTriggerListI = listeners.length;
                    while (listenerToTriggerListI--) {
                        listener = listeners[listenerToTriggerListI];
                        listener.callback.call(self, latestEvent);
                        if (listener.isOne) {
                            listeners.splice(listenerToTriggerListI, 1);
                        }
                    }
                }
                this.triggerCallbacks = function triggerCallbacks() {

                    if (this.isInViewport && !wasInViewport) {
                        triggerCallbackArray(this.callbacks[ENTERVIEWPORT]);
                    }
                    if (this.isFullyInViewport && !wasFullyInViewport) {
                        triggerCallbackArray(this.callbacks[FULLYENTERVIEWPORT]);
                    }


                    if (this.isAboveViewport !== wasAboveViewport &&
                            this.isBelowViewport !== wasBelowViewport) {

                        triggerCallbackArray(this.callbacks[VISIBILITYCHANGE]);

                        // if you skip completely past this element
                        if (!wasFullyInViewport && !this.isFullyInViewport) {
                            triggerCallbackArray(this.callbacks[FULLYENTERVIEWPORT]);
                            triggerCallbackArray(this.callbacks[PARTIALLYEXITVIEWPORT]);
                        }
                        if (!wasInViewport && !this.isInViewport) {
                            triggerCallbackArray(this.callbacks[ENTERVIEWPORT]);
                            triggerCallbackArray(this.callbacks[EXITVIEWPORT]);
                        }
                    }

                    if (!this.isFullyInViewport && wasFullyInViewport) {
                        triggerCallbackArray(this.callbacks[PARTIALLYEXITVIEWPORT]);
                    }
                    if (!this.isInViewport && wasInViewport) {
                        triggerCallbackArray(this.callbacks[EXITVIEWPORT]);
                    }
                    if (this.isInViewport !== wasInViewport) {
                        triggerCallbackArray(this.callbacks[VISIBILITYCHANGE]);
                    }
                    switch (true) {
                        case wasInViewport !== this.isInViewport:
                        case wasFullyInViewport !== this.isFullyInViewport:
                        case wasAboveViewport !== this.isAboveViewport:
                        case wasBelowViewport !== this.isBelowViewport:
                            triggerCallbackArray(this.callbacks[STATECHANGE]);
                    }

                    wasInViewport = this.isInViewport;
                    wasFullyInViewport = this.isFullyInViewport;
                    wasAboveViewport = this.isAboveViewport;
                    wasBelowViewport = this.isBelowViewport;

                };

                this.recalculateLocation = function () {
                    if (this.locked) {
                        return;
                    }
                    var previousTop = this.top;
                    var previousBottom = this.bottom;
                    if (this.watchItem.nodeName) { // a dom element
                        var cachedDisplay = this.watchItem.style.display;
                        if (cachedDisplay === 'none') {
                            this.watchItem.style.display = '';
                        }

                        var boundingRect = this.watchItem.getBoundingClientRect();
                        this.top = boundingRect.top + exports.viewportTop;
                        this.bottom = boundingRect.bottom + exports.viewportTop;

                        if (cachedDisplay === 'none') {
                            this.watchItem.style.display = cachedDisplay;
                        }

                    } else if (this.watchItem === +this.watchItem) { // number
                        if (this.watchItem > 0) {
                            this.top = this.bottom = this.watchItem;
                        } else {
                            this.top = this.bottom = exports.documentHeight - this.watchItem;
                        }

                    } else { // an object with a top and bottom property
                        this.top = this.watchItem.top;
                        this.bottom = this.watchItem.bottom;
                    }

                    this.top -= this.offsets.top;
                    this.bottom += this.offsets.bottom;
                    this.height = this.bottom - this.top;

                    if ((previousTop !== undefined || previousBottom !== undefined) && (this.top !== previousTop || this.bottom !== previousBottom)) {
                        triggerCallbackArray(this.callbacks[LOCATIONCHANGE]);
                    }
                };

                this.recalculateLocation();
                this.update();

                wasInViewport = this.isInViewport;
                wasFullyInViewport = this.isFullyInViewport;
                wasAboveViewport = this.isAboveViewport;
                wasBelowViewport = this.isBelowViewport;
            }

            ElementWatcher.prototype = {
                on: function (event, callback, isOne) {

                    // trigger the event if it applies to the element right now.
                    switch (true) {
                        case event === VISIBILITYCHANGE && !this.isInViewport && this.isAboveViewport:
                        case event === ENTERVIEWPORT && this.isInViewport:
                        case event === FULLYENTERVIEWPORT && this.isFullyInViewport:
                        case event === EXITVIEWPORT && this.isAboveViewport && !this.isInViewport:
                        case event === PARTIALLYEXITVIEWPORT && this.isAboveViewport:
                            callback.call(this, latestEvent);
                            if (isOne) {
                                return;
                            }
                    }

                    if (this.callbacks[event]) {
                        this.callbacks[event].push({callback: callback, isOne: isOne || false});
                    } else {
                        throw new Error('Tried to add a scroll monitor listener of type ' + event + '. Your options are: ' + eventTypes.join(', '));
                    }
                },
                off: function (event, callback) {
                    if (this.callbacks[event]) {
                        for (var i = 0, item; item = this.callbacks[event][i]; i++) {
                            if (item.callback === callback) {
                                this.callbacks[event].splice(i, 1);
                                break;
                            }
                        }
                    } else {
                        throw new Error('Tried to remove a scroll monitor listener of type ' + event + '. Your options are: ' + eventTypes.join(', '));
                    }
                },
                one: function (event, callback) {
                    this.on(event, callback, true);
                },
                recalculateSize: function () {
                    this.height = this.watchItem.offsetHeight + this.offsets.top + this.offsets.bottom;
                    this.bottom = this.top + this.height;
                },
                update: function () {
                    this.isAboveViewport = this.top < exports.viewportTop;
                    this.isBelowViewport = this.bottom > exports.viewportBottom;

                    this.isInViewport = (this.top <= exports.viewportBottom && this.bottom >= exports.viewportTop);
                    this.isFullyInViewport = (this.top >= exports.viewportTop && this.bottom <= exports.viewportBottom) ||
                            (this.isAboveViewport && this.isBelowViewport);

                },
                destroy: function () {
                    var index = watchers.indexOf(this),
                            self = this;
                    watchers.splice(index, 1);
                    for (var i = 0, j = eventTypes.length; i < j; i++) {
                        self.callbacks[eventTypes[i]].length = 0;
                    }
                },
                // prevent recalculating the element location
                lock: function () {
                    this.locked = true;
                },
                unlock: function () {
                    this.locked = false;
                }
            };

            var eventHandlerFactory = function (type) {
                return function (callback, isOne) {
                    this.on.call(this, type, callback, isOne);
                };
            };

            for (var i = 0, j = eventTypes.length; i < j; i++) {
                var type = eventTypes[i];
                ElementWatcher.prototype[type] = eventHandlerFactory(type);
            }

            try {
                calculateViewport();
            } catch (e) {
                try {
                    window.$(calculateViewport);
                } catch (e) {
                    throw new Error('If you must put scrollMonitor in the <head>, you must use jQuery.');
                }
            }

            function scrollMonitorListener(event) {
                latestEvent = event;
                calculateViewport();
                updateAndTriggerWatchers();
            }

            if (window.addEventListener) {
                window.addEventListener('scroll', scrollMonitorListener);
                window.addEventListener('resize', debouncedRecalcuateAndTrigger);
            } else {
                // Old IE support
                window.attachEvent('onscroll', scrollMonitorListener);
                window.attachEvent('onresize', debouncedRecalcuateAndTrigger);
            }

            exports.beget = exports.create = function (element, offsets) {
                if (typeof element === 'string') {
                    element = document.querySelector(element);
                } else if (element && element.length > 0) {
                    element = element[0];
                }

                var watcher = new ElementWatcher(element, offsets);
                watchers.push(watcher);
                watcher.update();
                return watcher;
            };

            exports.update = function () {
                latestEvent = null;
                calculateViewport();
                updateAndTriggerWatchers();
            };
            exports.recalculateLocations = function () {
                exports.documentHeight = 0;
                exports.update();
            };

            return exports;
        });

app.directive('viewportWatch', ['scrollMonitor', '$timeout', function (scrollMonitor, $timeout) {
        // Runs during compile
        var viewportUpdateTimeout;

        function debouncedViewportUpdate() {
            $timeout.cancel(viewportUpdateTimeout);
            viewportUpdateTimeout = $timeout(function () {
                scrollMonitor.update();
            }, 10);
        }
        return {
            restrict: 'AE',
            link: function (scope, element, attr) {

                //console.log(' view watch'+ scope.$eval(attr.viewportWatch || '-200'));

                //var elementWatcher = scrollMonitor.create(element, scope.$eval(attr.viewportWatch || '0'));
                var elementWatcher = scrollMonitor.create(element, {bottom: 300});

                function watchDuringDisable() {
                    /*jshint validthis:true */
                    this.$$watchersBackup = this.$$watchersBackup || [];
                    this.$$watchers = this.$$watchersBackup;
                    this.constructor.prototype.$watch.apply(this, arguments);
                    this.$$watchers = null;
                }

                function toggleWatchers(scope, enable) {
                    var digest, current, next = scope;

                    do {
                        current = next;

                        if (enable) {
                            if (current.hasOwnProperty('$$watchersBackup')) {
                                current.$$watchers = current.$$watchersBackup;
                                delete current.$$watchersBackup;
                                delete current.$watch;
                                digest = !scope.$root.$$phase;
                            }
                        } else {
                            if (!current.hasOwnProperty('$$watchersBackup')) {
                                current.$$watchersBackup = current.$$watchers;
                                current.$$watchers = null;
                                current.$watch = watchDuringDisable;
                            }
                        }

                        //DFS
                        next = current.$$childHead;
                        while (!next && current !== scope) {
                            if (current.$$nextSibling) {
                                next = current.$$nextSibling;
                            } else {
                                current = current.$parent;
                            }
                        }
                    } while (next);

                    if (digest) {
                        //local digest only for this scope subtree
                        scope.$digest();
                    }
                }

                function disableDigest() {
                    toggleWatchers(scope, false);
                }

                function enableDigest() {
                    toggleWatchers(scope, true);
                }

                if (!elementWatcher.isInViewport) {
                    scope.$evalAsync(disableDigest);
                    debouncedViewportUpdate();
                }

                elementWatcher.enterViewport(enableDigest);
                elementWatcher.exitViewport(disableDigest);
                scope.$on('toggleWatchers', function (event, enable) {
                    toggleWatchers(scope, enable);
                });

                scope.$on('$destroy', function () {
                    elementWatcher.destroy();
                    debouncedViewportUpdate();
                });
            }
        };
    }]).value('scrollMonitor', window.scrollMonitor);

/*
 * Controller(s)
 */
app.run(function ($rootScope, $location) {
    $rootScope.location = $location;
});
app.directive('repeatDone', function () {
    return function (scope, element, attrs) {
        if (scope.$last) { // all are rendered
            scope.$eval(attrs.repeatDone);
        }
    }
});

app.directive('onFocus', function () {
    return {
        restrict: 'A',
        link: function ($scope, $element) {
            $element.on('focus', function () {
                $element.closest('.form-group').addClass('form-focus');
            }).on('blur', function () {
                $element.closest('.form-group').removeClass('form-focus');
            });
        }
    };
});

app.directive('ngxTipsy', function () {
    // jQuery Tipsy Tooltip
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            // possible directions:
            // nw | n | ne | w | e | sw | s | se
            element.tipsy({
                delayIn: 0,
                delayOut: 0,
                gravity: attrs.ngxTipsy,
                opacity: 1,
                html: true
            });
        }
    }
});

app.directive('optionStyle', function ($compile, $parse) {
    return {
        restrict: 'A',
        priority: 10000,
        link: function optionStylePostLink(scope, elem, attrs) {
            setTimeout(function () {
                var allItems = angular.element(document.getElementById('UserListCtrl')).scope().users;
                var options = elem.find("option");
                for (var i = 0; i < options.length; i++) {
                    angular.element(options[i]).attr("data-img-src", image_server_path + 'upload/220x220/profile/' + allItems[i].ProfilePicture);
                }
                setTimeout(function () {
                    $(".localytics-chosen").trigger("chosen:updated");
                }, 2000);
            }, 2000);
        }
    };
});

app.factory('Settings', ['$rootScope', '$http', '$q', 'appInfo', function ($rootScope, $http, $q, appInfo) {
        return {
            getSettings: function () {
                if (!$rootScope.Settings) {
                    $rootScope.LoginSessionKey = $('#AdminLoginSessionKey').val();
                    $rootScope.ImageServerPath = image_server_path;
                    $rootScope.SiteURL = base_url;
                    $rootScope.CoverImage = "";
                    $rootScope.CoverExists = 0;
                    $rootScope.ProfileImage = '';
                    $rootScope.ShowProfileImageLoader = true;
                    $rootScope.AssetBaseUrl = AssetBaseUrl;
                }
                return $rootScope.Settings;
            },
            getImageServerPath: function () {
                return $rootScope.ImageServerPath;
            },
            getSiteUrl: function () {
                return $rootScope.SiteURL;
            },
            CallApi: function (reqData, reqURL) {
                var deferred = $q.defer();
                $http.post(appInfo.serviceUrl + reqURL, reqData).success(function (data) {
                    deferred.resolve(data);
                }).error(function (data) {
                    deferred.reject(data);
                });
                return deferred.promise;
            },
            getCurrentTimeUserTimeZone: function (date) {
                var localTime = new Date();
                var userDate = moment.tz(localTime, TimeZone).toDate();
                //console.log('ud ',userDate);
                return userDate;
            }
        }
    }]);

app.factory('setFormatDate', function () {
    return {
        getRelativeTime: function (date, msg) {



            var currentDate = new Date(); // local system date
            var timezoneOffset = time_zone_offset;

            //Convert current dateTime into UTC dateTime
            var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
            //console.log(utcDate);               

            //Convert date string (2015-02-02 07:12:13) in date object
            var t = date.split(/[- :]/);
            var today = new Date();
            // Apply each element to the Date function
            var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
            //date = new Date(date);
            var dateDiff = Math.floor((utcDate.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
            var formatedDate = '';
            var time = '';
            var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
            var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
            var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
            var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
            var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov', 'Dec');
            //console.log(dateDiff);

            date = new Date(date.getTime() - (timezoneOffset * 60000));

            if (fullDays > 2) {
                //var dt = new Date(date*1000);
                if (msg == 1) {
                    time = monthArray[date.getMonth()] + ' ' + date.getDate();
                } else {
                    time = monthArray[date.getMonth()] + ' ' + date.getDate() + ' at ' + formatAMPM(date);
                }
            } else if (fullDays == 2) {
                time = '2 days';
            } else if (today.getDate() > t[2]) {
                if (msg == 1) {
                    time = 'Yesterday';
                } else {
                    time = 'Yesterday at ' + formatAMPM(date);
                }
            } else if (fullHours > 0) {
                time = fullHours + ' hours';
                if (fullHours == 1) {
                    time = fullHours + ' hour';
                }
            } else if (fullMinutes > 0) {
                time = fullMinutes + ' mins';
                if (fullMinutes == 1) {
                    time = fullMinutes + ' min';
                }
            } else {
                time = 'Just now';
            }
            return time;
        },

        getTime: function (date, msg) {



            var currentDate = new Date(); // local system date
            var timezoneOffset = time_zone_offset;

            //Convert current dateTime into UTC dateTime
            var utcDate = new Date(currentDate.getTime() + (timezoneOffset * 60000));
            //console.log(utcDate);               

            //Convert date string (2015-02-02 07:12:13) in date object
            var t = date.split(/[- :]/);
            var today = new Date();
            // Apply each element to the Date function
            var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
            //date = new Date(date);
            var dateDiff = Math.floor((utcDate.getTime() / 1000)) - Math.floor((date.getTime() / 1000));
            var formatedDate = '';
            var time = '';
            var fullDays = Math.floor(dateDiff / (60 * 60 * 24));
            var fullHours = Math.floor((dateDiff - (fullDays * 60 * 60 * 24)) / (60 * 60));
            var fullMinutes = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60)) / 60);
            var fullSeconds = Math.floor((dateDiff - (fullDays * 60 * 60 * 24) - (fullHours * 60 * 60) - (fullMinutes * 60)));
            var dayArray = new Array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            var monthArray = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov', 'Dec');
            //console.log(dateDiff);

            date = new Date(date.getTime() - (timezoneOffset * 60000));

            if (fullDays > 2) {
                //var dt = new Date(date*1000);
                if (msg == 1) {
                    time = monthArray[date.getMonth()] + ' ' + date.getDate();
                } else {
                    time = monthArray[date.getMonth()] + ' ' + date.getDate() + ' at ' + formatAMPM(date);
                }
            } else if (fullDays == 2) {
                time = '2 days';
            } else if (today.getDate() > t[2]) {
                if (msg == 1) {
                    time = 'Yesterday';
                } else {
                    time = 'Yesterday at ' + formatAMPM(date);
                }
            } else if (fullHours > 0) {
                time = fullHours + ' hours';
                if (fullHours == 1) {
                    time = fullHours + ' hour';
                }
            } else if (fullMinutes > 0) {
                time = fullMinutes + ' mins';
                if (fullMinutes == 1) {
                    time = fullMinutes + ' min';
                }
            } else {
                time = 'Just now';
            }
            return time;
        }
    }
});

app.directive('dndList', function () {
    return function (scope, element, attrs) {
        // variables used for dnd
        var toUpdate;
        var startIndex = -1;

        // watch the model, so we always know what element
        // is at a specific position
        scope.$watch(attrs.dndList, function (value) {
            toUpdate = value;
        }, true);

        // use jquery to make the element sortable (dnd). This is called
        // when the element is rendered
        $(element[0]).sortable({
            items: 'li',
            start: function (event, ui) {
                // on start we define where the item is dragged from
                startIndex = ($(ui.item).index());
            },
            stop: function (event, ui) {
                // on stop we determine the new index of the
                // item and store it there
                var newIndex = ($(ui.item).index());
                var toMove = toUpdate[startIndex];
                toUpdate.splice(startIndex, 1);
                toUpdate.splice(newIndex, 0, toMove);

                // we move items in the array, if we want
                // to trigger an update in angular use $apply()
                // since we're outside angulars lifecycle
                scope.$apply(scope.model);
            },
            axis: 'y'
        })
    }
});

app.directive('errSrc', function () {
    return {
        link: function (scope, element, attrs) {
            element.bind('error', function () {
                if (attrs.src != attrs.errSrc) {
                    attrs.$set('src', attrs.errSrc);
                }
            });
        }
    }
});

app.directive('errName', function () {
    return {
        link: function (scope, element, attrs) {
            element.bind('error', function () {
                if (attrs.src != attrs.errName) {
                    var name = attrs.errName.split(' ');
                    var attr = '?';
                    if (name.length == 1)
                    {
                        attr = name[0].substring(1, 0);
                    }
                    if (name.length > 1)
                    {
                        attr = name[0].substring(1, 0) + name[1].substring(1, 0);
                    }
                    $(element).hide();
                    $(element).next('.thumb-alpha').remove();
                    $(element).after('<span class="thumb-alpha"><span class="default-thumb"><span class="default-thumb-placeholder">' + attr.toUpperCase() + '</span></span></span>');
                }
            });
        }
    }
});

app.directive("collapseFeed", function () {
    return {
        restrict: "EA",
        link: function (scope, elem, attrs, ngModelCtrl) {
            var listHt = elem.parent('div').parent('.news-feed-listing').height() - 52;
            elem.on('click', function () {
                if (!elem.parent('div').parent('.news-feed-listing').hasClass('collapsed')) {
                    elem.prev('.collapse-content').addClass('collapsed');
                    elem.prev('.collapse-content').animate({
                        height: 38
                    }, 500, function () {
                        elem.parent('div').parent('.news-feed-listing').addClass('collapsed');
                    });
                    listHt = elem.parent('div').parent('.news-feed-listing').height() - 52;
                } else {
                    elem.prev('.collapse-content').animate({
                        height: listHt
                    }, 500, function () {
                        elem.prev('.collapse-content').removeClass('collapsed').removeAttr('style');
                        elem.parent('div').parent('.news-feed-listing').removeClass('collapsed');
                    });
                }
            });
        }
    }
});

app.directive("pagingInfo", function () {
    return {
        scope: {
            numPerPage: '=',
            currentPage: '=',
            totalRecord: '='
        },
        restrict: "EA",
        link: function (scope, elem, attrs, ngModelCtrl) {
            scope.$watch('numPerPage + currentPage + totalRecord', function () {
                var offset = (scope.numPerPage * (scope.currentPage - 1));
                var from = offset + 1;
                var to = offset + scope.numPerPage;
                to = (to > scope.totalRecord) ? scope.totalRecord : to;

                var info = "Showing " + from + " to " + to + " of " + scope.totalRecord + " Records";
                angular.element(elem).html(info);
            });
            //var = "Showing {{(numPerPage * (currentPage-1))+1}} to {{((numPerPage * (currentPage-1))+numPerPage) > totalRecord ? totalRecord : ((numPerPage * (currentPage-1))+numPerPage)}} of {{totalRecord}} Records";
        }
    }
});



app.factory('lazyLoadCS', ['$ocLazyLoad', '$rootScope', '$templateCache', function ($ocLazyLoad, $rootScope, $templateCache) {

        var callbacksStack = {};
        var shareObjs = {};

        $rootScope.$on("$includeContentLoaded", function (event, templateName) {
            if (!callbacksStack[templateName]) {
                return;
            }
            callbacksStack[templateName].callback({isInit: 1});
            delete callbacksStack[templateName];
        });


        return {
            loadModule: function (params) {

                var moduleName = params.moduleName;

                if ($ocLazyLoad.getModules().indexOf(moduleName) > -1) {
                    params.callback({isInit: 0});
                }

                callbacksStack[params.templateUrl] = params;

                loadModule(params);

            },

            loadTemplate: function (params) {

                if ($templateCache.get(params.templateUrl)) {
                    params.callback({isInit: 0});
                    return;
                }

                callbacksStack[params.templateUrl] = params;
                $ocLazyLoad.load(params.templateUrl).then(function () {
                    params.scopeObj[params.scopeTmpltProp] = params.templateUrl;
                });

            },

            shareObj: function (entityName, entityObj, isRemove) {
                if (entityObj === undefined || entityObj === null) {
                    if (entityName in shareObjs) {
                        var sharedObj = angular.copy(shareObjs[entityName]);
                        if (isRemove) {
                            delete shareObjs[entityName];
                        }

                        return sharedObj;
                    }
                }

                shareObjs[entityName] = entityObj;
            }
        }



        function loadModule(params) {
            var files = [];
            if (params.files && params.files.length) {
                files = params.files;
            }

            files.push(params.moduleUrl);

            $ocLazyLoad.load(files, {serie: true}).then(function (test) {
                params.scopeObj[params.scopeTmpltProp] = params.templateUrl;
            }, function (e) {
                console.log(e);
            });
        }



    }]);
