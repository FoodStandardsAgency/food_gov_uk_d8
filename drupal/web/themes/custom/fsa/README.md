# 🍴 FSA Drupal Theme

## Requirements
FSA Theme uses ES6 features, so it is recommended to have a latest version of [Node.js](https://nodejs.org/) installed locally ([Node.js](https://nodejs.org/) v4.0+ required). [Node.js](https://nodejs.org/) is the only global requirement as this project does not use any task runners. We are using [Webpack](https://webpack.js.org/) instead.

## Installation
To get the started with theming, complete the following steps:

1. Install [Node.js](https://nodejs.org/)

  * If you are using macOS please install [Homebrew](https://brew.sh/index_fi.html) first. Then install both [Yarn](https://yarnpkg.com/lang/en/) (depency manager) and [Node.js](https://nodejs.org/) at the same time by running: `brew install yarn`

2. Install project dependencies by running the following task
  * `npm install` or `yarn`

## 🏗 Development
When developing FSA Theme, you may want assets automatically compiled. To do this, run the following watch task:

* `npm run watch` or `yarn watch`

This will create a folder called `dist`, into which the required assets will be created.

### JavaScript development
The file `index.js` is the entry point for all the assests used by this theme. This file contains `require` function for style and image files. This function should not be removed ever.

Npm dependencies and theme related [modular JavaScript files](https://github.com/lukehoban/es6features#modules) can be imported at the beginning of every JavaScript file with following line of code:

```
import defaultMember from 'module-name'
```

[ES6 import syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/import). To export a JavaScript module use [ES6 export syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/export) such as `module.exports = myFunction;`.

There are number of other really useful ES6 features that can be also used, like:
* [Arrows](https://github.com/lukehoban/es6features#arrows)
* [Classes](https://github.com/lukehoban/es6features#classes)
* [Template strings](https://github.com/lukehoban/es6features#template-strings)
* [Destructuring](https://github.com/lukehoban/es6features#destructuring)
* [Spread](https://github.com/lukehoban/es6features#default--rest--spread)
* [Block-scoped binding constructs](https://github.com/lukehoban/es6features#let--const)

### CSS development
This theme uses number of postCSS plugins with  [postCSS-cssnext](http://cssnext.io/) plugin collection. Cssnext helps developer to use the latest CSS syntax today. Active plugins of the cssnext collection can be managed inside of a `postcss.config.js` file and available features can be browsed on [GitHub](https://github.com/MoOx/postcss-cssnext/blob/master/docs/content/features.md). All the css files are extracted and bundled into one file with Webpack [_extract-text-webpack-plugin_](https://github.com/webpack-contrib/extract-text-webpack-plugin).

#### Using CSS Custom Properties
_Related PostCSS plugin: [postcss-custom-properties](https://github.com/postcss/postcss-custom-properties)_

[CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*) can be used in this theme. To support [older IE browsers](http://caniuse.com/#feat=css-variables) CSS custom properties should only be used inside of a `:root` selector. Cssnext has been configurated to preserve custom property values.

CSS Custom properties are defined at the beginning of the `base.css` file with following line of code:

```
:root {
  --font-size-base: 112.5%;
}
```

CSS Custom properties can be reused throughout the CSS files using the (var()) function.
```
html {
  font-size: var(--font-size-base);
}
```

#### Media Queries
_Related PostCSS plugin: [postcss-custom-media](https://github.com/postcss/postcss-custom-media)_

As this theme uses _mobile first_ approach all media queries should use `min-width`.

Custom media queries are defined at the beginning of the `base.css` file with following line of code:

```
@custom-media --breakpoint-sm (min-width: 50em);
```

Custom variables can be used with a following line of code inside of a css file:

```
@media (--breakpoint-sm) {
  width: 100%;
}
```

#### Using PostCSS Mixins
_Related PostCSS plugin: [postcss-custom-media](https://github.com/postcss/postcss-mixins)_

Cssnext is providing a [PostCSS-apply](https://github.com/pascalduez/postcss-apply) plugin but it doesn't support arguments. That's why PostCSS-mixin plugin is also included in this theme.

PostCSS Mixin is currently only used for defining one mixin:

```
@define-mixin responsive-declaration $property, $valueMin, $valueMax {
  $(property): $valueMin;

  @media (--breakpoint-xs) {
    $(property): calc($valueMin + (($valueMax - $valueMin) / 4) * 1);
  }

  @media (--breakpoint-sm) {
    $(property): calc($valueMin + (($valueMax - $valueMin) / 4) * 2);
  }

  @media (--breakpoint-md) {
    $(property): calc($valueMin + (($valueMax - $valueMin) / 4) * 3);
  }

  @media (--breakpoint-lg) {
    $(property): calc($valueMin + (($valueMax - $valueMin) / 4) * 4);
  }
}
```
This responsive-declaration mixin is used throughout the CSS files to help creating both mobile and desktop styles with one line of code. This is great for declaring _paddings_, _margins_, _font sizes_ etc.


Mixin takes three arguments: _Property name_, _Minimum value_ and _Maximum value_. Responsive-declaration mixin can be used with following line of code:

```
h1 {
  @mixin responsive-declaration font-size, 2.2em, 3.333em;
}
```

#### CSS Naming conventions
This theme uses [BEM](http://getbem.com/) as much as it is possible.

### Bitmap and vector assets
Webpack loaders check for bitmap and vector images separately. Bitmap images are compressed and copied to `dist/img/` folder. All the vector images on the other hand are turned into one sprite (`dist/sprite.svg`). Developer can reference to a specific vector image with this line of code:

```
<div class="svg">
  <svg><use xlink:href="/themes/custom/fsa/dist/sprite.svg#name-of-the-svg-file"></use></svg>
</div>
```
To polyfill browsers that don't support `<use>` tags every svg reference will be turned into inline svg by browser with [svg4everybody](https://github.com/jonathantneal/svg4everybody) JavaScript plugin.


## 📦 Building the theme assets
Remember to create the static assets of this theme before committing your changes to version control. Build the assets by running the following task:

* `npm run build` or `yarn build`

## Repository structure
Theme folder/file structure

```
├─ dist/               # Distributed assets that Drupal uses
│  ├─ img/             # Compressed bitmap images
│  ├─ bundle.js        # Minified JavaScript file that bundles all the JavaScript files
│  ├─ main.css         # Minified CSS files
│  └─ sprites.svg      # Main SVG sprite file
│
├─ inc/                # Theme function includes
│
├─ src/
│  ├─ css/             # CSS files
│  │  ├─ style.css     # Entrypoint CSS file, includes all the imports for other css files
│  │  └─ *.css
|  |
│  ├─ img/             # Bitmap and vector images
│  │
│  ├─ index.js         # Entrypoint JavaScript file
│  └─ *.js             # Modular JavaScript files
|
├─ template/           # Drupal Twig templates
│
├─ .gitignore          # List of files and folders not tracked by Git
├─ browserconfig.xml   # Browser configuration files
├─ manifest.json       # Site metadata
├─ postcss.config.js   # PostCSS Plugin configurations
├─ webpack.config.js   # Webpack configurations
├─ package.json        # Npm dependancies
├─ yarn.lock           # Store exactly which versions of each npm dependency were installed
└─ README.md           # README file
```
