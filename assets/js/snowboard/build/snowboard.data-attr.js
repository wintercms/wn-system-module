"use strict";(self.webpackChunk_wintercms_wn_system_module=self.webpackChunk_wintercms_wn_system_module||[]).push([[14],{660:function(e,t,r){r.d(t,{A:function(){return n}});class n{constructor(e){this.snowboard=e}construct(){}dependencies(){return[]}listens(){return{}}destruct(){this.detach(),delete this.snowboard}destructor(){this.destruct()}}},373:function(e,t,r){r.d(t,{A:function(){return a}});var n=r(660);class a extends n.A{}},364:function(e,t,r){var n=r(373);function a(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function s(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?a(Object(r),!0).forEach((function(t){i(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):a(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}function i(e,t,r){return(t=function(e){var t=function(e,t){if("object"!=typeof e||!e)return e;var r=e[Symbol.toPrimitive];if(void 0!==r){var n=r.call(e,t||"default");if("object"!=typeof n)return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"==typeof t?t:t+""}(t))in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}class u extends n.A{listens(){return{ready:"ready",ajaxSetup:"onAjaxSetup"}}ready(){this.attachHandlers(),this.disableDefaultFormValidation()}dependencies(){return["request","jsonParser"]}destruct(){this.detachHandlers(),super.destruct()}attachHandlers(){window.addEventListener("change",(e=>this.changeHandler(e))),window.addEventListener("click",(e=>this.clickHandler(e))),window.addEventListener("keydown",(e=>this.keyDownHandler(e))),window.addEventListener("submit",(e=>this.submitHandler(e)))}disableDefaultFormValidation(){document.querySelectorAll("form[data-request]:not([data-browser-validate])").forEach((e=>{e.setAttribute("novalidate",!0)}))}detachHandlers(){window.removeEventListener("change",(e=>this.changeHandler(e))),window.removeEventListener("click",(e=>this.clickHandler(e))),window.removeEventListener("keydown",(e=>this.keyDownHandler(e))),window.removeEventListener("submit",(e=>this.submitHandler(e)))}changeHandler(e){e.target.matches("select[data-request], input[type=radio][data-request], input[type=checkbox][data-request], input[type=file][data-request]")&&this.processRequestOnElement(e.target)}clickHandler(e){let t=e.target;for(;t&&"HTML"!==t.tagName;){if(t.matches("a[data-request], button[data-request], input[type=button][data-request], input[type=submit][data-request]")){e.preventDefault(),this.processRequestOnElement(t);break}t=t.parentElement}}keyDownHandler(e){if(!e.target.matches("input"))return;-1!==["checkbox","color","date","datetime","datetime-local","email","image","month","number","password","radio","range","search","tel","text","time","url","week"].indexOf(e.target.getAttribute("type"))&&("Enter"===e.key&&e.target.matches("*[data-request]")?(this.processRequestOnElement(e.target),e.preventDefault(),e.stopImmediatePropagation()):e.target.matches("*[data-track-input]")&&this.trackInput(e.target))}submitHandler(e){e.target.matches("form[data-request]")&&(e.preventDefault(),this.processRequestOnElement(e.target))}processRequestOnElement(e){const t=e.dataset,r=String(t.request),n={confirm:"requestConfirm"in t?String(t.requestConfirm):null,redirect:"requestRedirect"in t?String(t.requestRedirect):null,loading:"requestLoading"in t?String(t.requestLoading):null,stripe:!("requestStripe"in t)||"true"===t.requestStripe,flash:"requestFlash"in t,files:"requestFiles"in t,browserValidate:"requestBrowserValidate"in t,form:"requestForm"in t?String(t.requestForm):null,url:"requestUrl"in t?String(t.requestUrl):null,update:"requestUpdate"in t?this.parseData(String(t.requestUpdate)):[],data:"requestData"in t?this.parseData(String(t.requestData)):[]};this.snowboard.request(e,r,n)}onAjaxSetup(e){if(!e.element)return;const t=e.element.getAttribute("name"),r=s(s({},this.getParentRequestData(e.element)),e.options.data);e.element&&e.element.matches("input, textarea, select, button")&&!e.form&&t&&!e.options.data[t]&&(r[t]=e.element.value),e.options.data=r}getParentRequestData(e){const t=[];let r={},n=e;for(;n.parentElement&&"HTML"!==n.parentElement.tagName;)t.push(n.parentElement),n=n.parentElement;return t.reverse(),t.forEach((e=>{const t=e.dataset;"requestData"in t&&(r=s(s({},r),this.parseData(t.requestData)))})),r}parseData(e){let t;if(void 0===e&&(t=""),"object"==typeof t)return t;try{return this.snowboard.jsonparser().parse(`{${e}}`)}catch(e){throw new Error(`Error parsing the data attribute on element: ${e.message}`)}}trackInput(e){const{lastValue:t}=e.dataset,r=e.dataset.trackInput||300;void 0!==t&&t===e.value||(this.resetTrackInputTimer(e),e.dataset.inputTimer=window.setTimeout((()=>{if(e.dataset.request)return void this.processRequestOnElement(e);let t=e;for(;t.parentElement&&"HTML"!==t.parentElement.tagName;)if(t=t.parentElement,"FORM"===t.tagName&&t.dataset.request){this.processRequestOnElement(t);break}}),r))}resetTrackInputTimer(e){e.dataset.inputTimer&&(window.clearTimeout(e.dataset.inputTimer),e.dataset.inputTimer=null)}}if(void 0===window.Snowboard)throw new Error("Snowboard must be loaded in order to use the HTML data attribute AJAX request feature.");window.Snowboard.addPlugin("attributeRequest",u)}},function(e){var t;t=364,e(e.s=t)}]);