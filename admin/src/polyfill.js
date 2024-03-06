
import 'core-js/es6/array'

import 'core-js/es6/map'

import 'core-js/es6/set'
import 'core-js/es7/object'
import 'core-js/es6/promise'

(function () {

  if ( typeof window.CustomEvent === "function" ) return false

  function CustomEvent ( event, params ) {
    params = params || { bubbles: false, cancelable: false, detail: undefined }
    var evt = document.createEvent( 'CustomEvent' )
    evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail )
    return evt
  }

  CustomEvent.prototype = window.Event.prototype

  window.CustomEvent = CustomEvent
})()
