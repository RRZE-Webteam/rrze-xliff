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
    var advancedOptionsDiv = document.createElement('div'),
        emailWrapper = document.createElement('div'),
        emailFieldWrapper = document.createElement('p'),
        emailField = document.createElement('input'),
        emailLabel = document.createElement('label'),
        noteWrapper = document.createElement('p'),
        noteField = document.createElement('textarea'),
        noteLabel = document.createElement('label'),
        choiceDownloadWrapper = document.createElement('p'),
        choiceDownload = document.createElement('input'),
        choiceDownloadLabel = document.createElement('label'),
        choiceEmailWrapper = document.createElement('p'),
        choiceEmail = document.createElement('input'),
        choiceEmailLabel = document.createElement('label');
    advancedOptionsDiv.classList.add('xliff-bulk-export-options');
    choiceDownload.setAttribute('type', 'radio');
    choiceDownload.setAttribute('checked', 'checked');
    choiceDownload.setAttribute('id', 'xliff-bulk-export-choice-download');
    choiceDownload.setAttribute('value', 'xliff-bulk-export-choice-download');
    choiceDownload.setAttribute('name', 'xliff-bulk-export-choice');
    choiceDownloadLabel.append(document.createTextNode('Download'));
    choiceDownloadLabel.setAttribute('for', 'xliff-bulk-export-choice-download');
    choiceDownloadWrapper.append(choiceDownload);
    choiceDownloadWrapper.append(choiceDownloadLabel);
    choiceEmail.setAttribute('type', 'radio');
    choiceEmail.setAttribute('id', 'xliff-bulk-export-choice-email');
    choiceEmail.setAttribute('value', 'xliff-bulk-export-choice-email');
    choiceEmail.setAttribute('name', 'xliff-bulk-export-choice');
    choiceEmailLabel.append(document.createTextNode('Als E-Mail senden'));
    choiceEmailLabel.setAttribute('for', 'xliff-bulk-export-choice-email');
    choiceEmailWrapper.append(choiceEmail);
    choiceEmailWrapper.append(choiceEmailLabel);
    emailWrapper.classList.add('xliff-bulk-export-email-wrapper');
    emailWrapper.setAttribute('hidden', 'hidden');
    emailField.setAttribute('type', 'email');
    emailField.setAttribute('id', 'xliff-bulk-export-email');
    emailField.setAttribute('name', 'xliff-bulk-export-email');
    emailLabel.append(document.createTextNode('E-Mail-Adresse'));
    emailLabel.setAttribute('for', 'xliff-bulk-export-email');
    emailLabel.setAttribute('style', 'display: block;');
    emailFieldWrapper.append(emailLabel);
    emailFieldWrapper.append(emailField);
    noteField.setAttribute('id', 'xliff-bulk-export-note');
    noteField.setAttribute('name', 'xliff-bulk-export-note');
    noteField.setAttribute('style', 'width: 100%;');
    noteLabel.append(document.createTextNode('E-Mail-Text'));
    noteLabel.setAttribute('for', 'xliff-bulk-export-note');
    noteLabel.setAttribute('style', 'display: block;');
    noteWrapper.append(noteLabel);
    noteWrapper.append(noteField);
    emailWrapper.append(emailFieldWrapper);
    emailWrapper.append(noteWrapper);
    advancedOptionsDiv.append(choiceDownloadWrapper);
    advancedOptionsDiv.append(choiceEmailWrapper);
    advancedOptionsDiv.append(emailWrapper);
    postsFilterForm.addEventListener('change', function (e) {
      // Prüfen, ob einer der Bulk-Selects geändert wurde.
      if (e.target.id === 'bulk-action-selector-bottom' || e.target.id === 'bulk-action-selector-top') {
        if (e.target.value === 'xliff_bulk_export') {
          var bulkActionsWrapper = e.target.parentNode;
          bulkActionsWrapper.append(advancedOptionsDiv);
        } else {
          var childNodes = e.target.parentNode.childNodes;
          var _iteratorNormalCompletion = true;
          var _didIteratorError = false;
          var _iteratorError = undefined;

          try {
            for (var _iterator = childNodes[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
              var childNode = _step.value;

              if (childNode.classList !== undefined && childNode.classList.contains('xliff-bulk-export-options')) {
                childNode.remove();
              }
            }
          } catch (err) {
            _didIteratorError = true;
            _iteratorError = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion && _iterator.return != null) {
                _iterator.return();
              }
            } finally {
              if (_didIteratorError) {
                throw _iteratorError;
              }
            }
          }
        }
      }

      if (e.target.name === 'xliff-bulk-export-choice' && e.target.id === 'xliff-bulk-export-choice-email') {
        emailWrapper.removeAttribute('hidden');
      } else if (e.target.name === 'xliff-bulk-export-choice') {
        emailWrapper.setAttribute('hidden', 'hidden');
      }
    });
  }
})();

/***/ })

/******/ });
//# sourceMappingURL=bulk-export-functions.js.map