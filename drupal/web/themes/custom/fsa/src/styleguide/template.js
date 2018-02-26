/* eslint-disable import/no-webpack-loader-syntax */
import toCamelCase from 'to-camel-case'
import readme from '../../README.md'
import customProperties from '../helper/custom-property.css'
import styles from './styleguide.css'
import guid from '../helper/guid'
import safeTagsReplace from '../helper/safeTagsReplace'
import isColor from '../helper/isColor'

// Parse partial markup
function parsePartialMarkup (string) {
  const re = /= "|";/
  return string.split(re)[1]
}

// Preprocess html
function preprocessHTML (array) {
  let string = []
  array.forEach(element => {
    string = [...string, safeTagsReplace(element)]
  })
  return string.join('')
}

// Preprocess css
function preprocessCSS (array) {
  return array.map(element => {
    return element.replace(/"/g, '&quot;')
  }).join('')
}

// Preprocess js
function preprocessJS (array) {
  return array.map(element => {
    return element
  }).join('')
}

const myArray = customProperties.toString().split(/[{}]+/).filter(function (e) { return e })

const parts = myArray[1].replace(/\s/g, '').split(';')
const colorArray = []
for (let i = 0; i < parts.length; i++) {
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

const requiredHTMLComponents = require.context('../component/', true, /\.html$/)
const requiredCSSComponents = require.context('../component/', true, /\.css$/)
const requiredJSComponents = require.context('raw-loader!../component/', true, /\.js$/)

function uniq (a) {
  return a.sort().filter(function (item, pos, ary) {
    return !pos || item !== ary[pos - 1]
  })
}

const componentNameArrayConstructor = (html, css, js) => {
  let combinedComponentArray = [...html, ...css, ...js]
  combinedComponentArray = [...combinedComponentArray.map((element) => {
    const componentName = element.split('/')[1]
    return componentName
  })]

  return combinedComponentArray
}

const componentNameArray = uniq(componentNameArrayConstructor(requiredHTMLComponents.keys(), requiredCSSComponents.keys(), requiredJSComponents.keys()))

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

  return {
    title: componentName,
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
    title: 'Workflow',
    element: readme
  },
  {
    title: 'CSS Custom Properties',
    description: 'These are custom properties. Use them with var() function',
    element: intro
  },
  {
    title: 'Colors',
    element: colors
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
  const html =
    `
    <html>
      <head>
        <title>${templateParams.htmlWebpackPlugin.options.title}</title>
        <style>
          ${styles}
        </style>
      </head>
      <body class="${styles.locals.styleguide}">
       <article class="${styles.locals.styleGuide}">
        <section class="${styles.locals.hero}">
          <span><b>${templateParams.htmlWebpackPlugin.options.title}</b></span> — <span class="${styles.locals.underline}">Technical Style Guide </span>
        </section>
        <section class="${styles.locals.layout} js-sticky-container">
          <aside class="${styles.locals.navigation} js-sticky-element">
            <h3 class="${styles.locals.navigation__heading}">Getting started</h3>
            ${navigationIntroComponentItems}
            <h3 class="${styles.locals.navigation__heading}">Components</h3>
            ${navigationComponentItems}
          </aside>
          <main class="${styles.locals.layout__content} ${styles.locals.main}">
            ${introComponents}
            ${components}
          </main>
        </section>
      </article>
      </body>
    </html>
    `
  return html
}

export default styleGuide
