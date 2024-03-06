// /* ReUsableControl Module
// ===========================*/
// angular.module('ReUsableControl', [])
// 	.directive('uixInput', uixInput)
// 	.directive('uixTextarea', uixTextarea)

// function uixInput(){
// 		return {
// 			restrict: 'E',
// 			replace: true,			
// 			template: '<input>',
// 			link: function($scope, iElm, iAttrs) {
// 			        	iElm.loadControl();
// 				}
// 		}
// }
// function uixTextarea(){
// 		return {
// 			restrict: 'E',
// 			replace: true,
// 			template: '<textarea />',
// 			link: function($scope, iElm, iAttrs) {
// 				setTimeout( function(){ iElm.loadControl()} ,500);
// 				}
// 		}
// }