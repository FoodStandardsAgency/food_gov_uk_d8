import "babel-polyfill";
import svg4everybody from 'svg4everybody';
import responsiveTables from './responsiveTables';
import stickyElement from './stickyElement';
import toggleContent from './toggleContent';
import toc from './toc';
import mobileMenu from './mobile-menu';
import regionalVariation from './regionalVariation';
import printPage from './printPage';

const breakpoints = {
  small: "sm",
  medium: "md"
}

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./css/style.css');

// Polyfill svgs
// svg4everybody({ polyfill: true });

// Temperary fix
const searchLogoElement = document.querySelector('.ratings.ratings--frontpage .ratings__logo');

if (searchLogoElement != null) {
  const searchHeading = document.querySelector('#fsa-ratings-search h2');
  searchHeading.classList.add('small');
  const searchLead = document.querySelector('#fsa-ratings-search p');
  searchLead.classList.add('lead');

  searchLogoElement.parentNode.insertBefore(searchLead, searchLogoElement.nextSibling);
  searchLogoElement.parentNode.insertBefore(searchHeading, searchLogoElement.nextSibling);
}

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

// Regional variations
const regionalVariationElements = [...document.querySelectorAll('.js-regional-variation')];
if (regionalVariationElements.length > 0) {
  regionalVariation(regionalVariationElements);
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
