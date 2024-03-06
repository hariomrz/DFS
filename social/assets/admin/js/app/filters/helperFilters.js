!(function () {
  'use strict';
    app.filter('limitToObj', [function(){
        return function(obj, limit){
            var keys = Object.keys(obj);
            if(keys.length < 1){
                return [];
            }

            var ret = new Object,
            count = 0;
            angular.forEach(keys, function(key, arrayIndex){
                if(count >= limit){
                    return false;
                }
                ret[key] = obj[key];
                count++;
            });
            return ret;
        };
    }]);

    app.filter('orderObjectBy', [function() {
      return function(items, field, reverse) {
        var filtered = [];
        angular.forEach(items, function(item) {
          filtered.push(item);
        });
        filtered.sort(function (a, b) {
          return (a[field] > b[field] ? 1 : -1);
        });
        if(reverse) filtered.reverse();
        return filtered;
      };
    }]);

    app.filter('custom', [function () {
      return function (input, search) {
        if (!input)
          return input;
        if (!search)
          return input;
        var expected = ('' + search).toLowerCase();
        var result = {};
        angular.forEach(input, function (value, key) {
          var actual = ('' + value).toLowerCase();
          if (actual.indexOf(expected) !== -1) {
            result[key] = value;
          }
        });
        return result;
      }
    }]);
})();