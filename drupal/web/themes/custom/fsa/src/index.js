import _ from 'lodash';
import svg4everybody from 'svg4everybody';
import responsiveTables from './responsiveTables';
import stickyElement from './stickyElement';
import toggleContent from './toggleContent';

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./css/style.css');

// Polyfill svgs
//svg4everybody({ polyfill: true });

// Responsive tables
const tables = [...document.querySelectorAll('.js-table')];
responsiveTables(tables);

// Sticky element
const container = document.querySelectorAll('.js-sticky-container');
const stickyElem = document.querySelectorAll('.js-sticky-element');
stickyElement(container, stickyElem);

// Toggle content
const toggleButtons = document.querySelectorAll('.js-toggle-button');
toggleContent(toggleButtons);

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)
