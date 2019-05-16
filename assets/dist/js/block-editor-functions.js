/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/src/js/block-editor-functions.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/src/js/block-editor-functions.js":
/*!*************************************************!*\
  !*** ./assets/src/js/block-editor-functions.js ***!
  \*************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

var registerPlugin = wp.plugins.registerPlugin;
var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
var _wp$components = wp.components,
    Button = _wp$components.Button,
    Panel = _wp$components.Panel,
    PanelBody = _wp$components.PanelBody,
    PanelRow = _wp$components.PanelRow,
    TextControl = _wp$components.TextControl,
    Modal = _wp$components.Modal;
var withState = wp.compose.withState;
var __ = wp.i18n.__;
var Fragment = wp.element.Fragment;
registerPlugin('rrze-xliff', {
  render: function render() {
    var currentUrl = window.location;
    var postId = new URL(currentUrl).searchParams.get('post');
    var xliffExportUrl = "".concat(currentUrl.protocol, "//").concat(currentUrl.host).concat(currentUrl.pathname, "?xliff-export=").concat(postId);
    var ExportModal = withState({
      isOpen: false,
      emailAddress: ''
    })(function (_ref) {
      var isOpen = _ref.isOpen,
          emailAddress = _ref.emailAddress,
          setState = _ref.setState;
      return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Fragment, null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
        isTertiary: true,
        onClick: function onClick() {
          return setState({
            isOpen: true
          });
        }
      }, __('Export', 'rrze-xliff')), isOpen && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Modal, {
        title: __('Export post as XLIFF', 'rrze-xliff'),
        onRequestClose: function onRequestClose() {
          return setState({
            isOpen: false
          });
        }
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
        href: xliffExportUrl,
        isDefault: true
      }, __('Download XLIFF file', 'rrze-xliff'))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("strong", null, __('Or send the file to an email address:', 'rrze-xliff'))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TextControl, {
        label: __('Email address', 'rrze-xliff'),
        value: emailAddress,
        onChange: function onChange(emailAddress) {
          return setState({
            emailAddress: emailAddress
          });
        }
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("p", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(Button, {
        href: "".concat(currentUrl.protocol, "//").concat(currentUrl.host).concat(currentUrl.pathname, "?xliff-export=").concat(postId, "&email_address=").concat(emailAddress),
        isDefault: true
      }, __('Send XLIFF file', 'rrze-xliff')))));
    });
    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(PluginPostStatusInfo, {
      className: "rrze-xliff-export-and-import"
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", null, __('XLIFF:', 'rrze-xliff'), " ", Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(ExportModal, null)));
  }
});

/***/ }),

/***/ "@wordpress/element":
/*!******************************************!*\
  !*** external {"this":["wp","element"]} ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["element"]; }());

/***/ })

/******/ });
//# sourceMappingURL=block-editor-functions.js.map