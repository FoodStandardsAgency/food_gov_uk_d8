!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:r})},n.r=function(e){Object.defineProperty(e,"__esModule",{value:!0})},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=133)}({133:function(e,t,n){"use strict";var r=i(n(67)),o=i(n(65));function i(e){return e&&e.__esModule?e:{default:e}}function c(e){if(Array.isArray(e)){for(var t=0,n=Array(e.length);t<e.length;t++)n[t]=e[t];return n}return Array.from(e)}document.querySelector(".navigation-wrapper__main").style.height="82px",document.querySelectorAll(".promo-listing__item").forEach(function(e){e.style.height="400px"}),document.querySelectorAll(".field__field_content_reference__item").forEach(function(e){e.style.height="130.5px",e.style.marginBottom="17.5px"});var l=[].concat(c(document.querySelectorAll(".js-sticky-container"))),u=[].concat(c(document.querySelectorAll(".js-sticky-element")));null==l&&null==u||(0,r.default)(l,u);var a=[].concat(c(document.querySelectorAll(".js-scroll")));if(null!=a)for(var s=0;s<a.length;s++){a[s].addEventListener("click",function(e){e.preventDefault();var t=this.href.substr(this.href.indexOf("#")+1),n=document.getElementById(t);(0,o.default)(n,1e3,-20)})}},65:function(e,t,n){"use strict";e.exports=function(e,t,n){var r,o=window.pageYOffset,i=window.pageYOffset+e.getBoundingClientRect().top,c=(document.body.scrollHeight-i<window.innerHeight?document.body.scrollHeight-window.innerHeight+n:i+n)-o;c&&window.requestAnimationFrame(function e(n){r||(r=n);var i,l=n-r,u=Math.min(l/t,1);u=(i=u)<.5?4*i*i*i:(i-1)*(2*i-2)*(2*i-2)+1,window.scrollTo(0,o+c*u),l<t&&window.requestAnimationFrame(e)})}},67:function(e,t,n){"use strict";var r=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}();function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}e.exports=function(e,t){for(var n=function(){function e(t){o(this,e),this.element=t}return r(e,[{key:"calcOffset",value:function(){return this.element.getBoundingClientRect().top}},{key:"calcInview",value:function(){var e=this.element.getBoundingClientRect();return e.top-window.innerHeight<=0&&e.bottom>=0}},{key:"calcBottom",value:function(){var e=this._relatedInstance.element.offsetHeight;return this.element.getBoundingClientRect().bottom<=e}},{key:"relatedInstance",set:function(e){this._relatedInstance=e},get:function(){return this._relatedInstance}},{key:"thisElement",get:function(){return this.element}},{key:"offset",get:function(){return this.calcOffset()}},{key:"inview",get:function(){return this.calcInview()}},{key:"isBottom",get:function(){return this.calcBottom()}}]),e}(),i=function(e){function t(){return o(this,t),function(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}(this,(t.__proto__||Object.getPrototypeOf(t)).apply(this,arguments))}return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}(t,n),t}(),c=[],l=[],u=0;u<e.length;u++){for(var a=t,s=0;s<a.length;s++){var f=a[s];l.push(new i(f))}c.push(new n(e[u]))}for(var d=0;d<c.length;d++)c[d].relatedInstance=l[d];var h=function(){c.forEach(function(e){e.isBottom?e.relatedInstance.element.classList.add("is-bottom"):e.relatedInstance.element.classList.remove("is-bottom"),e.inview&&e.offset<0?e.relatedInstance.element.classList.add("is-sticky"):e.relatedInstance.element.classList.remove("is-sticky")})};window.addEventListener("scroll",h),window.addEventListener("load",h)}}});