import 'babel-polyfill';
import './core/helper/polyfill/classList';
import './core/helper/polyfill/closest';
import svg4everybody from 'svg4everybody';
import responsiveTables from './core/helper/responsiveTables';
import stickyElement from './core/helper/stickyElement';
import toggleContent from './core/helper/toggleContent';
import toc from './core/helper/toc';
import mobileMenu from './core/helper/mobile-menu';
import subNavigation from './core/helper/subNavigation';
import printPage from './core/helper/printPage';

import regionalVariation from './component/regional-variation/regionalVariation';
import explanation from './component/explanation/explanation';
import parallax from './component/parallax/parallax';


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

  // Regional variation
  regionalVariation();

  // Explanation
  explanation();

  // Parallax
  parallax();

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
const toggleButtons = [...document.querySelectorAll('.js-toggle-button')];
if (toggleButtons != null) {
  toggleContent(toggleButtons, breakpoints);
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

// Table of contents
const tableOfContentsElements = [...document.querySelectorAll('.toc-tree')];
const contentElements = [...document.querySelectorAll('.toc-filter')];
if (tableOfContentsElements.length > 0 || contentElements.length > 0) {
  toc(tableOfContentsElements, contentElements);
}

// Mobile menu
const menuButtonElements = document.querySelectorAll('.js-menu-button');
const navigationElement = document.querySelector('.js-navigation');
const siteElement = document.querySelector('.js-site');
if (menuButtonElements != null || navigationElement != null || siteElement != null) {
  mobileMenu(menuButtonElements, navigationElement, siteElement);
}

// Print page
const printPDFWrapperElements = [...document.querySelectorAll('.print__wrapper--pdf')];
if (printPDFWrapperElements.length > 0) {
  printPage(printPDFWrapperElements);
}

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)
