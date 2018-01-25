import 'babel-polyfill';
import './core/helper/polyfill/classList';
import './core/helper/polyfill/closest';
import svg4everybody from 'svg4everybody';
import responsiveTables from './core/helper/responsiveTables';
import stickyElement from './core/helper/stickyElement';
import cssCustomPropertySupport from './core/helper/cssCustomPropertySupport';

import navigation from './component/navigation/navigation';
import addHeading from './component/content/content';
import toggle from './component/toggle/toggle';
import peek from './component/peek/peek';
import fhrs from './component/fhrs/fhrs';
import toc from './component/toc/toc';

const breakpoints = {
  small: "sm",
  medium: "md"
}

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./style.css');

document.addEventListener('DOMContentLoaded', () => {
  // Polyfill svgs
  svg4everybody({ polyfill: true });

  // Add heading
  addHeading();

  // peek
  peek();

  // Navigation
  navigation();

  // Toggle content
  toggle();

  // FHRS
  fhrs();

  // Toc
  toc();
});


// Responsive tableElements
const tableElements = [...document.querySelectorAll('.js-table')];
if (tableElements != null) {
  responsiveTables(tableElements, breakpoints);
}

// Sticky element
const container = [...document.querySelectorAll('.js-sticky-container')];
const stickyElem = [...document.querySelectorAll('.js-sticky-element')];
if (container != null || stickyElem != null) {
  stickyElement(container, stickyElem);
}

// Toggle content
const profileElement = document.getElementById('block-myprofile');
if (profileElement != null) {
  const profileElementArray = [...document.getElementById('block-myprofile').children];
  toggleContent(profileElementArray[0], breakpoints, profileElementArray[2]);
}

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)

// Add class if css custom properties are supported
if (cssCustomPropertySupport()) {
  document.documentElement.classList.add('is-modern');
}

