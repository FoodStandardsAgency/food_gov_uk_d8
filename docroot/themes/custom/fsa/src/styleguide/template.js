/* eslint-disable import/no-webpack-loader-syntax */
import toCamelCase from 'to-camel-case'
import readme from '../../README.md'
import aboutThisStyleguide from './about.md'
import customProperties from '../helper/custom-property.css'
import styles from './styleguide.css'
import guid from '../helper/guid'
import safeTagsReplace from '../helper/safeTagsReplace'
import isColor from '../helper/isColor'
import fsaLogo from './fsa-logo'

const includedComponents = ['temporary-messaging', 'next-previous', 'back-to-top', 'document-menu', 'document-menu-side-bar', 'button', 'breadcrumb', 'contact-links', 'feedback', 'footer', 'form', 'header', 'hero', 'infobox', 'landing-links', 'landing-promo', 'language-bar', 'latest-news', 'link-list', 'listing', 'navigation', 'pager', 'pagination', 'promo', 'share']

function uniq(a) {
  return a.sort().filter(function (item, pos, ary) {
    return !pos || item !== ary[pos - 1]
  })
}

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function parsePartialMarkup (string) {
  const re = /= "|";/
  return string.split(re)[1]
}

function preprocessHTML (array) {
  let string = []
  array.forEach(element => {
    string = [...string, safeTagsReplace(element)]
  })
  return string.join('')
}

function preprocessCSS (array) {
  return array.map(element => {
    return element.replace(/"/g, '&quot;')
  }).join('')
}

function preprocessJS (array) {
  return array.map(element => {
    return element
  }).join('')
}

const customPropertiesArray = customProperties.toString().split(/[{}]+/).filter(function (e) { return e })

const parts = customPropertiesArray[1].replace(/\s/g, '').split(';')
const colorArray = []
const numberOfColors = 18
for (let i = 0; i < numberOfColors; i++) {
  const subParts = parts[i].split(':')

  if (isColor(subParts[1])) {
    colorArray.push({
      customProperty: subParts[0],
      value: subParts[1]
    })
  }
}

const colors = colorArray.map((color) => {
  return parsePartialMarkup(require('template-string-loader!./partial/color.html')({
    guid: guid(),
    customProperty: (color.customProperty !== undefined) ? color.customProperty : 'color',
    value: (color.value !== undefined) ? color.value : 'Not available',
    styles
  }))
}).join('')

const intro = parsePartialMarkup(require('template-string-loader!./partial/intro.html')({
  guid: guid(),
  title: 'CSS Custom Properties',
  content: customProperties,
  styles
}))

const typography = parsePartialMarkup(require('template-string-loader!./partial/typography.html')({
  guid: guid(),
  title: 'Typography',
  styles
}))

const requiredHTMLComponents = require.context('../component/', true, /\.html$/)
const requiredCSSComponents = require.context('../component/', true, /\.css$/)
const requiredJSComponents = require.context('../component/', true, /\.js$/)

const componentNameArrayConstructor = (html, css, js) => {
  let combinedComponentArray = [...html, ...css, ...js]
  combinedComponentArray = [...combinedComponentArray.map((element) => {
    const componentName = element.split('/')[1]
    return componentName
  })]

  return combinedComponentArray
}

const componentNameArray = uniq(componentNameArrayConstructor(requiredHTMLComponents.keys(), requiredCSSComponents.keys(), requiredJSComponents.keys()))
  .filter(name => [...includedComponents].includes(name))

const componentArray = componentNameArray.map((componentName) => {
  let HTMLArray = []
  let CSSArray = []
  let JSArray = []

  requiredHTMLComponents.keys().forEach((key) => {
    if (componentName === key.split('/')[1]) {
      HTMLArray = [...HTMLArray, requiredHTMLComponents(key)]
    }
  })

  requiredCSSComponents.keys().forEach((key) => {
    if (componentName === key.split('/')[1]) {
      CSSArray = [...CSSArray, requiredCSSComponents(key)]
    }
  })

  requiredJSComponents.keys().forEach((key) => {
    if (componentName === key.split('/')[1]) {
      JSArray = [...JSArray, requiredJSComponents(key)]
    }
  })

  if (componentName === 'pager') {
    componentName = 'pagination'
  }

  return {
    title: capitalizeFirstLetter(componentName),
    html: HTMLArray,
    css: CSSArray,
    js: JSArray
  }
})

const components = componentArray.map((component) => {
  return parsePartialMarkup(require('template-string-loader!./partial/component.html')({
    guid: guid(),
    id: toCamelCase(component.title),
    title: (component.title !== undefined) ? component.title : 'Component',
    description: (component.description !== undefined) ? component.description : '',
    element: (component.html !== undefined) ? component.html : 'Not available',
    html: (component.html !== undefined) ? preprocessHTML(component.html) : 'Not available',
    css: (component.css !== undefined) ? preprocessCSS(component.css) : 'Not available',
    js: (component.js !== undefined) ? preprocessJS(component.js) : 'Not available',
    styles
  }))
}).join('')

const navigationComponentItems = componentArray.map((component) => {
  return parsePartialMarkup(require('template-string-loader!./partial/navigationItem.html')({
    guid: guid(),
    id: toCamelCase(component.title),
    title: (component.title !== undefined) ? component.title : 'Component',
    styles
  }))
}).join('')

const introComponentArray = [
  {
    title: 'About',
    element: aboutThisStyleguide
  },
  {
    title: 'Developer Workflow',
    element: readme
  },
  {
    title: 'CSS Custom Properties',
    description: 'These are custom properties, which are defined in the file `custom-property.css`. They can be used with var() function. There are also some examples of this function being used in the code below.',
    element: intro
  },
  {
    title: 'Colours',
    description: `The colours used throughout this project are based on the FSA Brand Guidelines. Any further modifications to this site should consult this document and/or the FSA design team.
    Below, colours from the FSA Brand Guidelines are shown. These are all present in the \`custom-property.css\` file, along with other colours used throughout the site.`,
    element: colors
  },
  {
    title: 'Typography',
    description: `Here are some examples of the typography used throughout the site. The font family is Fira Sans for headings and Open Sans for content. Font styles are set with mixins and custom properties as shown above, and are overridden with more specific styles where appropriate.`,
    element: typography
  }
]

const introComponents = introComponentArray.map((component) => {
  return parsePartialMarkup(require('template-string-loader!./partial/introComponent.html')({
    guid: guid(),
    id: toCamelCase(component.title),
    title: (component.title !== undefined) ? component.title : 'Component',
    description: (component.description !== undefined) ? component.description : '',
    element: (component.element !== undefined) ? component.element : 'Not available',
    styles
  }))
}).join('')

const navigationIntroComponentItems = introComponentArray.map((component) => {
  return parsePartialMarkup(require('template-string-loader!./partial/navigationItem.html')({
    guid: guid(),
    id: toCamelCase(component.title),
    title: (component.title !== undefined) ? component.title : 'Component',
    styles
  }))
}).join('')

const styleGuide = (templateParams) => {
  return `
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>food.gov.uk style guide</title>
        <style>
          ${styles}
        </style>
        <link href="app.css" rel="stylesheet">
        <link href="editor.css" rel="stylesheet">
      </head>
      <body>
       <article>
        <section class="${styles.locals.hero}">
          <div class="${styles.locals.heroContainer}">
            ${fsaLogo}
            <span class=""><strong>food.gov.uk</strong> style guide </span>
          </div>
        </section>
        <div id="${styles.locals.container}">
          <section class="${styles.locals.layout}">
            <aside class="${styles.locals.navigation}">
              <h3 class="${styles.locals.navigation__heading}">Getting started</h3>
              <div class="${styles.locals.navigation__links}">
                ${navigationIntroComponentItems}
              </div>
              <h3 class="${styles.locals.navigation__heading}">Components</h3>
              <div class="${styles.locals.navigation__links}">
                ${navigationComponentItems}
              </div>
            </aside>
            <main class="${styles.locals.layout__content} ${styles.locals.main}">
              ${introComponents}
              <h1>Components</h1>
              <hr>
              ${components}
            </main>
          </section>
        </div>
      </article>
      <link rel="stylesheet"
      href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.12.0/build/styles/default.min.css">
      <script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.12.0/build/highlight.min.js"></script>
      <script>hljs.initHighlightingOnLoad();</script>
      <script type="text/javascript" src="app.js"></script>
      <script type="text/javascript" src="editor.js"></script>
      <script type="text/javascript" src="styleguide.js"></script>
      </body>
    </html>
  `
}

export default styleGuide
