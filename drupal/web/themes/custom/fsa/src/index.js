import 'babel-polyfill';
import './core/helper/polyfill/classList';
import './core/helper/polyfill/closest';
import svg4everybody from 'svg4everybody';
import responsiveTables from './core/helper/responsiveTables';
import stickyElement from './core/helper/stickyElement';
import cssCustomPropertySupport from './core/helper/cssCustomPropertySupport';
import subNavigation from './core/helper/subNavigation';

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


// Temperary fix
// const searchLogoElement = document.querySelector('.ratings.ratings--frontpage .ratings__text');

// if (searchLogoElement != null) {
//   const searchHeading = document.querySelector('#fsa-ratings-search h2');
//   searchHeading.classList.add('small');
//   const searchLead = document.querySelector('#fsa-ratings-search p');
//   searchLead.classList.add('lead');

//   searchLogoElement.parentNode.insertBefore(searchLead, searchLogoElement.nextSibling);
//   searchLogoElement.parentNode.insertBefore(searchHeading, searchLogoElement.nextSibling);
// }

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

// Subnavigation
const subNavigationElement = document.getElementById('block-menu-help-secondary');
if (subNavigationElement != null) {
  subNavigation(subNavigationElement);
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

