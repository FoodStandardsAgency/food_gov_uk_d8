import "babel-polyfill";
import svg4everybody from 'svg4everybody';
import responsiveTables from './responsiveTables';
import stickyElement from './stickyElement';
import toggleContent from './toggleContent';
import toc from './toc';
import mobileMenu from './mobile-menu';
import regionalVariation from './regionalVariation';

const breakpoints = {
  small: "sm",
  medium: "md"
}

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./css/style.css');

// Polyfill svgs
svg4everybody({ polyfill: true });

// Temperary fix
const searchLogo = document.querySelector('.ratings.ratings--frontpage .ratings__logo');

if (searchLogo != null) {
  const searchHeading = document.querySelector('#fsa-ratings-search h2');
  searchHeading.classList.add('small');
  const searchLead = document.querySelector('#fsa-ratings-search p');
  searchLead.classList.add('lead');

  searchLogo.parentNode.insertBefore(searchLead, searchLogo.nextSibling);
  searchLogo.parentNode.insertBefore(searchHeading, searchLogo.nextSibling);
}

// Second temperary fix
// let pageTitle = document.querySelector('.js-quickedit-page-title');
//
// if (pageTitle == null) {
//   pageTitle = document.querySelector('#block-pagetitle');
// }
//
// if (pageTitle != null) {
//   pageTitle.classList.add('page-title');
//   const layoutArticle = document.querySelector('.layout__content').children[0];
//   layoutArticle.parentNode.insertBefore(pageTitle, layoutArticle);
// }

// Responsive tables
const tables = [...document.querySelectorAll('.js-table')];
if (tables != null) {
  responsiveTables(tables, breakpoints);
}

// Sticky element
const container = document.querySelectorAll('.js-sticky-container');
const stickyElem = document.querySelectorAll('.js-sticky-element');
if (container != null || stickyElem != null) {
  stickyElement(container, stickyElem);
}

// Toggle content
const toggleButtons = document.querySelectorAll('.js-toggle-button');
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


// Mobile menu
const regionalVariationElements = [...document.querySelectorAll('.js-regional-variation')];
if (regionalVariationElements.length > 0) {
  regionalVariation(regionalVariationElements);
}

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)
