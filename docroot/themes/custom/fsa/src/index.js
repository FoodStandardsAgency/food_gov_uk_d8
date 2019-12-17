import 'babel-polyfill'
import 'mutationobserver-shim'
import 'wicg-inert'
import 'custom-event-polyfill'

import './helper/polyfill/classList'
import './helper/polyfill/closest'
import './helper/polyfill/matches'
import './helper/polyfill/pointerEvents'
import svg4everybody from 'svg4everybody'
import responsiveTables from './helper/responsiveTables'
import stickyElement from './helper/stickyElement'
// import cssCustomPropertySupport from './helper/cssCustomPropertySupport'

import navigation from './component/navigation/navigation'
import addHeading from './component/content/content'
import toggle from './component/toggle/toggle'
import peek from './component/peek/peek'
import fhrs from './component/fhrs/fhrs'
import toc from './component/toc/toc'
import { changeAriaRoleOnToggle, autoOpenFormError, scrollToMultiStepForm } from './component/form/form'
import autoOpenFirstSearchFilter from './component/search/search'
import toggleSidebarDocumentMenu from './component/document-menu-side-bar/document-menu-side-bar';
import webFormError from './component/webform-error/webform-error'

// Require every image asset inside of img folder
require.context('./img/', true, /\.(gif|png|svg|jpe?g)$/)

// Require application style
require('./style.css')

document.addEventListener('DOMContentLoaded', () => {
  // Polyfill svgs
  svg4everybody({ polyfill: true })

  // Add heading
  addHeading()

  // peek
  peek()

  // Navigation
  navigation()

  // Toggle content
  toggle()

  // FHRS
  fhrs()

  // Toc
  toc()

  // Responsive tables
  responsiveTables()

  // Forms
  changeAriaRoleOnToggle()
  autoOpenFormError()
  scrollToMultiStepForm()

  webFormError()

  // Auto-open first search filter (on desktop only)
  autoOpenFirstSearchFilter()

  // Add the toggle to the document menu.
  toggleSidebarDocumentMenu();
})

// Sticky element
const container = [...document.querySelectorAll('.js-sticky-container')]
const stickyElem = [...document.querySelectorAll('.js-sticky-element')]
if (container != null || stickyElem != null) {
  stickyElement(container, stickyElem)
}

// Add class if touch device
document.addEventListener('touchstart', function addtouchclass (e) {
  document.documentElement.classList.add('is-touch')
  document.removeEventListener('touchstart', addtouchclass, false)
}, false)

// // Add class if css custom properties are supported
// if (cssCustomPropertySupport()) {
//   document.documentElement.classList.add('is-modern')
// }
