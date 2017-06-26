import _ from 'lodash';
import svg4everybody from 'svg4everybody';
import responsiveTables from './responsiveTables';

// Require every image asset inside of img folder
require.context("./img/", true, /\.(gif|png|svg|jpe?g)$/);
require('./css/style.css');

// Polyfill svgs
svg4everybody({ polyfill: true });

// Responsive tables
const tables = [...document.querySelectorAll('.js-table')];
responsiveTables(tables);

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass(e) {
  document.documentElement.classList.add('is-touch');
  document.removeEventListener('touchstart', addtouchclass, false);
}, false)
