(function () {
    'use strict';
    // webStorage.js
    function webStorage($localStorage, $sessionStorage) {

        /**
         *Set storege data
         * @param {any, any}
         */
        this.setStorageData = function setStorageData(key, data) {
            $localStorage[key] = data;
        };

        /**
         *Get storege data
         * @return {string}
         */
        this.getStorageData = function getStorageData(key) {
            return ( $localStorage[key] ) ? $localStorage[key] : false;
        };

        /**
         *Get storege data
         * @return {string}
         */
        this.deleteStorageData = function getStorageData(key) {
            if ( this.getStorageData(key) === false ) {
                delete $localStorage[key];
            }
        };

        /**
         *Reset storage data
         */
        this.resetStorageData = function destroy() {
            $localStorage.$reset();
        };


        /**
         *Set session storege data
         * @param {any, any}
         */
        this.setSessionStorageData = function setSessionStorageData(key, data) {
            $sessionStorage[key] = data;
        };

        /**
         *Get storege data
         * @return {string}
         */
        this.getSessionStorageData = function getSessionStorageData(key) {
            return ( $sessionStorage[key] ) ? $sessionStorage[key] : false;
        };

        /**
         *Get storege data
         * @return {string}
         */
        this.deleteSessionStorageData = function getSessionStorageData(key) {
            if ( this.getStorageData(key) === false ) {
                delete $sessionStorage[key];
            }
        };

        /**
         *Reset storage data
         */
        this.resetSessionStorageData = function destroy() {
            $sessionStorage.$reset();
        };

    }

    app.service('webStorage', ['$localStorage','$sessionStorage', webStorage]);
})();

