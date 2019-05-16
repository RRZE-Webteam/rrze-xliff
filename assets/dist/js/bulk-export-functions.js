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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/src/js/bulk-export-functions.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/src/js/bulk-export-functions.js":
/*!************************************************!*\
  !*** ./assets/src/js/bulk-export-functions.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * Funktionen für den Massenexport.
 */
(function () {
  // Change-Events vom Posts-Filter-Formular abfangen, um auf Änderungen in
  // Bulk-Selects zu reagieren.
  var postsFilterForm = document.querySelector('#posts-filter');

  if (postsFilterForm) {
    // Markup für die Optionen zusammenbauen.
    var advancedOptionsDiv = document.createElement('div');
    emailField = document.createElement('input'), emailLabel = document.createElement('label'), noteField = document.createElement('textarea'), noteLabel = document.createElement('label'), choiceDownload = document.createElement('input'), choiceDownloadLabel = document.createElement('label'), choiceEmail = document.createElement('input'), choiceEmailLabel = document.createElement('label');
    advancedOptionsDiv.classList.add('xliff-bulk-export-options');
    choiceDownload.setAttribute('type', 'radio');
    choiceDownload.setAttribute('id', 'xliff-bulk-export-choice-download');
    choiceDownload.setAttribute('value', 'xliff-bulk-export-choice-download');
    choiceDownload.setAttribute('name', 'xliff-bulk-export-choice');
    choiceDownloadLabel.append(document.createTextNode('Download'));
    choiceDownloadLabel.setAttribute('for', 'xliff-bulk-export-choice-download');
    choiceEmail.setAttribute('type', 'radio');
    choiceEmail.setAttribute('id', 'xliff-bulk-export-choice-email');
    choiceEmail.setAttribute('value', 'xliff-bulk-export-choice-email');
    choiceEmail.setAttribute('name', 'xliff-bulk-export-choice');
    choiceEmailLabel.append(document.createTextNode('Als E-Mail senden'));
    choiceEmailLabel.setAttribute('for', 'xliff-bulk-export-choice-email');
    emailField.setAttribute('type', 'email');
    emailField.setAttribute('id', 'xliff-bulk-export-email');
    emailField.setAttribute('name', 'xliff-bulk-export-email');
    emailLabel.append(document.createTextNode('E-Mail-Adresse'));
    emailLabel.setAttribute('for', 'xliff-bulk-export-email');
    noteField.setAttribute('id', 'xliff-bulk-export-note');
    noteField.setAttribute('name', 'xliff-bulk-export-note');
    noteLabel.append(document.createTextNode('E-Mail-Text'));
    noteLabel.setAttribute('for', 'xliff-bulk-export-note');
    advancedOptionsDiv.append(choiceDownload);
    advancedOptionsDiv.append(choiceDownloadLabel);
    advancedOptionsDiv.append(choiceEmail);
    advancedOptionsDiv.append(choiceEmailLabel);
    advancedOptionsDiv.append(emailLabel);
    advancedOptionsDiv.append(emailField);
    advancedOptionsDiv.append(noteLabel);
    advancedOptionsDiv.append(noteField);
    postsFilterForm.addEventListener('change', function (e) {
      // Prüfen, ob einer der Bulk-Selects geändert wurde.
      if (e.target.id === 'bulk-action-selector-bottom' || e.target.id === 'bulk-action-selector-top') {
        if (e.target.value === 'xliff_bulk_export') {
          var bulkActionsWrapper = e.target.parentNode;
          bulkActionsWrapper.append(advancedOptionsDiv);
        }
      }
    });
  }
})();

/***/ })

/******/ });
//# sourceMappingURL=bulk-export-functions.js.map