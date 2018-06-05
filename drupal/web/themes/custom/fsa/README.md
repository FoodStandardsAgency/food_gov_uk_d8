## Requirements

FSA Theme uses ES6 features, so it is recommended to have a recent version of [Node.js](https://nodejs.org/) installed globally (v5.0 minimum required, for `package-lock.json` support). Node.js is the only global requirement as this project uses [Webpack](https://webpack.js.org/) in lieu of task runners.

## Installation

To get the started with theming, complete the following steps:

1. Install [Node.js](https://nodejs.org/)

2. Install project dependencies by running `npm install`

## Workflow summary

When developing, run `npm run watch` to automatically compile assets.

Build for production before committing any changes, with `npm run build`. This will create a `dist` folder, into which the required assets will be created. In addition to assets for the site itself, the style guide will also be built (as a static html file) at `dist/styleguide/index.html`.

### JavaScript development

[![JavaScript Style Guide](https://cdn.rawgit.com/standard/standard/master/badge.svg)](https://github.com/standard/standard)

This theme uses the 'Standard' JavaScript code style as linked above. It is recommended to use an appropriate linting tool to ensure this is followed, such as the 'JavaScript Standard Style' VS Code extension, or appropriate ESLint rule.

The file `index.js` is the entry point for all the assests used by this theme. This file contains `require` function for style and image files. This function should not be removed ever.

NPM dependencies and theme related [modular JavaScript files](https://github.com/lukehoban/es6features#modules) can be imported at the beginning of every JavaScript file with following line of code:

```js
import defaultMember from 'module-name'
```

This project uses [Babel](https://babeljs.io/) to transpile modern JavaScript into a form that can be used across browsers.

[ES6 import syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/import). To export a JavaScript module use [ES6 export syntax](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/export) such as `module.exports = myFunction`.

There are number of other really useful ES6 features that can be also used, like:

* [Arrows](https://github.com/lukehoban/es6features#arrows)
* [Classes](https://github.com/lukehoban/es6features#classes)
* [Template strings](https://github.com/lukehoban/es6features#template-strings)
* [Destructuring](https://github.com/lukehoban/es6features#destructuring)
* [Spread](https://github.com/lukehoban/es6features#default--rest--spread)
* [Block-scoped binding constructs](https://github.com/lukehoban/es6features#let--const)

### CSS development

This theme uses number of postCSS plugins with  [postCSS-cssnext](http://cssnext.io/) plugin collection. Cssnext allows developers to use the latest CSS syntax today. Active plugins of the cssnext collection can be managed inside of a `postcss.config.js` file and available features can be browsed on [GitHub](https://github.com/MoOx/postcss-cssnext/blob/master/docs/content/features.md).

All the css files are extracted and bundled into one file with Webpack [_extract-text-webpack-plugin_](https://github.com/webpack-contrib/extract-text-webpack-plugin).

#### Units

The correct unit should be chosen depending on the context. _Em_ is great for responsive values such as _paddings_ and _margins_ while _px_ is good choice for fixed sized icons.

#### CSS Custom Properties

_Related PostCSS plugin: [postcss-custom-properties](https://github.com/postcss/postcss-custom-properties)_

[CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/--*) can be used in this theme. To support [older IE browsers](http://caniuse.com/#feat=css-variables) CSS custom properties should only be used inside of a `:root` selector. Cssnext has been configurated to preserve custom property values.

CSS Custom properties are defined at the beginning of the `base.css` file with following line of code:

```css
:root {
  --font-size-base: 112.5%;
}
```

CSS Custom properties can be reused throughout the CSS files using the `var()` function.

```js
html {
  font-size: var(--font-size-base);
}
```

#### Media Queries

_Related PostCSS plugin: [postcss-custom-media](https://github.com/postcss/postcss-custom-media)_

As this theme uses _mobile first_ approach all media queries should use `min-width`.

Custom media queries are defined at the beginning of the `base.css` file with following line of code:

```css
@custom-media --breakpoint-sm (min-width: 50em);
```

Custom variables can be used inside of a css file like so:

```css
@media (--breakpoint-sm) {
  width: 100%;
}
```

#### Mixins

_Related PostCSS plugin: [postcss-custom-media](https://github.com/postcss/postcss-mixins)_

Cssnext is providing a [PostCSS-apply](https://github.com/pascalduez/postcss-apply) plugin but it doesn't support arguments. That's why PostCSS-mixin plugin is also used in this theme.

PostCSS Mixin is currently only used for defining one mixin:

```css
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

This responsive-declaration mixin is used throughout the CSS files to create both mobile and desktop styles with one line of code. This is great for declaring _paddings_, _margins_, _font sizes_ etc.

The mixin takes three arguments: _Property name_, _Minimum value_ and _Maximum value_. It can be used like this:

```css
h1 {
  @mixin responsive-declaration font-size, 2.2em, 3.333em;
}
```

#### Selectors and nesting

_Related PostCSS plugins: [postcss-nesting](https://github.com/jonathantneal/postcss-nesting) and [postcss-custom-selectors](https://github.com/postcss/postcss-custom-selectors)_

Custom selectors can be declared like this:

```css
@custom-selector :--not-js html:not(.js);
```

And used as a selector like this:

```css
:--not-js .toggle-content {
  display: inherit;
}
```

Selectors can also be nested:

```css
ul {
  margin-top: 0.5em;

  & li:not(:last-child) {
    margin-bottom: 1em;
  }
}
```

#### CSS Naming conventions

This theme uses [BEM](http://getbem.com/) as much as possible.

### Bitmap and vector assets

Webpack loaders check for bitmap and vector images separately. Bitmap images are compressed and copied to `dist/img/` directory. All the vector images on the other hand are turned into one sprite (`dist/sprite.svg`). Specific vector images can be referenced like this:

```html
<div class="svg">
  <svg><use xlink:href="/themes/custom/fsa/dist/sprite.svg#name-of-the-svg-file"></use></svg>
</div>
```

To polyfill browsers that don't support `<use>` tags, every svg reference will be turned into an inline svg by the browser with the [svg4everybody](https://github.com/jonathantneal/svg4everybody) JavaScript plugin.

Remember to restart varnish with `sudo systemctl restart varnish` after generating a new sprite, in order to rebuild the cache.
