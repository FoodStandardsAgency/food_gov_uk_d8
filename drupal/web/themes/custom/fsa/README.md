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

Npm dependencies and theme related [modular JavaScript files](https://github.com/lukehoban/es6features#modules) can be imported at the beginning of every JavaScript file with `import defaultMember from 'module-name'` [ES6 import syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/import). To export a JavaScript module use [ES6 export syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/export) such as `module.exports = myFunction;`.

There are number of other really useful ES6 features that can be also used, like:
* [Arrows](https://github.com/lukehoban/es6features#arrows)
* [Classes](https://github.com/lukehoban/es6features#classes)
* [Template strings](https://github.com/lukehoban/es6features#template-strings)
* [Destructuring](https://github.com/lukehoban/es6features#destructuring)
* [Spread](https://github.com/lukehoban/es6features#default--rest--spread)
* [Block-scoped binding constructs](https://github.com/lukehoban/es6features#let--const)

### CSS development
This theme uses number of [PostCSS plugins](http://cssnext.io/features/) to enable native-like css syntax while still providing _Sass_ like features. Active plugins can be managed inside of a `postcss.config.js` file. All the css files are extracted and bundled into one file with Webpack [_extract-text-webpack-plugin_](https://github.com/webpack-contrib/extract-text-webpack-plugin).

### CSS Naming conventions
This theme uses [BEM](http://getbem.com/) as much as it is possible.

### Bitmap and vector assets
Webpack loaders check for bitmap and vector images separately. Bitmap images and compressed and copied to `dist/img/` folder. All the vector images are turned into one sprite (`dist/sprite.svg`) on the other hand. Themer can reference to a specific vector image with this line of code:

```
<div class="svg">
  <svg><use xlink:href="/themes/custom/fsa/dist/sprite.svg#name-of-the-svg-file"></use></svg>
</div>
```
To polyfill browsers that don't support `<use>` tags every svg reference will be turned into inline svg by browser with [svg4everybody](https://github.com/jonathantneal/svg4everybody) JavaScript plugin.


## 📦 Deployment
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
