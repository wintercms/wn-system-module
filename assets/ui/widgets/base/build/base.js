(()=>{"use strict";function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(e,t){for(var o=0;o<t.length;o++){var r=t[o];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function o(){return o="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,o){var n=r(e,t);if(n){var i=Object.getOwnPropertyDescriptor(n,t);return i.get?i.get.call(arguments.length<3?e:o):i.value}},o.apply(this,arguments)}function r(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=a(e)););return e}function n(e,t){return n=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e},n(e,t)}function i(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(e){return!1}}();return function(){var o,r=a(e);if(t){var n=a(this).constructor;o=Reflect.construct(r,arguments,n)}else o=r.apply(this,arguments);return l(this,o)}}function l(t,o){if(o&&("object"===e(o)||"function"==typeof o))return o;if(void 0!==o)throw new TypeError("Derived constructors may only return object or undefined");return function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(t)}function a(e){return a=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)},a(e)}var s=function(e){!function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&n(e,t)}(c,Snowboard.Singleton);var r,l,s,y=i(c);function c(e){var t;return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,c),(t=y.call(this,e)).overlay=null,t.shown=!1,t.color="#000000",t.opacity=.5,t.speed=175,t}return r=c,(l=[{key:"dependencies",value:function(){return["transition"]}},{key:"listens",value:function(){return{ready:"ready"}}},{key:"ready",value:function(){this.createOverlay()}},{key:"destructor",value:function(){this.destroyOverlay(),o(a(c.prototype),"destructor",this).call(this)}},{key:"createOverlay",value:function(){var e=this;this.overlay=document.createElement("div"),this.overlay.id="overlay",this.setStyle(),this.overlay.addEventListener("click",(function(t){e.snowboard.globalEvent("overlay.clicked",e.overlay,t)})),document.body.appendChild(this.overlay)}},{key:"destroyOverlay",value:function(){document.body.removeChild(this.overlay),this.overlay=null}},{key:"setStyle",value:function(){var e=this;this.overlay.style.backgroundColor=this.color,this.overlay.style.position="fixed",this.overlay.style.top=0,this.overlay.style.left=0,this.overlay.style.zIndex=1e3,this.overlay.style.transitionProperty="opacity",this.overlay.style.transitionTimingFunction="ease-out",this.overlay.style.transitionDuration="".concat(this.speed,"ms"),window.requestAnimationFrame((function(){e.shown?(e.overlay.style.width="100%",e.overlay.style.height="100%",e.overlay.style.opacity=e.opacity):(e.overlay.style.width="0px",e.overlay.style.height="0px",e.overlay.style.opacity=0)}))}},{key:"show",value:function(){var e=this;this.snowboard.globalEvent("overlay.show",this.overlay),this.shown=!0,this.overlay.style.width="100%",this.overlay.style.height="100%",window.requestAnimationFrame((function(){e.overlay.style.opacity=e.opacity,e.overlay.addEventListener("transitionend",(function(){e.snowboard.globalEvent("overlay.shown",e.overlay)}),{once:!0})}))}},{key:"hide",value:function(){var e=this;this.snowboard.globalEvent("overlay.hide",this.overlay),this.overlay.style.opacity=0,this.overlay.addEventListener("transitionend",(function(){e.shown=!1,e.overlay.style.width="0px",e.overlay.style.height="0px",e.snowboard.globalEvent("overlay.hidden",e.overlay)}),{once:!0})}},{key:"toggle",value:function(){this.shown?this.hide():this.show()}},{key:"setColor",value:function(e){return this.color=String(e),this.setStyle(),this}},{key:"setOpacity",value:function(e){return this.opacity=Number(e),this.setStyle(),this}},{key:"setSpeed",value:function(e){return this.speed=Number(e),this.setStyle(),this}}])&&t(r.prototype,l),s&&t(r,s),Object.defineProperty(r,"prototype",{writable:!1}),c}();if(void 0===window.Snowboard)throw new Error("The Snowboard library must be loaded in order to use the Inspector widget");window.Snowboard.addPlugin("overlay",s)})();