import _ from 'lodash';
import svg4everybody from 'svg4everybody';
import responsiveTables from './responsiveTables';
import stickyElement from './stickyElement';
import toggleContent from './toggleContent';

const breakpoints = {
  small: "sm",
  medium: "md"
}

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./css/style.css');

// Polyfill svgs
svg4everybody({ polyfill: true });

// Responsive tables
const tables = [...document.querySelectorAll('.js-table')];
if (tables != null) {
  responsiveTables(tables);
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

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)
