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
/******/ 	return __webpack_require__(__webpack_require__.s = "./styleguide/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./helper/scrollToElement.js":
/*!***********************************!*\
  !*** ./helper/scrollToElement.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// Helper animate scrolling function with requestAnimationFrame
function doScrolling(element, duration, offset) {
  var startingY = window.pageYOffset;
  var elementY = window.pageYOffset + element.getBoundingClientRect().top;
  // If element is close to page's bottom then window will scroll only to some position above the element.
  var targetY = document.body.scrollHeight - elementY < window.innerHeight ? document.body.scrollHeight - window.innerHeight + offset : elementY + offset;
  var diff = targetY - startingY;
  // Easing function: easeInOutCubic
  // From: https://gist.github.com/gre/1650294
  var easing = function easing(t) {
    return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
  };
  var start;

  if (!diff) return;

  // Bootstrap our animation - it will get called right before next frame shall be rendered.
  window.requestAnimationFrame(function step(timestamp) {
    if (!start) start = timestamp;
    // Elapsed miliseconds since start of scrolling.
    var time = timestamp - start;
    // Get percent of completion in range [0, 1].
    var percent = Math.min(time / duration, 1);
    // Apply the easing.
    // It can cause bad-looking slow frames in browser performance tool, so be careful.
    percent = easing(percent);

    window.scrollTo(0, startingY + diff * percent);

    // Proceed with animation as long as we wanted it to.
    if (time < duration) {
      window.requestAnimationFrame(step);
    }
  });
}

module.exports = doScrolling;

/***/ }),

/***/ "./helper/stickyElement.js":
/*!*********************************!*\
  !*** ./helper/stickyElement.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function stickyElement(containers, stickyElements) {
  // Area class
  var Area = function () {
    function Area(element) {
      _classCallCheck(this, Area);

      this.element = element;
    }

    _createClass(Area, [{
      key: 'calcOffset',
      value: function calcOffset() {
        return this.element.getBoundingClientRect().top;
      }
    }, {
      key: 'calcInview',
      value: function calcInview() {
        var rect = this.element.getBoundingClientRect();
        return rect.top - window.innerHeight <= 0 && rect.bottom >= 0;
      }
    }, {
      key: 'calcBottom',
      value: function calcBottom() {
        var elementHeight = this._relatedInstance.element.offsetHeight;
        return this.element.getBoundingClientRect().bottom <= elementHeight;
      }
    }, {
      key: 'relatedInstance',
      set: function set(item) {
        this._relatedInstance = item;
      },
      get: function get() {
        return this._relatedInstance;
      }
    }, {
      key: 'thisElement',
      get: function get() {
        return this.element;
      }
    }, {
      key: 'offset',
      get: function get() {
        return this.calcOffset();
      }
    }, {
      key: 'inview',
      get: function get() {
        return this.calcInview();
      }
    }, {
      key: 'isBottom',
      get: function get() {
        return this.calcBottom();
      }
    }]);

    return Area;
  }();

  // Content section class


  var Section = function (_Area) {
    _inherits(Section, _Area);

    function Section() {
      _classCallCheck(this, Section);

      return _possibleConstructorReturn(this, (Section.__proto__ || Object.getPrototypeOf(Section)).apply(this, arguments));
    }

    return Section;
  }(Area);

  var containerArray = [];
  var stickyElementArray = [];

  // Push all containerArray into an array
  for (var _i = 0; _i < containers.length; _i++) {
    // Query all content sections inside area
    var allSections = stickyElements;

    // Loop through every sections inside current content area
    for (var y = 0; y < allSections.length; y++) {
      var stickyScrollableElement = allSections[y];
      stickyElementArray.push(new Section(stickyScrollableElement));
    }

    containerArray.push(new Area(containers[_i]));
  }

  // Set related instance for each instance of the container
  for (var i = 0; i < containerArray.length; i++) {
    containerArray[i].relatedInstance = stickyElementArray[i];
  }

  // Function to toggle sticky navigation
  var toggleStickyElement = function toggleStickyElement() {
    containerArray.forEach(function (container) {
      // Check if element is bottom of the content area
      if (container.isBottom) {
        container.relatedInstance.element.classList.add('is-bottom');
      } else {
        container.relatedInstance.element.classList.remove('is-bottom');
      }

      // Check if element is in view
      if (container.inview && container.offset < 0) {
        container.relatedInstance.element.classList.add('is-sticky');
      } else {
        container.relatedInstance.element.classList.remove('is-sticky');
      }
    });
  };

  // Add scroll listener
  window.addEventListener('scroll', toggleStickyElement);

  // Add load listener
  window.addEventListener('load', toggleStickyElement);
}

module.exports = stickyElement;

/***/ }),

/***/ "./styleguide/index.js":
/*!*****************************!*\
  !*** ./styleguide/index.js ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _stickyElement = __webpack_require__(/*! ../helper/stickyElement */ "./helper/stickyElement.js");

var _stickyElement2 = _interopRequireDefault(_stickyElement);

var _scrollToElement = __webpack_require__(/*! ../helper/scrollToElement */ "./helper/scrollToElement.js");

var _scrollToElement2 = _interopRequireDefault(_scrollToElement);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

// Fix issues with 100vh flex height
document.querySelector('.navigation-wrapper__main').style.height = '82px';
document.querySelectorAll('.promo-listing__item').forEach(function (promo) {
  promo.style.height = '400px';
});
document.querySelectorAll('.field__field_content_reference__item').forEach(function (el) {
  el.style.height = '130.5px';
  el.style.marginBottom = '17.5px';
});

function resizeLatestNews() {
  document.querySelectorAll('div.promo-wrapper').forEach(function (el) {
    el.style.height = window.innerWidth < 1280 ? '183px' : '286px';
  });
}

// Sticky element
var container = [].concat(_toConsumableArray(document.querySelectorAll('.js-sticky-container')));
var stickyElem = [].concat(_toConsumableArray(document.querySelectorAll('.js-sticky-element')));
if (container != null || stickyElem != null) {
  (0, _stickyElement2.default)(container, stickyElem);
}

var scrollElementArray = [].concat(_toConsumableArray(document.querySelectorAll('.js-scroll')));
if (scrollElementArray != null) {
  for (var i = 0; i < scrollElementArray.length; i++) {
    var thisTocNavigationItem = scrollElementArray[i];

    thisTocNavigationItem.addEventListener('click', function (e) {
      e.preventDefault();

      var id = this.href.substr(this.href.indexOf('#') + 1);
      var currentHeading = document.getElementById(id);

      // Scroll
      (0, _scrollToElement2.default)(currentHeading, 1000, -20);
    });
  }
}

// Show or hide code using checkbox
var checkboxes = document.querySelectorAll('input[type="checkbox"].show-code');
checkboxes.forEach(function (checkbox) {
  checkbox.addEventListener('change', function () {
    var code = document.querySelector('form.' + this.dataset.correspondingCode);
    code.classList.toggle('hidden');
  });
});

resizeLatestNews();
window.addEventListener('resize', function () {
  resizeLatestNews();
});

/***/ })

/******/ });
//# sourceMappingURL=styleguide.js.map